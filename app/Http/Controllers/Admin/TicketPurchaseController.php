<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Accounts;
use App\Models\Customers;
use App\Models\TicketPurchasePaymentHistory;
use App\Models\TicketPurchases;
use App\Models\Vendors;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class TicketPurchaseController extends Controller
{
    public function index(Request $request): View
    {
        $vendors = Vendors::orderBy('name')->get();

        $accounts = Accounts::where('status', 'active')
            ->orderBy('name')
            ->get();

        $customers = Customers::orderBy('name')->get();

        $ticketPurchases = TicketPurchases::with([
            'vendor:id,name',
            'customer:id,name',
            'account:id,name',
        ])
            ->latest()
            ->get();

        return view('admin.ticket_purchases.index', [
            'vendors' => $vendors,
            'accounts' => $accounts,
            'customers' => $customers,
            'ticketPurchases' => $ticketPurchases,
        ]);
    }

    public function create(Request $request): RedirectResponse
    {
        return redirect()->route('ticket_purchases.index');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'vendor_id' => ['nullable', Rule::exists('vendors', 'id')],
            'customer_id' => ['nullable', Rule::exists('customers', 'id')],
            'account_id' => ['nullable', Rule::exists('accounts', 'id')],
            'flight_date' => ['required', 'date'],
            'sector' => ['required', 'string', 'max:255'],
            'carrier' => ['required', 'string', 'max:255'],
            'net_fare' => ['required', 'numeric', 'min:0'],
            'paid_amount' => ['nullable', 'numeric', 'min:0'],
            'issue_date' => ['nullable', 'date'],
            'notes' => ['nullable', 'string', 'max:5000'],
        ]);

        $paid = (float) ($validated['paid_amount'] ?? 0);
        $due  = (float) $validated['net_fare'] - $paid;

        DB::transaction(function () use ($validated, $paid, $due) {
            $purchase = TicketPurchases::create([
                'vendor_id' => $validated['vendor_id'] ?? null,
                'customer_id' => $validated['customer_id'] ?? null,
                'account_id' => $validated['account_id'] ?? null,
                'flight_date' => $validated['flight_date'],
                'sector' => $validated['sector'],
                'carrier' => $validated['carrier'],
                'net_fare' => $validated['net_fare'],
                'paid_amount' => $paid,
                'due_amount' => $due,
                'issue_date' => $validated['issue_date'] ?? null,
                'notes' => $validated['notes'] ?? null,
            ]);

            // Decrement account by paid amount
            if (!empty($validated['account_id']) && $paid > 0) {
                Accounts::where('id', $validated['account_id'])
                    ->decrement('current_balance', $paid);
            }

            // Create first history row (optional but recommended)
            if ($paid > 0) {
                TicketPurchasePaymentHistory::create([
                    'ticket_purchase_id' => $purchase->id,
                    'account_id' => $validated['account_id'] ?? null,
                    'paid' => $paid,
                    'due' => $due,
                    'company_id' => $purchase->company_id ?? null,
                ]);
            }
        });

        return redirect()
            ->route('ticket_purchases.index')
            ->with('success', 'Ticket purchase saved successfully.');
    }

    public function edit(int $ticketPurchase): View
    {
        $ticketPurchase = TicketPurchases::findOrFail($ticketPurchase);

        $vendors = Vendors::orderBy('name')->get();

        $accounts = Accounts::where('status', 'active')
            ->orderBy('name')
            ->get();

        $customers = Customers::orderBy('name')->get();

        return view('admin.ticket_purchases.edit', [
            'ticketPurchase' => $ticketPurchase,
            'vendors' => $vendors,
            'accounts' => $accounts,
            'customers' => $customers,
        ]);
    }

    public function update(Request $request, int $ticketPurchase): RedirectResponse
    {
        $ticketPurchase = TicketPurchases::findOrFail($ticketPurchase);

        $historyCount = TicketPurchasePaymentHistory::where('ticket_purchase_id', $ticketPurchase->id)->count();

        /**
         * If multiple payments exist -> disable editing paid/account
         * (same logic as your TicketSalesController)
         */
        if ($historyCount > 1) {
            $validated = $request->validate([
                'vendor_id'   => ['nullable', Rule::exists('vendors', 'id')],
                'customer_id' => ['nullable', Rule::exists('customers', 'id')],
                'flight_date' => ['required', 'date'],
                'sector'      => ['required', 'string', 'max:255'],
                'carrier'     => ['required', 'string', 'max:255'],
                'net_fare'    => ['required', 'numeric', 'min:0'],
                'issue_date'  => ['nullable', 'date'],
                'notes'       => ['nullable', 'string', 'max:5000'],
            ]);

            $paid = (float) ($ticketPurchase->paid_amount ?? 0);
            $due  = (float) $validated['net_fare'] - $paid;

            DB::transaction(function () use ($ticketPurchase, $validated, $due) {
                $ticketPurchase->update([
                    'vendor_id'   => $validated['vendor_id'] ?? null,
                    'customer_id' => $validated['customer_id'] ?? null,
                    'flight_date' => $validated['flight_date'],
                    'sector'      => $validated['sector'],
                    'carrier'     => $validated['carrier'],
                    'net_fare'    => $validated['net_fare'],
                    'due_amount'  => $due,
                    'issue_date'  => $validated['issue_date'] ?? null,
                    'notes'       => $validated['notes'] ?? null,
                ]);
            });

            return redirect()
                ->route('ticket_purchases.index')
                ->with('success', 'Ticket purchase updated (payment edit disabled because multiple payments exist).');
        }

        /**
         * If only 0/1 history row -> allow editing paid/account like TicketSalesController
         */
        $validated = $request->validate([
            'vendor_id'   => ['nullable', Rule::exists('vendors', 'id')],
            'customer_id' => ['nullable', Rule::exists('customers', 'id')],
            'account_id'  => ['nullable', Rule::exists('accounts', 'id')],
            'flight_date' => ['required', 'date'],
            'sector'      => ['required', 'string', 'max:255'],
            'carrier'     => ['required', 'string', 'max:255'],
            'net_fare'    => ['required', 'numeric', 'min:0'],
            'paid_amount' => ['nullable', 'numeric', 'min:0'],
            'issue_date'  => ['nullable', 'date'],
            'notes'       => ['nullable', 'string', 'max:5000'],
        ]);

        $newPaid = (float) ($validated['paid_amount'] ?? 0);
        $due     = (float) $validated['net_fare'] - $newPaid;

        DB::transaction(function () use ($ticketPurchase, $validated, $due, $newPaid) {

            // Revert previous payment (Purchase decrements balance, so revert = increment back)
            if (!empty($ticketPurchase->account_id) && (float) ($ticketPurchase->paid_amount ?? 0) > 0) {
                Accounts::whereKey($ticketPurchase->account_id)
                    ->increment('current_balance', (float) $ticketPurchase->paid_amount);
            }

            // Apply new payment (Purchase paid means money went out, so decrement)
            if (!empty($validated['account_id']) && $newPaid > 0) {
                Accounts::whereKey($validated['account_id'])
                    ->decrement('current_balance', $newPaid);
            }

            // Update (or create) first history row (oldest)
            $firstHistory = TicketPurchasePaymentHistory::where('ticket_purchase_id', $ticketPurchase->id)
                ->oldest('id')
                ->first();

            if ($firstHistory) {
                $firstHistory->update([
                    'account_id' => $validated['account_id'] ?? null,
                    'paid'       => $newPaid,
                    'due'        => $due,
                ]);
            } else {
                // if no history row exists yet, create one
                TicketPurchasePaymentHistory::create([
                    'ticket_purchase_id' => $ticketPurchase->id,
                    'account_id'         => $validated['account_id'] ?? null,
                    'paid'               => $newPaid,
                    'due'                => $due,
                    'company_id'         => $ticketPurchase->company_id ?? null,
                ]);
            }

            // Update purchase
            $ticketPurchase->update([
                'vendor_id'   => $validated['vendor_id'] ?? null,
                'customer_id' => $validated['customer_id'] ?? null,
                'account_id'  => $validated['account_id'] ?? null,
                'flight_date' => $validated['flight_date'],
                'sector'      => $validated['sector'],
                'carrier'     => $validated['carrier'],
                'net_fare'    => $validated['net_fare'],
                'paid_amount' => $newPaid,
                'due_amount'  => $due,
                'issue_date'  => $validated['issue_date'] ?? null,
                'notes'       => $validated['notes'] ?? null,
            ]);
        });

        return redirect()
            ->route('ticket_purchases.index')
            ->with('success', 'Ticket purchase updated successfully.');
    }

    public function destroy(int $ticketPurchase): RedirectResponse
    {
        $ticketPurchase = TicketPurchases::findOrFail($ticketPurchase);

        DB::transaction(function () use ($ticketPurchase) {
            // Reverse paid amount to account
            if (!empty($ticketPurchase->account_id) && (float) ($ticketPurchase->paid_amount ?? 0) > 0) {
                Accounts::where('id', $ticketPurchase->account_id)
                    ->increment('current_balance', (float) ($ticketPurchase->paid_amount ?? 0));
            }

            // Optional: delete histories
            TicketPurchasePaymentHistory::where('ticket_purchase_id', $ticketPurchase->id)->delete();

            $ticketPurchase->delete();
        });

        return redirect()
            ->route('ticket_purchases.index')
            ->with('success', 'Ticket purchase deleted successfully.');
    }

    // =========================
    // PAYMENT HISTORY
    // =========================

    public function paymentHistory(int $ticketPurchase): View
    {
        $ticketPurchase = TicketPurchases::with(['vendor:id,name', 'customer:id,name'])->findOrFail($ticketPurchase);

        $accounts = Accounts::where('status', 'active')->orderBy('name')->get();

        $paymentHistory = TicketPurchasePaymentHistory::with('account:id,name')
            ->where('ticket_purchase_id', $ticketPurchase->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('admin.ticket_purchases.payment_history', [
            'ticketPurchase' => $ticketPurchase,
            'accounts' => $accounts,
            'paymentHistory' => $paymentHistory,
        ]);
    }

    public function addPayment(Request $request, int $ticketPurchase): RedirectResponse
    {
        $ticketPurchase = TicketPurchases::findOrFail($ticketPurchase);

        $validated = $request->validate([
            'account_id' => ['required', Rule::exists('accounts', 'id')],
            'paid' => ['required', 'numeric', 'min:0.01'],
        ]);

        $paid = (float) $validated['paid'];

        DB::transaction(function () use ($ticketPurchase, $validated, $paid) {

            $currentPaid = (float) ($ticketPurchase->paid_amount ?? 0);
            $netFare     = (float) ($ticketPurchase->net_fare ?? 0);

            // Prevent overpay (recommended)
            $maxPayable = max(0, $netFare - $currentPaid);
            if ($paid > $maxPayable) {
                // throw validation-like exception to rollback
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'paid' => 'Paid amount cannot be greater than due amount.',
                ]);
            }

            $newPaidTotal = $currentPaid + $paid;
            $newDue = $netFare - $newPaidTotal;

            // Create history row
            TicketPurchasePaymentHistory::create([
                'ticket_purchase_id' => $ticketPurchase->id,
                'account_id' => $validated['account_id'],
                'paid' => $paid,
                'due' => $newDue,
                'company_id' => $ticketPurchase->company_id ?? null,
            ]);

            // Update purchase totals
            $ticketPurchase->update([
                'paid_amount' => $newPaidTotal,
                'due_amount' => $newDue,
                'account_id' => $validated['account_id'], // keep last used account (optional)
            ]);

            // Decrement account balance
            Accounts::where('id', $validated['account_id'])
                ->decrement('current_balance', $paid);
        });

        return redirect()
            ->route('ticket_purchases.payment_history', $ticketPurchase->id)
            ->with('success', 'Payment added successfully.');
    }

    public function editPaymentHistory(int $history): View
    {
        $history = TicketPurchasePaymentHistory::with([
            'ticketPurchase:id,net_fare,paid_amount,due_amount',
            'account:id,name',
        ])->findOrFail($history);

        $accounts = Accounts::where('status', 'active')->orderBy('name')->get();

        return view('admin.ticket_purchases.payment_history_edit', [
            'history' => $history,
            'accounts' => $accounts,
        ]);
    }

    public function updatePaymentHistory(Request $request, int $history): RedirectResponse
    {
        $history = TicketPurchasePaymentHistory::with('ticketPurchase')->findOrFail($history);
        $ticketPurchase = $history->ticketPurchase;

        if (!$ticketPurchase) {
            return redirect()->route('ticket_purchases.index')
                ->with('success', 'Purchase not found for this history row.');
        }

        $validated = $request->validate([
            'account_id' => ['required', Rule::exists('accounts', 'id')],
            'paid'       => ['required', 'numeric', 'min:0.01'],
        ]);

        $newAccountId = (int) $validated['account_id'];
        $newPaid      = (float) $validated['paid'];

        $oldAccountId = $history->account_id;
        $oldPaid      = (float) ($history->paid ?? 0);

        DB::transaction(function () use (
            $history,
            $ticketPurchase,
            $newAccountId,
            $newPaid,
            $oldAccountId,
            $oldPaid
        ) {
            // 1) reverse OLD effect (purchase payment: money went OUT => we revert by adding back)
            if (!empty($oldAccountId) && $oldPaid > 0) {
                Accounts::whereKey($oldAccountId)->increment('current_balance', $oldPaid);
            }

            // 2) apply NEW effect (money goes OUT => decrement)
            if (!empty($newAccountId) && $newPaid > 0) {
                Accounts::whereKey($newAccountId)->decrement('current_balance', $newPaid);
            }

            // 3) update history row
            $history->update([
                'account_id' => $newAccountId,
                'paid'       => $newPaid,
            ]);

            // 4) recalc purchase totals + each history due (running due)
            $this->recalculateTicketPurchasePayments($ticketPurchase->id);
        });

        return redirect()
            ->route('ticket_purchases.payment_history', $ticketPurchase->id)
            ->with('success', 'Payment history updated successfully.');
    }

    /**
     * Recalculate:
     * - purchase paid_amount / due_amount
     * - each history row "due" (due after that payment), based on created_at ASC
     */
    private function recalculateTicketPurchasePayments(int $ticketPurchaseId): void
    {
        $purchase = TicketPurchases::findOrFail($ticketPurchaseId);

        $rows = TicketPurchasePaymentHistory::where('ticket_purchase_id', $purchase->id)
            ->orderBy('created_at', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        $netFare = (float) ($purchase->net_fare ?? 0);
        $runningPaid = 0.0;

        foreach ($rows as $row) {
            $runningPaid += (float) ($row->paid ?? 0);
            $runningDue = $netFare - $runningPaid;

            $row->update([
                'due' => $runningDue,
            ]);
        }

        $paidTotal = (float) $rows->sum('paid');
        $dueTotal  = $netFare - $paidTotal;

        $purchase->update([
            'paid_amount' => $paidTotal,
            'due_amount'  => $dueTotal,
        ]);
    }
}

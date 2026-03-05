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
use Illuminate\Validation\ValidationException;
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
            ->whereDoesntHave('ticketSale')
            ->latest()
            ->get();

        return view('admin.ticket_purchases.index', compact(
            'vendors',
            'accounts',
            'customers',
            'ticketPurchases'
        ));
    }

    public function create(Request $request): RedirectResponse
    {
        return redirect()->route('ticket_purchases.index');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'vendor_id'    => ['nullable', Rule::exists('vendors', 'id')],
            'customer_id'  => ['nullable', Rule::exists('customers', 'id')],
            'account_id'   => ['nullable', Rule::exists('accounts', 'id')],
            'flight_date'  => ['required', 'date'],
            'sector'       => ['required', 'string', 'max:255'],
            'carrier'      => ['required', 'string', 'max:255'],
            'net_fare'     => ['required', 'numeric', 'min:0'],
            'paid_amount'  => ['nullable', 'numeric', 'min:0'],
            'issue_date'   => ['nullable', 'date'],
            'notes'        => ['nullable', 'string', 'max:5000'],
        ]);

        $paid = (float) ($validated['paid_amount'] ?? 0);
        $net  = (float) $validated['net_fare'];
        $due  = $net - $paid;

        DB::transaction(function () use ($validated, $paid, $due) {

            // If paid > 0, account must be provided
            if ($paid > 0 && empty($validated['account_id'])) {
                throw ValidationException::withMessages([
                    'account_id' => 'Account is required when paid amount is greater than 0.',
                ]);
            }

            // Decrement balance only if we have paid + account
            if ($paid > 0 && !empty($validated['account_id'])) {
                Accounts::whereKey($validated['account_id'])
                    ->decrement('current_balance', $paid);
            }

            $purchase = TicketPurchases::create([
                'vendor_id'   => $validated['vendor_id'] ?? null,
                'customer_id' => $validated['customer_id'] ?? null,
                'account_id'  => $validated['account_id'] ?? null,
                'flight_date' => $validated['flight_date'],
                'sector'      => $validated['sector'],
                'carrier'     => $validated['carrier'],
                'net_fare'    => $validated['net_fare'],
                'paid_amount' => $paid,
                'due_amount'  => $due,
                'issue_date'  => $validated['issue_date'] ?? null,
                'notes'       => $validated['notes'] ?? null,
                // 'company_id' => auth()->user()->company_id, // if you use company_id manually
            ]);

            // First payment history (recommended)
            if ($paid > 0) {
                TicketPurchasePaymentHistory::create([
                    'ticket_purchase_id' => $purchase->id,
                    'account_id'         => $validated['account_id'],
                    'paid'               => $paid,
                    'due'                => $due,
                    'company_id'         => $purchase->company_id ?? null,
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
        $accounts = Accounts::where('status', 'active')->orderBy('name')->get();
        $customers = Customers::orderBy('name')->get();

        return view('admin.ticket_purchases.edit', compact(
            'ticketPurchase',
            'vendors',
            'accounts',
            'customers'
        ));
    }

    public function update(Request $request, int $ticketPurchase): RedirectResponse
    {
        $ticketPurchase = TicketPurchases::findOrFail($ticketPurchase);

        $historyCount = TicketPurchasePaymentHistory::where('ticket_purchase_id', $ticketPurchase->id)->count();

        // Always editable fields
        $baseValidated = $request->validate([
            'vendor_id'   => ['nullable', Rule::exists('vendors', 'id')],
            'customer_id' => ['nullable', Rule::exists('customers', 'id')],
            'flight_date' => ['required', 'date'],
            'sector'      => ['required', 'string', 'max:255'],
            'carrier'     => ['required', 'string', 'max:255'],
            'net_fare'    => ['required', 'numeric', 'min:0'],
            'issue_date'  => ['nullable', 'date'],
            'notes'       => ['nullable', 'string', 'max:5000'],
        ]);

        /**
         * If multiple payments exist -> disable editing paid/account
         */
        if ($historyCount > 1) {

            $paid = (float) ($ticketPurchase->paid_amount ?? 0);
            $net  = (float) $baseValidated['net_fare'];
            $due  = $net - $paid;

            DB::transaction(function () use ($ticketPurchase, $baseValidated, $net, $due) {
                $ticketPurchase->update([
                    'vendor_id'   => $baseValidated['vendor_id'] ?? null,
                    'customer_id' => $baseValidated['customer_id'] ?? null,
                    'flight_date' => $baseValidated['flight_date'],
                    'sector'      => $baseValidated['sector'],
                    'carrier'     => $baseValidated['carrier'],
                    'net_fare'    => $net,
                    'due_amount'  => $due,
                    'issue_date'  => $baseValidated['issue_date'] ?? null,
                    'notes'       => $baseValidated['notes'] ?? null,
                ]);

                // also update each history "due" based on new net fare
                $this->recalculateTicketPurchasePayments($ticketPurchase->id);
            });

            return redirect()
                ->route('ticket_purchases.index')
                ->with('success', 'Ticket purchase updated (payment edit disabled because multiple payments exist).');
        }

        /**
         * If only 0/1 payment rows -> allow editing paid/account
         */
        $payValidated = $request->validate([
            'account_id'  => ['nullable', Rule::exists('accounts', 'id')],
            'paid_amount' => ['nullable', 'numeric', 'min:0'],
        ]);

        $newPaid = (float) ($payValidated['paid_amount'] ?? 0);
        $net     = (float) $baseValidated['net_fare'];
        $due     = $net - $newPaid;

        if ($newPaid > 0 && empty($payValidated['account_id'])) {
            throw ValidationException::withMessages([
                'account_id' => 'Account is required when paid amount is greater than 0.',
            ]);
        }

        DB::transaction(function () use ($ticketPurchase, $baseValidated, $payValidated, $net, $newPaid, $due) {

            // revert previous payment effect
            $oldPaid = (float) ($ticketPurchase->paid_amount ?? 0);
            if (!empty($ticketPurchase->account_id) && $oldPaid > 0) {
                Accounts::whereKey($ticketPurchase->account_id)
                    ->increment('current_balance', $oldPaid);
            }

            // apply new payment effect
            if (!empty($payValidated['account_id']) && $newPaid > 0) {
                Accounts::whereKey($payValidated['account_id'])
                    ->decrement('current_balance', $newPaid);
            }

            // update/create first history row
            $firstHistory = TicketPurchasePaymentHistory::where('ticket_purchase_id', $ticketPurchase->id)
                ->oldest('id')
                ->first();

            if ($newPaid > 0) {
                if ($firstHistory) {
                    $firstHistory->update([
                        'account_id' => $payValidated['account_id'],
                        'paid'       => $newPaid,
                        'due'        => $due,
                    ]);
                } else {
                    TicketPurchasePaymentHistory::create([
                        'ticket_purchase_id' => $ticketPurchase->id,
                        'account_id'         => $payValidated['account_id'],
                        'paid'               => $newPaid,
                        'due'                => $due,
                        'company_id'         => $ticketPurchase->company_id ?? null,
                    ]);
                }
            } else {
                // if set paid to 0, remove first history (optional behavior)
                if ($firstHistory) {
                    $firstHistory->delete();
                }
            }

            // update purchase
            $ticketPurchase->update([
                'vendor_id'   => $baseValidated['vendor_id'] ?? null,
                'customer_id' => $baseValidated['customer_id'] ?? null,
                'account_id'  => $payValidated['account_id'] ?? null,
                'flight_date' => $baseValidated['flight_date'],
                'sector'      => $baseValidated['sector'],
                'carrier'     => $baseValidated['carrier'],
                'net_fare'    => $net,
                'paid_amount' => $newPaid,
                'due_amount'  => $due,
                'issue_date'  => $baseValidated['issue_date'] ?? null,
                'notes'       => $baseValidated['notes'] ?? null,
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

            // reverse paid amount to account
            $oldPaid = (float) ($ticketPurchase->paid_amount ?? 0);
            if (!empty($ticketPurchase->account_id) && $oldPaid > 0) {
                Accounts::whereKey($ticketPurchase->account_id)
                    ->increment('current_balance', $oldPaid);
            }

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

    public function addPaymentForm(int $ticketPurchase): View
    {
        $ticketPurchase = TicketPurchases::findOrFail($ticketPurchase);

        $accounts = Accounts::where('status', 'active')
            ->orderBy('name')
            ->get();

        return view('admin.ticket_purchases.payment_history_add', [
            'ticketPurchase' => $ticketPurchase,
            'accounts' => $accounts,
        ]);
    }

    public function paymentHistory(int $ticketPurchase): View
    {
        $ticketPurchase = TicketPurchases::with(['vendor:id,name', 'customer:id,name'])
            ->findOrFail($ticketPurchase);

        $accounts = Accounts::where('status', 'active')->orderBy('name')->get();

        $paymentHistory = TicketPurchasePaymentHistory::with('account:id,name')
            ->where('ticket_purchase_id', $ticketPurchase->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('admin.ticket_purchases.payment_history', compact(
            'ticketPurchase',
            'accounts',
            'paymentHistory'
        ));
    }

    public function addPayment(Request $request, int $ticketPurchase): RedirectResponse
    {
        $ticketPurchase = TicketPurchases::findOrFail($ticketPurchase);

        $validated = $request->validate([
            'account_id' => ['required', Rule::exists('accounts', 'id')],
            'paid'       => ['required', 'numeric', 'min:0.01'],
        ]);

        $paid = (float) $validated['paid'];

        DB::transaction(function () use ($ticketPurchase, $validated, $paid) {

            $currentPaid = (float) ($ticketPurchase->paid_amount ?? 0);
            $netFare     = (float) ($ticketPurchase->net_fare ?? 0);

            $maxPayable = max(0, $netFare - $currentPaid);
            if ($paid > $maxPayable) {
                throw ValidationException::withMessages([
                    'paid' => 'Paid amount cannot be greater than due amount.',
                ]);
            }

            $newPaidTotal = $currentPaid + $paid;
            $newDue       = $netFare - $newPaidTotal;

            TicketPurchasePaymentHistory::create([
                'ticket_purchase_id' => $ticketPurchase->id,
                'account_id'         => $validated['account_id'],
                'paid'               => $paid,
                'due'                => $newDue,
                'company_id'         => $ticketPurchase->company_id ?? null,
            ]);

            $ticketPurchase->update([
                'paid_amount' => $newPaidTotal,
                'due_amount'  => $newDue,
                'account_id'  => $validated['account_id'], // last used account
            ]);

            Accounts::whereKey($validated['account_id'])
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

        return view('admin.ticket_purchases.payment_history_edit', compact('history', 'accounts'));
    }

    public function updatePaymentHistory(Request $request, int $history): RedirectResponse
    {
        $history = TicketPurchasePaymentHistory::with('ticketPurchase')->findOrFail($history);
        $ticketPurchase = $history->ticketPurchase;

        $validated = $request->validate([
            'account_id' => ['required', Rule::exists('accounts', 'id')],
            'paid'       => ['required', 'numeric', 'min:0.01'],
        ]);

        $newAccountId = (int) $validated['account_id'];
        $newPaid      = (float) $validated['paid'];

        $oldAccountId = (int) ($history->account_id ?? 0);
        $oldPaid      = (float) ($history->paid ?? 0);

        DB::transaction(function () use (
            $history,
            $ticketPurchase,
            $newAccountId,
            $newPaid,
            $oldAccountId,
            $oldPaid
        ) {
            // reverse OLD effect
            if ($oldAccountId && $oldPaid > 0) {
                Accounts::whereKey($oldAccountId)->increment('current_balance', $oldPaid);
            }

            // apply NEW effect
            if ($newAccountId && $newPaid > 0) {
                Accounts::whereKey($newAccountId)->decrement('current_balance', $newPaid);
            }

            $history->update([
                'account_id' => $newAccountId,
                'paid'       => $newPaid,
            ]);

            $this->recalculateTicketPurchasePayments($ticketPurchase->id);

            // optional: keep purchase account_id as last edited history account
            $ticketPurchase->update([
                'account_id' => $newAccountId,
            ]);
        });

        return redirect()
            ->route('ticket_purchases.payment_history', $ticketPurchase->id)
            ->with('success', 'Payment history updated successfully.');
    }

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

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Accounts;
use App\Models\Customers;
use App\Models\Reference;
use App\Models\TicketPurchases;
use App\Models\TicketSales;
use App\Models\TicketSalesPaymentHistory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class TicketSaleController extends Controller
{
    public function index(Request $request): View
    {
        $customers = Customers::orderBy('name')->get();
        $references = Reference::orderBy('company_name')->get();

        $accounts = Accounts::where('status', 'active')
            ->orderBy('name')
            ->get();

        $purchases = TicketPurchases::with([
            'vendor:id,name',
            'customer:id,name',
        ])
            ->whereNotExists(function ($q) {
                $q->selectRaw(1)
                    ->from('ticket_sales')
                    ->whereColumn('ticket_sales.purchase_id', 'ticket_purchases.id');
            })
            ->latest()
            ->get();

        $dateType = (string) $request->query('date_type', 'issue_date');
        if (!in_array($dateType, ['issue_date', 'flight_date'], true)) {
            $dateType = 'issue_date';
        }

        $ticketSalesQuery = TicketSales::with([
            'purchase:id,sector,carrier,flight_date',
            'reference:id,company_name',
            'customer:id,name',
            'account:id,name',
        ])
            ->latest();

        if ($request->filled('customer_id')) {
            $ticketSalesQuery->where('customer_id', $request->query('customer_id'));
        }

        if ($request->filled('reference_id')) {
            $ticketSalesQuery->where('reference_id', $request->query('reference_id'));
        }

        if ($request->filled('account_id')) {
            $ticketSalesQuery->where('account_id', $request->query('account_id'));
        }

        if ($request->filled('date_from')) {
            $ticketSalesQuery->whereDate($dateType, '>=', $request->query('date_from'));
        }

        if ($request->filled('date_to')) {
            $ticketSalesQuery->whereDate($dateType, '<=', $request->query('date_to'));
        }

        $ticketSales = $ticketSalesQuery->get();

        return view('admin.ticket_sales.index', [
            'customers' => $customers,
            'references' => $references,
            'accounts' => $accounts,
            'purchases' => $purchases,
            'ticketSales' => $ticketSales,
            'dateType' => $dateType,
        ]);
    }

    public function create(): RedirectResponse
    {
        return redirect()->route('ticket_sales.index');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'reference_id' => [
                'nullable',
                Rule::exists('references', 'id')->where(fn($query) => $query->where('company_id', $request->session()->get('company_id'))),
            ],
            'purchase_id' => ['nullable', Rule::exists('ticket_purchases', 'id')],
            'customer_id' => ['nullable', Rule::exists('customers', 'id')],
            'account_id' => ['nullable', Rule::exists('accounts', 'id')],
            'sell_price' => ['required', 'numeric', 'min:0'],
            'paid' => ['nullable', 'numeric', 'min:0'],
            'issue_date' => ['nullable', 'date'],
        ]);

        $due = $validated['sell_price'] - ($validated['paid'] ?? 0);

        DB::transaction(function () use ($validated, $due) {

            if (!empty($validated['account_id']) && ($validated['paid'] ?? 0) > 0) {
                Accounts::find($validated['account_id'])
                    ->increment('current_balance', $validated['paid'] ?? 0);
            }


            if (!empty($validated['purchase_id'])) {
                $ticketPurchase = TicketPurchases::find($validated['purchase_id'])
                    ->update(['customer_id' => $validated['customer_id'] ?? null]);
            }

            $ticketSale =  TicketSales::create([
                'reference_id' => $validated['reference_id'] ?? null,
                'purchase_id' => $validated['purchase_id'] ?? null,
                'customer_id' => $validated['customer_id'] ?? null,
                'account_id' => $validated['account_id'] ?? null,
                'sell_price' => $validated['sell_price'],
                'paid' => $validated['paid'] ?? 0,
                'due' => $due,
                'flight_date' =>  $ticketPurchase->flight_date ?? null,
                'issue_date' => $validated['issue_date'] ?? null,
            ]);

            if (($validated['paid'] ?? 0) > 0) {
                TicketSalesPaymentHistory::create([
                    'ticket_sales_id' =>  $ticketSale->id,
                    'account_id' => $validated['account_id'] ?? null,
                    'paid' => $validated['paid'] ?? 0,
                    'due' => $due,
                ]);
            }
        });

        return redirect()
            ->route('ticket_sales.index')
            ->with('success', 'Ticket sale saved successfully.');
    }

    public function edit(int $ticketSale): View
    {
        $ticketSale = TicketSales::findOrFail($ticketSale);

        $customers = Customers::orderBy('name')->get();
        $references = Reference::orderBy('company_name')->get();

        $accounts = Accounts::where('status', 'active')
            ->orderBy('name')
            ->get();

        $purchases = TicketPurchases::with([
            'vendor:id,name',
            'customer:id,name'
        ])
            ->latest()
            ->get();

        return view('admin.ticket_sales.edit', [
            'ticketSale' => $ticketSale,
            'customers' => $customers,
            'references' => $references,
            'accounts' => $accounts,
            'purchases' => $purchases,
        ]);
    }

    public function update(Request $request, int $ticketSale): RedirectResponse
    {
        $ticketSale = TicketSales::findOrFail($ticketSale);
        $historyCount = TicketSalesPaymentHistory::where('ticket_sales_id', $ticketSale->id)->count();

        if ($historyCount > 1) {
            $validated = $request->validate([
                'reference_id' => [
                    'nullable',
                    Rule::exists('references', 'id')->where(fn($query) => $query->where('company_id', $request->session()->get('company_id'))),
                ],
                'purchase_id' => ['nullable', Rule::exists('ticket_purchases', 'id')],
                'customer_id' => ['nullable', Rule::exists('customers', 'id')],
                'sell_price'  => ['required', 'numeric', 'min:0'],
                'issue_date'  => ['nullable', 'date'],
            ]);

            $paid = (float) ($ticketSale->paid ?? 0);
            $due  = (float) $validated['sell_price'] - $paid;
            $ticketPurchase = TicketPurchases::find($validated['purchase_id']);

            DB::transaction(function () use ($ticketSale, $validated, $due, $ticketPurchase) {


                if (!empty($validated['purchase_id'])) {
                    $ticketPurchase->update(['customer_id' => $validated['customer_id'] ?? null]);
                }


                $ticketSale->update([
                    'reference_id' => $validated['reference_id'] ?? null,
                    'purchase_id' => $validated['purchase_id'] ?? null,
                    'customer_id' => $validated['customer_id'] ?? null,
                    'sell_price'  => $validated['sell_price'],
                    'due'         => $due,
                    'flight_date' => $ticketPurchase->flight_date ?? null,
                    'issue_date'  => $validated['issue_date'] ?? null,
                ]);
            });

            return redirect()
                ->route('ticket_sales.index')
                ->with('success', 'Ticket sale updated (payment edit disabled because multiple payments exist).');
        }



        $validated = $request->validate([
            'reference_id' => [
                'nullable',
                Rule::exists('references', 'id')->where(fn($query) => $query->where('company_id', $request->session()->get('company_id'))),
            ],
            'purchase_id' => ['nullable', Rule::exists('ticket_purchases', 'id')],
            'customer_id' => ['nullable', Rule::exists('customers', 'id')],
            'account_id' => ['nullable', Rule::exists('accounts', 'id')],
            'sell_price' => ['required', 'numeric', 'min:0'],
            'paid' => ['nullable', 'numeric', 'min:0'],
            'issue_date' => ['nullable', 'date'],
        ]);

        $due = $validated['sell_price'] - ($validated['paid'] ?? 0);
        $ticketPurchase = TicketPurchases::find($validated['purchase_id']);
        DB::transaction(function () use ($ticketSale, $validated, $due, $ticketPurchase) {

            // Revert previous payment
            if (!empty($ticketSale->account_id) && ($ticketSale->paid ?? 0) > 0) {
                Accounts::find($ticketSale->account_id)
                    ->decrement('current_balance', $ticketSale->paid);
            }

            // Add new payment
            if (!empty($validated['account_id']) && ($validated['paid'] ?? 0) > 0) {
                Accounts::find($validated['account_id'])
                    ->increment('current_balance', $validated['paid'] ?? 0);
            }


            TicketSalesPaymentHistory::where('ticket_sales_id', $ticketSale->id)
                ->oldest('id')
                ->first()
                ?->update([
                    'account_id' => $validated['account_id'] ?? null,
                    'paid' => $validated['paid'] ?? 0,
                    'due' => $due,
                ]);

            $ticketSale->update([
                'reference_id' => $validated['reference_id'] ?? null,
                'purchase_id' => $validated['purchase_id'] ?? null,
                'customer_id' => $validated['customer_id'] ?? null,
                'account_id' => $validated['account_id'] ?? null,
                'sell_price' => $validated['sell_price'],
                'paid' => $validated['paid'] ?? 0,
                'due' => $due,
                'flight_date' => $ticketPurchase->flight_date ?? null,
                'issue_date' => $validated['issue_date'] ?? null,
            ]);
        });
        return redirect()
            ->route('ticket_sales.index')
            ->with('success', 'Ticket sale updated successfully.');
    }



    public function destroy(int $ticketSale): RedirectResponse
    {
        $ticketSale = TicketSales::findOrFail($ticketSale);

        if (!empty($ticketSale->account_id) && ($ticketSale->paid ?? 0) > 0) {
            Accounts::find($ticketSale->account_id)
                ->decrement('current_balance', $ticketSale->paid);
        }

        $ticketSale->delete();


        return redirect()
            ->route('ticket_sales.index')
            ->with('success', 'Ticket sale deleted successfully.');
    }

    public function paymentHistory(int $ticketSale): View
    {
        $ticketSale = TicketSales::findOrFail($ticketSale);

        $paymentHistory = TicketSalesPaymentHistory::where('ticket_sales_id', $ticketSale->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('admin.ticket_sales.payment_history', [
            'ticketSale' => $ticketSale,
            'paymentHistory' => $paymentHistory,
        ]);
    }

    public function addPaymentForm(int $ticketSale): View
    {
        $ticketSale = TicketSales::findOrFail($ticketSale);

        $accounts = Accounts::where('status', 'active')
            ->orderBy('name')
            ->get();

        return view('admin.ticket_sales.payment_history_add', [
            'ticketSale' => $ticketSale,
            'accounts' => $accounts,
        ]);
    }

    public function storePaymentHistory(Request $request, int $ticketSale): RedirectResponse
    {
        $ticketSale = TicketSales::findOrFail($ticketSale);

        $validated = $request->validate([
            'account_id' => ['nullable', Rule::exists('accounts', 'id')],
            'paid' => ['required', 'numeric', 'min:0'],
            // optional if you want:
            // 'paid_at' => ['nullable', 'date'],
            // 'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $paid = (float) $validated['paid'];
        $accountId = $validated['account_id'] ?? null;

        // ✅ Prevent paying more than sell_price (optional but recommended)
        $alreadyPaid = (float) TicketSalesPaymentHistory::where('ticket_sales_id', $ticketSale->id)->sum('paid');
        if ($paid + $alreadyPaid > (float) $ticketSale->sell_price) {
            return back()
                ->withErrors(['paid' => 'Paid amount exceeds sell price.'])
                ->withInput();
        }

        DB::transaction(function () use ($ticketSale, $accountId, $paid) {
            // 1) increment account balance (money received)
            if ($accountId && $paid > 0) {
                Accounts::whereKey($accountId)->increment('current_balance', $paid);
            }

            // 2) calculate new totals
            $newPaidTotal = (float) TicketSalesPaymentHistory::where('ticket_sales_id', $ticketSale->id)->sum('paid') + $paid;
            $due = (float) $ticketSale->sell_price - $newPaidTotal;

            // 3) create history row (store due snapshot)
            TicketSalesPaymentHistory::create([
                'ticket_sales_id' => $ticketSale->id,
                'account_id' => $accountId,
                'paid' => $paid,
                'due' => $due,
            ]);

            // 4) update ticket sale totals (and optionally account_id = last payment account)
            $ticketSale->update([
                'paid' => $newPaidTotal,
                'due' => $due,
                'account_id' => $accountId, // optional
            ]);
        });

        return redirect()
            ->route('ticket_sales.payment_history', $ticketSale->id)
            ->with('success', 'Payment added successfully.');
    }


    public function editPaymentHistory(int $ticketSale, int $history): View
    {
        $ticketSale = TicketSales::findOrFail($ticketSale);

        $historyRow = TicketSalesPaymentHistory::where('ticket_sales_id', $ticketSale->id)
            ->where('id', $history)
            ->firstOrFail();

        $accounts = Accounts::where('status', 'active')->orderBy('name')->get();

        return view('admin.ticket_sales.payment_history_edit', [
            'ticketSale' => $ticketSale,
            'historyRow' => $historyRow,
            'accounts' => $accounts,
        ]);
    }

    public function updatePaymentHistory(Request $request, int $ticketSale, int $history): RedirectResponse
    {
        $ticketSale = TicketSales::findOrFail($ticketSale);

        $historyRow = TicketSalesPaymentHistory::where('ticket_sales_id', $ticketSale->id)
            ->where('id', $history)
            ->firstOrFail();

        $validated = $request->validate([
            'account_id' => ['nullable', Rule::exists('accounts', 'id')],
            'paid' => ['required', 'numeric', 'min:0'],
        ]);

        $newAccountId = $validated['account_id'] ?? null;
        $newPaid = (float) $validated['paid'];

        // OLD values (before update)
        $oldAccountId = $historyRow->account_id;
        $oldPaid = (float) ($historyRow->paid ?? 0);

        DB::transaction(function () use (
            $ticketSale,
            $historyRow,
            $oldAccountId,
            $oldPaid,
            $newAccountId,
            $newPaid
        ) {
            // 1) Reverse old balance effect
            if ($oldAccountId && $oldPaid > 0) {
                Accounts::whereKey($oldAccountId)->decrement('current_balance', $oldPaid);
            }

            // 2) Apply new balance effect
            if ($newAccountId && $newPaid > 0) {
                Accounts::whereKey($newAccountId)->increment('current_balance', $newPaid);
            }

            // 3) Update history row
            $historyRow->update([
                'account_id' => $newAccountId,
                'paid' => $newPaid,
            ]);

            // 4) Recalculate sale totals from history rows
            $paidTotal = (float) TicketSalesPaymentHistory::where('ticket_sales_id', $ticketSale->id)->sum('paid');
            $due = (float) $ticketSale->sell_price - $paidTotal;

            $ticketSale->update([
                'paid' => $paidTotal,
                'due' => $due,
                // optional: set account_id to latest payment account
                'account_id' => $newAccountId,
            ]);

            // 5) Update "due snapshot" for ALL history rows (optional)
            // If you want each row to keep its own snapshot, remove this.
            TicketSalesPaymentHistory::where('ticket_sales_id', $ticketSale->id)
                ->update(['due' => $due]);
        });

        return redirect()
            ->route('ticket_sales.payment_history', $ticketSale->id)
            ->with('success', 'Payment history updated successfully.');
    }
}

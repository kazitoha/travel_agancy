<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Accounts;
use App\Models\Customers;
use App\Models\TicketPurchases;
use App\Models\Vendors;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class TicketPurchaseController extends Controller
{
    public function index(Request $request): View
    {
        $companyId = $request->user()->company_id;

        $vendors = Vendors::query()
            ->where($this->companyColumn('vendors'), $companyId)
            ->orderBy('name')
            ->get();

        $accounts = Accounts::query()
            ->where($this->companyColumn('accounts'), $companyId)
            ->where('status', 'active')
            ->orderBy('name')
            ->get();

        $customers = Customers::query()
            ->where($this->companyColumn('customers'), $companyId)
            ->orderBy('name')
            ->get();

        $ticketPurchases = TicketPurchases::query()
            ->where($this->companyColumn('ticket_purchases'), $companyId)
            ->with([
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
        $companyId = $request->user()->company_id;
        $vendorCompanyColumn = $this->companyColumn('vendors');
        $customerCompanyColumn = $this->companyColumn('customers');
        $accountCompanyColumn = $this->companyColumn('accounts');

        $validated = $request->validate([
            'vendor_id' => [
                'nullable',
                Rule::exists('vendors', 'id')
                    ->where(fn($query) => $query->where($vendorCompanyColumn, $companyId)),
            ],
            'customer_id' => [
                'nullable',
                Rule::exists('customers', 'id')
                    ->where(fn($query) => $query->where($customerCompanyColumn, $companyId)),
            ],
            'account_id' => [
                'nullable',
                Rule::exists('accounts', 'id')
                    ->where(fn($query) => $query->where($accountCompanyColumn, $companyId)),
            ],

            'flight_date' => ['required', 'date'],
            'sector' => ['required', 'string', 'max:255'],
            'carrier' => ['required', 'string', 'max:255'],
            'net_fare' => ['required', 'numeric', 'min:0'],
            'paid_amount' => ['nullable', 'numeric', 'min:0'],
            'due_amount' => ['nullable', 'numeric', 'min:0'],
            'issue_date' => ['nullable', 'date'],
            'notes' => ['nullable', 'string', 'max:5000'],
        ]);

        TicketPurchases::create([
            'company_id' => $companyId,
            'vendor_id' => $validated['vendor_id'] ?? null,
            'customer_id' => $validated['customer_id'] ?? null,
            'account_id' => $validated['account_id'] ?? null,
            'flight_date' => $validated['flight_date'],
            'sector' => $validated['sector'],
            'carrier' => $validated['carrier'],
            'net_fare' => $validated['net_fare'],
            'paid_amount' => $validated['paid_amount'] ?? 0,
            'due_amount' => $validated['due_amount'] ?? 0,
            'issue_date' => $validated['issue_date'] ?? null,
            'notes' => $validated['notes'] ?? null,
        ]);

        return redirect()
            ->route('ticket_purchases.index')
            ->with('success', 'Ticket purchase saved successfully.');
    }

    public function edit(Request $request, int $ticketPurchase): View
    {
        $companyId = $request->user()->company_id;
        $ticketPurchase = $this->ownedTicketPurchase($companyId, $ticketPurchase);

        $vendors = Vendors::query()
            ->where($this->companyColumn('vendors'), $companyId)
            ->orderBy('name')
            ->get();

        $accounts = Accounts::query()
            ->where($this->companyColumn('accounts'), $companyId)
            ->where('status', 'active')
            ->orderBy('name')
            ->get();

        $customers = Customers::query()
            ->where($this->companyColumn('customers'), $companyId)
            ->orderBy('name')
            ->get();

        return view('admin.ticket_purchases.edit', [
            'ticketPurchase' => $ticketPurchase,
            'vendors' => $vendors,
            'accounts' => $accounts,
            'customers' => $customers,
        ]);
    }

    public function update(Request $request, int $ticketPurchase): RedirectResponse
    {
        $companyId = $request->user()->company_id;
        $ticketPurchase = $this->ownedTicketPurchase($companyId, $ticketPurchase);
        $vendorCompanyColumn = $this->companyColumn('vendors');
        $customerCompanyColumn = $this->companyColumn('customers');
        $accountCompanyColumn = $this->companyColumn('accounts');

        $validated = $request->validate([
            'vendor_id' => [
                'nullable',
                Rule::exists('vendors', 'id')
                    ->where(fn($query) => $query->where($vendorCompanyColumn, $companyId)),
            ],
            'customer_id' => [
                'nullable',
                Rule::exists('customers', 'id')
                    ->where(fn($query) => $query->where($customerCompanyColumn, $companyId)),
            ],
            'account_id' => [
                'nullable',
                Rule::exists('accounts', 'id')
                    ->where(fn($query) => $query->where($accountCompanyColumn, $companyId)),
            ],

            'flight_date' => ['required', 'date'],
            'sector' => ['required', 'string', 'max:255'],
            'carrier' => ['required', 'string', 'max:255'],
            'net_fare' => ['required', 'numeric', 'min:0'],
            'paid_amount' => ['nullable', 'numeric', 'min:0'],
            'due_amount' => ['nullable', 'numeric', 'min:0'],
            'issue_date' => ['nullable', 'date'],
            'notes' => ['nullable', 'string', 'max:5000'],
        ]);

        $ticketPurchase->update([
            'vendor_id' => $validated['vendor_id'] ?? null,
            'customer_id' => $validated['customer_id'] ?? null,
            'account_id' => $validated['account_id'] ?? null,
            'flight_date' => $validated['flight_date'],
            'sector' => $validated['sector'],
            'carrier' => $validated['carrier'],
            'net_fare' => $validated['net_fare'],
            'paid_amount' => $validated['paid_amount'] ?? 0,
            'due_amount' => $validated['due_amount'] ?? 0,
            'issue_date' => $validated['issue_date'] ?? null,
            'notes' => $validated['notes'] ?? null,
        ]);

        return redirect()
            ->route('ticket_purchases.index')
            ->with('success', 'Ticket purchase updated successfully.');
    }

    public function destroy(Request $request, int $ticketPurchase): RedirectResponse
    {
        $companyId = $request->user()->company_id;
        $ticketPurchase = $this->ownedTicketPurchase($companyId, $ticketPurchase);
        $ticketPurchase->delete();

        return redirect()
            ->route('ticket_purchases.index')
            ->with('success', 'Ticket purchase deleted successfully.');
    }

    private function ownedTicketPurchase(int $companyId, int $ticketPurchaseId): TicketPurchases
    {
        return TicketPurchases::query()
            ->where('id', $ticketPurchaseId)
            ->where($this->companyColumn('ticket_purchases'), $companyId)
            ->firstOrFail();
    }

    private function companyColumn(string $table): string
    {
        return Schema::hasColumn($table, 'company_id') ? 'company_id' : 'companies_id';
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Accounts;
use App\Models\Customers;
use App\Models\TicketPurchases;
use App\Models\TicketSales;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class TicketSaleController extends Controller
{
    public function index(Request $request): View
    {
        $companyId = $request->user()->company_id;

        $customers = Customers::query()
            ->where($this->companyColumn('customers'), $companyId)
            ->orderBy('name')
            ->get();

        $accounts = Accounts::query()
            ->where($this->companyColumn('accounts'), $companyId)
            ->where('status', 'active')
            ->orderBy('name')
            ->get();

        $purchases = TicketPurchases::query()
            ->where($this->companyColumn('ticket_purchases'), $companyId)
            ->with(['vendor:id,name', 'customer:id,name'])
            ->latest()
            ->get();

        $ticketSales = TicketSales::query()
            ->where($this->companyColumn('ticket_sales'), $companyId)
            ->with([
                'purchase:id,sector,carrier,flight_date',
                'customer:id,name',
                'account:id,name',
            ])
            ->latest()
            ->get();

        return view('admin.ticket_sales.index', [
            'customers' => $customers,
            'accounts' => $accounts,
            'purchases' => $purchases,
            'ticketSales' => $ticketSales,
        ]);
    }

    public function create(Request $request): RedirectResponse
    {
        return redirect()->route('ticket_sales.index');
    }

    public function store(Request $request): RedirectResponse
    {
        $companyId = $request->user()->company_id;
        $customerCompanyColumn = $this->companyColumn('customers');
        $accountCompanyColumn = $this->companyColumn('accounts');
        $purchaseCompanyColumn = $this->companyColumn('ticket_purchases');

        $validated = $request->validate([
            'purchase_id' => [
                'nullable',
                Rule::exists('ticket_purchases', 'id')
                    ->where(fn($query) => $query->where($purchaseCompanyColumn, $companyId)),
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
            'sell_price' => ['required', 'numeric', 'min:0'],
            'paid' => ['nullable', 'numeric', 'min:0'],
            'due' => ['nullable', 'numeric', 'min:0'],
            'issue_date' => ['nullable', 'date'],
        ]);

        TicketSales::create([
            'company_id' => $companyId,
            'purchase_id' => $validated['purchase_id'] ?? null,
            'customer_id' => $validated['customer_id'] ?? null,
            'account_id' => $validated['account_id'] ?? null,
            'sell_price' => $validated['sell_price'],
            'paid' => $validated['paid'] ?? 0,
            'due' => $validated['due'] ?? 0,
            'issue_date' => $validated['issue_date'] ?? null,
        ]);

        return redirect()
            ->route('ticket_sales.index')
            ->with('success', 'Ticket sale saved successfully.');
    }

    public function edit(Request $request, int $ticketSale): View
    {
        $companyId = $request->user()->company_id;
        $ticketSale = $this->ownedTicketSale($companyId, $ticketSale);

        $customers = Customers::query()
            ->where($this->companyColumn('customers'), $companyId)
            ->orderBy('name')
            ->get();

        $accounts = Accounts::query()
            ->where($this->companyColumn('accounts'), $companyId)
            ->where('status', 'active')
            ->orderBy('name')
            ->get();

        $purchases = TicketPurchases::query()
            ->where($this->companyColumn('ticket_purchases'), $companyId)
            ->with(['vendor:id,name', 'customer:id,name'])
            ->latest()
            ->get();

        return view('admin.ticket_sales.edit', [
            'ticketSale' => $ticketSale,
            'customers' => $customers,
            'accounts' => $accounts,
            'purchases' => $purchases,
        ]);
    }

    public function update(Request $request, int $ticketSale): RedirectResponse
    {
        $companyId = $request->user()->company_id;
        $ticketSale = $this->ownedTicketSale($companyId, $ticketSale);
        $customerCompanyColumn = $this->companyColumn('customers');
        $accountCompanyColumn = $this->companyColumn('accounts');
        $purchaseCompanyColumn = $this->companyColumn('ticket_purchases');

        $validated = $request->validate([
            'purchase_id' => [
                'nullable',
                Rule::exists('ticket_purchases', 'id')
                    ->where(fn($query) => $query->where($purchaseCompanyColumn, $companyId)),
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
            'sell_price' => ['required', 'numeric', 'min:0'],
            'paid' => ['nullable', 'numeric', 'min:0'],
            'due' => ['nullable', 'numeric', 'min:0'],
            'issue_date' => ['nullable', 'date'],
        ]);

        $ticketSale->update([
            'purchase_id' => $validated['purchase_id'] ?? null,
            'customer_id' => $validated['customer_id'] ?? null,
            'account_id' => $validated['account_id'] ?? null,
            'sell_price' => $validated['sell_price'],
            'paid' => $validated['paid'] ?? 0,
            'due' => $validated['due'] ?? 0,
            'issue_date' => $validated['issue_date'] ?? null,
        ]);

        return redirect()
            ->route('ticket_sales.index')
            ->with('success', 'Ticket sale updated successfully.');
    }

    public function destroy(Request $request, int $ticketSale): RedirectResponse
    {
        $companyId = $request->user()->company_id;
        $ticketSale = $this->ownedTicketSale($companyId, $ticketSale);
        $ticketSale->delete();

        return redirect()
            ->route('ticket_sales.index')
            ->with('success', 'Ticket sale deleted successfully.');
    }

    private function ownedTicketSale(int $companyId, int $ticketSaleId): TicketSales
    {
        return TicketSales::query()
            ->where('id', $ticketSaleId)
            ->where($this->companyColumn('ticket_sales'), $companyId)
            ->firstOrFail();
    }

    private function companyColumn(string $table): string
    {
        return Schema::hasColumn($table, 'company_id') ? 'company_id' : 'companies_id';
    }
}

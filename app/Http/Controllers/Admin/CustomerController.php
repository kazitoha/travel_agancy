<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customers;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CustomerController extends Controller
{
    public function index(Request $request): View
    {
        $authUser = $request->user();

        $customers = Customers::query()
            ->where('company_id', $authUser->company_id)
            ->latest()
            ->get();

        return view('admin.customers.index', [
            'customers' => $customers,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $authUser = $request->user();

        $validated = $request->validate([
            'passport_number' => [
                'required',
                'string',
                'max:100',
                Rule::unique('customers', 'passport_number')->where(fn($query) => $query->where('company_id', $authUser->company_id)),
            ],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['required', 'string', 'max:30'],
            'date_of_birth' => ['required', 'date', 'before_or_equal:today'],
            'address' => ['required', 'string', 'max:2000'],
        ]);

        Customers::create([
            'company_id' => $authUser->company_id,
            'user_id' => $authUser->id,
            'passport_number' => $validated['passport_number'],
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'date_of_birth' => $validated['date_of_birth'],
            'address' => $validated['address'],
        ]);

        return redirect()
            ->route('customers.index')
            ->with('success', 'Customer created successfully.');
    }

    public function edit(Request $request, int $customer): View
    {
        $customer = $this->ownedCustomer($request, $customer);

        return view('admin.customers.edit', [
            'customer' => $customer,
        ]);
    }

    public function update(Request $request, int $customer): RedirectResponse
    {
        $customer = $this->ownedCustomer($request, $customer);

        $validated = $request->validate([
            'passport_number' => [
                'required',
                'string',
                'max:100',
                Rule::unique('customers', 'passport_number')
                    ->where(fn($query) => $query->where('company_id', $request->user()->company_id))
                    ->ignore($customer->id),
            ],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['required', 'string', 'max:30'],
            'date_of_birth' => ['required', 'date', 'before_or_equal:today'],
            'address' => ['required', 'string', 'max:2000'],
        ]);

        $customer->update($validated);

        return redirect()
            ->route('customers.index')
            ->with('success', 'Customer updated successfully.');
    }

    public function destroy(Request $request, int $customer): RedirectResponse
    {
        $customer = $this->ownedCustomer($request, $customer);
        $customer->delete();

        return redirect()
            ->route('customers.index')
            ->with('success', 'Customer deleted successfully.');
    }

    private function ownedCustomer(Request $request, int $customerId): Customers
    {
        return Customers::query()
            ->where('id', $customerId)
            ->where('company_id', $request->user()->company_id)
            ->firstOrFail();
    }
}

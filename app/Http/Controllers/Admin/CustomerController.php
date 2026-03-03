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
        $customers = Customers::latest()->get();

        return view('admin.customers.index', [
            'customers' => $customers,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'passport_number' => [
                'required',
                'string',
                'max:100',
                'unique:customers,passport_number',
            ],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['required', 'string', 'max:30'],
            'date_of_birth' => ['required', 'date', 'before_or_equal:today'],
            'address' => ['required', 'string', 'max:2000'],
        ]);

        Customers::create($validated);

        return redirect()
            ->route('customers.index')
            ->with('success', 'Customer created successfully.');
    }

    public function edit(Request $request, int $customer): View
    {
        $customer = Customers::findOrFail($customer);

        return view('admin.customers.edit', [
            'customer' => $customer,
        ]);
    }

    public function update(Request $request, int $customer): RedirectResponse
    {
        $customer = Customers::findOrFail($customer);

        $validated = $request->validate([
            'passport_number' => [
                'required',
                'string',
                'max:100',
                Rule::unique('customers', 'passport_number')->ignore($customer->id),
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
        $customer = Customers::findOrFail($customer);
        $customer->delete();

        return redirect()
            ->route('customers.index')
            ->with('success', 'Customer deleted successfully.');
    }
}

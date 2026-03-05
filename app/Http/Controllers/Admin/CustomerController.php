<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customers;
use App\Models\Reference;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CustomerController extends Controller
{
    public function index(Request $request): View
    {
        $customers = Customers::with('reference')->latest()->get();
        $references = Reference::orderBy('company_name')->get();

        return view('admin.customers.index', [
            'customers' => $customers,
            'references' => $references,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'reference_id' => [
                'nullable',
                Rule::exists('references', 'id')->where(fn($query) => $query->where('company_id', $request->session()->get('company_id'))),
            ],
            'passport_number' => [
                'nullable',
                'string',
                'max:100',
                'unique:customers,passport_number',
            ],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['required', 'string', 'max:30'],
            'date_of_birth' => ['nullable', 'date', 'before_or_equal:today'],
            'address' => ['nullable', 'string', 'max:2000'],
        ]);

        Customers::create($validated);

        return redirect()
            ->route('customers.index')
            ->with('success', 'Customer created successfully.');
    }

    public function edit(Request $request, int $customer): View
    {
        $customer = Customers::findOrFail($customer);
        $references = Reference::orderBy('company_name')->get();

        return view('admin.customers.edit', [
            'customer' => $customer,
            'references' => $references,
        ]);
    }

    public function update(Request $request, int $customer): RedirectResponse
    {
        $customer = Customers::findOrFail($customer);

        $validated = $request->validate([
            'reference_id' => [
                'nullable',
                Rule::exists('references', 'id')->where(fn($query) => $query->where('company_id', $request->session()->get('company_id'))),
            ],
            'passport_number' => [
                'nullable',
                'string',
                'max:100',
                Rule::unique('customers', 'passport_number')->ignore($customer->id),
            ],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['required', 'string', 'max:30'],
            'date_of_birth' => ['nullable', 'date', 'before_or_equal:today'],
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

    public function history(Request $request, int $customer): View
    {
        $customer = Customers::with('reference')->findOrFail($customer);

        return view('admin.customers.history', [
            'customer' => $customer,
        ]);
    }
}

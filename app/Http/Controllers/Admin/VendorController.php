<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Vendors;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VendorController extends Controller
{
    public function index(Request $request): View
    {
        $vendors = Vendors::latest()->get();

        return view('admin.vendors.index', [
            'vendors' => $vendors,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'mobile' => ['required', 'string', 'max:30'],
            'address' => ['required', 'string', 'max:2000'],
        ]);

        Vendors::create($validated);

        return redirect()
            ->route('vendors.index')
            ->with('success', 'Vendor created successfully.');
    }

    public function edit(Request $request, int $vendor): View
    {
        $vendor = Vendors::findOrFail($vendor);

        return view('admin.vendors.edit', [
            'vendor' => $vendor,
        ]);
    }

    public function update(Request $request, int $vendor): RedirectResponse
    {
        $vendor = Vendors::findOrFail($vendor);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'mobile' => ['required', 'string', 'max:30'],
            'address' => ['required', 'string', 'max:2000'],
        ]);

        $vendor->update($validated);

        return redirect()
            ->route('vendors.index')
            ->with('success', 'Vendor updated successfully.');
    }

    public function destroy(Request $request, int $vendor): RedirectResponse
    {
        $vendor = Vendors::findOrFail($vendor);
        $vendor->delete();

        return redirect()
            ->route('vendors.index')
            ->with('success', 'Vendor deleted successfully.');
    }
}

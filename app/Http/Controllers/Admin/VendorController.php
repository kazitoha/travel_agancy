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
        $authUser = $request->user();

        $vendors = Vendors::query()
            ->where('companies_id', $authUser->companies_id)
            ->latest()
            ->get();

        return view('admin.vendors.index', [
            'vendors' => $vendors,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $authUser = $request->user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'mobile' => ['required', 'string', 'max:30'],
            'address' => ['required', 'string', 'max:2000'],
        ]);

        Vendors::create([
            'companies_id' => $authUser->companies_id,
            'user_id' => $authUser->id,
            'name' => $validated['name'],
            'email' => $validated['email'],
            'mobile' => $validated['mobile'],
            'address' => $validated['address'],
        ]);

        return redirect()
            ->route('vendors.index')
            ->with('success', 'Vendor created successfully.');
    }

    public function edit(Request $request, int $vendor): View
    {
        $vendor = $this->ownedVendor($request, $vendor);

        return view('admin.vendors.edit', [
            'vendor' => $vendor,
        ]);
    }

    public function update(Request $request, int $vendor): RedirectResponse
    {
        $vendor = $this->ownedVendor($request, $vendor);

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
        $vendor = $this->ownedVendor($request, $vendor);
        $vendor->delete();

        return redirect()
            ->route('vendors.index')
            ->with('success', 'Vendor deleted successfully.');
    }

    private function ownedVendor(Request $request, int $vendorId): Vendors
    {
        return Vendors::query()
            ->where('id', $vendorId)
            ->where('companies_id', $request->user()->companies_id)
            ->firstOrFail();
    }
}

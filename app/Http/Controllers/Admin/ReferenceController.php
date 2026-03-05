<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Reference;
use App\Models\TicketPurchases;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReferenceController extends Controller
{
    public function index(Request $request): View
    {
        $references = Reference::latest()->get();

        return view('admin.references.index', [
            'references' => $references,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'company_name' => ['required', 'string', 'max:255'],
            'contact_person_name' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'address' => ['nullable', 'string', 'max:255'],
        ]);

        Reference::create($validated);

        return redirect()
            ->route('references.index')
            ->with('success', 'Reference created successfully.');
    }

    public function edit(Request $request, int $reference): View
    {
        $reference = Reference::findOrFail($reference);

        return view('admin.references.edit', [
            'reference' => $reference,
        ]);
    }

    public function update(Request $request, int $reference): RedirectResponse
    {
        $reference = Reference::findOrFail($reference);

        $validated = $request->validate([
            'company_name' => ['required', 'string', 'max:255'],
            'contact_person_name' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'address' => ['nullable', 'string', 'max:255'],
        ]);

        $reference->update($validated);

        return redirect()
            ->route('references.index')
            ->with('success', 'Reference updated successfully.');
    }

    public function destroy(Request $request, int $reference): RedirectResponse
    {
        $reference = Reference::findOrFail($reference);
        $reference->delete();

        return redirect()
            ->route('references.index')
            ->with('success', 'Reference deleted successfully.');
    }

    public function history(Request $request, int $reference): View
    {
        $reference = Reference::findOrFail($reference);

        $ticketPurchases = TicketPurchases::with([
            'customer:id,name',
            'account:id,name',
        ])
            ->whereHas('customer', fn($query) => $query->where('reference_id', $reference->id))
            ->latest()
            ->get();

        $totalTickets = $ticketPurchases->count();
        $totalPaid = (float) $ticketPurchases->sum('paid_amount');
        $totalDue = (float) $ticketPurchases->sum('due_amount');

        return view('admin.references.history', [
            'reference' => $reference,
            'ticketPurchases' => $ticketPurchases,
            'totalTickets' => $totalTickets,
            'totalPaid' => $totalPaid,
            'totalDue' => $totalDue,
        ]);
    }
}

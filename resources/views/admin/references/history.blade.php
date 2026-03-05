@extends('admin.layout.app')

@section('admin-content')
    <div class="space-y-6">
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <h1 class="text-xl font-bold text-slate-900">Reference History</h1>
                    <p class="mt-1 text-sm text-slate-500">Overall purchase history for {{ $reference->company_name }}.</p>
                </div>

                <a href="{{ route('references.index') }}"
                    class="rounded-xl border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                    Back
                </a>
            </div>
        </div>

        <div class="grid gap-4 sm:grid-cols-3">
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="text-xs text-slate-500">Total Tickets</div>
                <div class="mt-2 text-2xl font-bold text-slate-900">{{ $totalTickets ?? 0 }}</div>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="text-xs text-slate-500">Total Paid</div>
                <div class="mt-2 text-2xl font-bold text-emerald-700">
                    {{ number_format($totalPaid ?? 0, 2) }}
                </div>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="text-xs text-slate-500">Total Due</div>
                <div class="mt-2 text-2xl font-bold text-rose-700">
                    {{ number_format($totalDue ?? 0, 2) }}
                </div>
            </div>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="text-sm font-bold text-slate-900">Ticket sales</div>

            <div class="mt-5 overflow-hidden rounded-2xl border border-slate-200">
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-white">
                            <tr class="text-left text-xs uppercase tracking-wide text-slate-400">
                                <th class="px-4 py-3">Customer</th>
                                <th class="px-4 py-3">Account</th>
                                <th class="px-4 py-3">Flight date</th>
                                <th class="px-4 py-3">Sector</th>
                                <th class="px-4 py-3">Carrier</th>
                                <th class="px-4 py-3">Net fare</th>
                                <th class="px-4 py-3">Paid</th>
                                <th class="px-4 py-3">Due</th>
                                <th class="px-4 py-3">Issue date</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse ($ticketSales as $ticket)
                                <tr class="bg-white hover:bg-slate-50">
                                    <td class="px-4 py-3 text-slate-700">
                                        {{ $ticket->customer?->name ?? '—' }}
                                    </td>
                                    <td class="px-4 py-3 text-slate-700">
                                        {{ $ticket->account?->name ?? '—' }}
                                    </td>
                                    <td class="px-4 py-3 text-slate-700">
                                        {{ $ticket->purchase?->flight_date?->format('M j, Y') }}
                                    </td>
                                    <td class="px-4 py-3 text-slate-700">{{ $ticket->purchase?->sector }}</td>
                                    <td class="px-4 py-3 text-slate-700">{{ $ticket->purchase?->carrier }}</td>
                                    <td class="px-4 py-3 text-slate-700">
                                        {{ number_format((float) $ticket->sell_price, 2) }}
                                    </td>
                                    <td class="px-4 py-3 text-slate-700">
                                        {{ number_format((float) $ticket->paid, 2) }}
                                    </td>
                                    <td class="px-4 py-3 text-slate-700">
                                        {{ number_format((float) $ticket->due, 2) }}
                                    </td>
                                    <td class="px-4 py-3 text-slate-700">
                                        {{ $ticket->issue_date?->format('M j, Y') ?? '—' }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="px-6 py-10 text-center text-sm text-slate-500">
                                        No ticket sales found for this reference.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

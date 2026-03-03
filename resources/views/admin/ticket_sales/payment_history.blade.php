{{-- resources/views/admin/ticket_sales/payment_history.blade.php --}}
@extends('admin.layout.app')

@section('admin-content')
    <div class="space-y-6">
        {{-- Header / Alerts --}}
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <h1 class="text-xl font-bold text-slate-900">Payment History</h1>
                    <p class="mt-1 text-sm text-slate-500">
                        Track all payments under this ticket sale.
                    </p>
                </div>

                <div class="flex items-center gap-2">
                    <a href="{{ route('ticket_sales.index') }}"
                        class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                        Back
                    </a>

                    {{-- Optional: যদি আপনি add-payment route বানান --}}
                    {{-- <a href="{{ route('ticket_sales.payments.create', $ticketSale->id) }}"
                        class="rounded-xl bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-800">
                        Add payment
                    </a> --}}
                </div>
            </div>

            @if (session('success'))
                <div class="mt-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                    {{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div class="mt-4 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">
                    {{ session('error') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="mt-4 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">
                    {{ $errors->first() }}
                </div>
            @endif
        </div>

        {{-- Sale Summary --}}
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="text-sm font-bold text-slate-900">Ticket Sale Summary</div>

            @php
                $purchase = $ticketSale->purchase;
                $purchaseLabel = $purchase
                    ? trim(
                        collect([
                            $purchase->sector,
                            $purchase->carrier,
                            optional($purchase->flight_date)->format('M j, Y'),
                        ])->filter()->implode(' • ')
                    )
                    : null;
            @endphp

            <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                    <div class="text-xs font-semibold uppercase tracking-wide text-slate-500">Purchase</div>
                    <div class="mt-1 text-sm font-semibold text-slate-900">
                        {{ $purchaseLabel ?: '—' }}
                    </div>
                </div>

                <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                    <div class="text-xs font-semibold uppercase tracking-wide text-slate-500">Customer</div>
                    <div class="mt-1 text-sm font-semibold text-slate-900">
                        {{ $ticketSale->customer?->name ?? '—' }}
                    </div>
                </div>

                <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                    <div class="text-xs font-semibold uppercase tracking-wide text-slate-500">Sell / Paid / Due</div>
                    <div class="mt-1 text-sm font-semibold text-slate-900">
                        {{ number_format((float) $ticketSale->sell_price, 2) }}
                        <span class="text-slate-400">/</span>
                        {{ number_format((float) $ticketSale->paid, 2) }}
                        <span class="text-slate-400">/</span>
                        {{ number_format((float) $ticketSale->due, 2) }}
                    </div>
                </div>

                <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                    <div class="text-xs font-semibold uppercase tracking-wide text-slate-500">Issue date</div>
                    <div class="mt-1 text-sm font-semibold text-slate-900">
                        {{ $ticketSale->issue_date?->format('M j, Y') ?? '—' }}
                    </div>
                </div>
            </div>
        </div>

        {{-- Payment History Table --}}
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="flex items-center justify-between">
                <div class="text-sm font-bold text-slate-900">Payments</div>
                <div class="text-xs text-slate-500">
                            <a href="{{ route('ticket_sales.payment_history.add', $ticketSale->id) }}"
   class="rounded-xl bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-800">
    Add payment
</a>
                </div>
                
            </div>
    

            <div class="mt-5 overflow-hidden rounded-2xl border border-slate-200">
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-white">
                            <tr class="text-left text-xs uppercase tracking-wide text-slate-400">
                                <th class="px-4 py-3">Date</th>
                                <th class="px-4 py-3">Account</th>
                                <th class="px-4 py-3">Paid</th>
                                <th class="px-4 py-3">Due (snapshot)</th>
                                <th class="px-4 py-3">Created at</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-slate-100">
                            @forelse ($paymentHistory as $row)
                                <tr class="bg-white hover:bg-slate-50">
                                    <td class="px-4 py-3 text-slate-700">
                                        {{ $row->created_at?->format('M j, Y') ?? '—' }}
                                    </td>
                                    <td class="px-4 py-3 font-semibold text-slate-900">
                                        {{-- যদি relation থাকে: $row->account?->name --}}
                                        {{ $row->account?->name ?? ('Account #' . ($row->account_id ?? '—')) }}
                                    </td>
                                    <td class="px-4 py-3 text-slate-700">
                                        {{ number_format((float) $row->paid, 2) }}
                                    </td>
                                    <td class="px-4 py-3 text-slate-700">
                                        {{ number_format((float) $row->due, 2) }}
                                    </td>
                                    <td class="px-4 py-3 text-slate-700">
                                        {{ $row->created_at?->format('M j, Y • h:i A') ?? '—' }}
                                    </td>
                                    <td class="px-4 py-3 text-right">
                                        <a href="{{ route('ticket_sales.payment_history.edit', [$ticketSale->id, $row->id]) }}"
                                        class="rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50">
                                            Edit
                                        </a>                              
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-10 text-center text-sm text-slate-500">
                                        No payment history found for this ticket sale.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="mt-4 text-xs text-slate-500">
                Note: “Due (snapshot)” is saved at the time of payment entry.
            </div>
        </div>
    </div>
@endsection
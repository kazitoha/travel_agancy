@extends('admin.layout.app')

@section('admin-content')
    <div class="space-y-6">
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <h1 class="text-xl font-bold text-slate-900">Ticket Sales</h1>
            <p class="mt-1 text-sm text-slate-500">Record ticket sales with paid and due amounts.</p>

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

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm lg:col-span-1">
                <div class="text-sm font-bold text-slate-900">Add ticket sale</div>

                <form class="mt-5 space-y-4" method="POST" action="{{ route('ticket_sales.store') }}">
                    @csrf

                    <div>
                        <label class="text-sm font-semibold text-slate-700">Ticket purchase</label>
                        <select name="purchase_id"
                            class="mt-2 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm outline-none ring-blue-200 focus:border-blue-300 focus:ring-4">
                            <option value="">Select purchase </option>
                            @foreach ($purchases as $purchase)
                                @php
                                    $purchaseLabel = trim(
                                        collect([
                                            $purchase->sector,
                                            $purchase->carrier,
                                            optional($purchase->flight_date)->format('M j, Y'),
                                            $purchase->vendor?->name,
                                        ])
                                            ->filter()
                                            ->implode(' • '),
                                    );
                                @endphp
                                <option value="{{ $purchase->id }}" @selected(old('purchase_id') == $purchase->id)>
                                    {{ $purchaseLabel ?: 'Purchase #' . $purchase->id }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="text-sm font-semibold text-slate-700">Customer</label>
                        <select name="customer_id"
                            class="mt-2 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm outline-none ring-blue-200 focus:border-blue-300 focus:ring-4">
                            <option value="">Select customer (optional)</option>
                            @foreach ($customers as $customer)
                                <option value="{{ $customer->id }}" @selected(old('customer_id') == $customer->id)>
                                    {{ $customer->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="text-sm font-semibold text-slate-700">Account (payment to)</label>
                        <select name="account_id"
                            class="mt-2 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm outline-none ring-blue-200 focus:border-blue-300 focus:ring-4">
                            <option value="">Select account (optional)</option>
                            @foreach ($accounts as $account)
                                <option value="{{ $account->id }}" @selected(old('account_id') == $account->id)>
                                    {{ $account->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="text-sm font-semibold text-slate-700">Sell price</label>
                        <input type="number" name="sell_price" step="0.01" min="0" value="{{ old('sell_price', 0) }}"
                            class="mt-2 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm outline-none ring-blue-200 focus:border-blue-300 focus:ring-4"
                            required>
                    </div>

                    <div>
                        <label class="text-sm font-semibold text-slate-700">Paid</label>
                        <input type="number" name="paid" step="0.01" min="0" value="{{ old('paid', 0) }}"
                            class="mt-2 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm outline-none ring-blue-200 focus:border-blue-300 focus:ring-4">
                    </div>

                  

                    <div>
                        <label class="text-sm font-semibold text-slate-700">Issue date</label>
                        <input type="date" name="issue_date" value="{{ old('issue_date') }}"
                            class="mt-2 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm outline-none ring-blue-200 focus:border-blue-300 focus:ring-4">
                    </div>

                    <button type="submit"
                        class="w-full rounded-xl bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white hover:bg-slate-800">
                        Save ticket sale
                    </button>
                </form>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm lg:col-span-2">
                <div class="text-sm font-bold text-slate-900">Ticket sale list</div>

                <div class="mt-5 overflow-hidden rounded-2xl border border-slate-200">
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead class="bg-white">
                                <tr class="text-left text-xs uppercase tracking-wide text-slate-400">
                                    <th class="px-4 py-3">Purchase</th>
                                    <th class="px-4 py-3">Customer</th>
                                    <th class="px-4 py-3">Account</th>
                                    <th class="px-4 py-3">Sell price</th>
                                    <th class="px-4 py-3">Paid</th>
                                    <th class="px-4 py-3">Due</th>
                                    <th class="px-4 py-3">Issue date</th>
                                    <th class="px-4 py-3 text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @forelse ($ticketSales as $sale)
                                    <tr class="bg-white hover:bg-slate-50">
                                        <td class="px-4 py-3 font-semibold text-slate-900">
                                            {{ $sale->purchase?->sector ?? '—' }}
                                        </td>
                                        <td class="px-4 py-3 text-slate-700">
                                            {{ $sale->customer?->name ?? '—' }}
                                        </td>
                                        <td class="px-4 py-3 text-slate-700">
                                            {{ $sale->account?->name ?? '—' }}
                                        </td>
                                        <td class="px-4 py-3 text-slate-700">
                                            {{ number_format((float) $sale->sell_price, 2) }}
                                        </td>
                                        <td class="px-4 py-3 text-slate-700">
                                            {{ number_format((float) $sale->paid, 2) }}
                                        </td>
                                        <td class="px-4 py-3 text-slate-700">
                                            {{ number_format((float) $sale->due, 2) }}
                                        </td>
                                        <td class="px-4 py-3 text-slate-700">
                                            {{ $sale->issue_date?->format('M j, Y') ?? '—' }}
                                        </td>
                                        <td class="px-4 py-3">
                                            <div class="flex items-center justify-end gap-2">
                                                 <a href="{{ route('ticket_sales.payment_history', $sale->id) }}"
                                                    class="rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50">
                                                    Payments
                                                </a>
                                                <a href="{{ route('ticket_sales.edit', $sale->id) }}"
                                                    class="rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50">
                                                    Edit
                                                </a>
                                                <form method="POST" action="{{ route('ticket_sales.destroy', $sale->id) }}"
                                                    onsubmit="return confirm('Delete this ticket sale?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit"
                                                        class="rounded-lg border border-rose-200 bg-rose-50 px-3 py-1.5 text-xs font-semibold text-rose-700 hover:bg-rose-100">
                                                        Delete
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="px-6 py-10 text-center text-sm text-slate-500">No ticket sales found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

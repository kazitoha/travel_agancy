@extends('admin.layout.app')

@section('admin-content')
    <div class="space-y-6">
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <h1 class="text-xl font-bold text-slate-900">Ticket Purchase Payment History</h1>
                    <p class="mt-1 text-sm text-slate-500">
                        Track payments for this purchase.
                    </p>
                </div>

                <a href="{{ route('ticket_purchases.index') }}"
                   class="rounded-xl border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                    Back
                </a>
            </div>

            @if (session('success'))
                <div class="mt-4 rounded-xl border border-emerald-200 bg-emerald-50 p-3 text-emerald-800">
                    {{ session('success') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="mt-4 rounded-xl border border-rose-200 bg-rose-50 p-3 text-rose-800">
                    <ul class="list-disc pl-5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="mt-6 grid gap-4 sm:grid-cols-4">
                <div class="rounded-xl border border-slate-200 p-4">
                    <div class="text-xs text-slate-500">Net Fare</div>
                    <div class="mt-1 text-lg font-bold text-slate-900">{{ number_format($ticketPurchase->net_fare, 2) }}</div>
                </div>
                <div class="rounded-xl border border-slate-200 p-4">
                    <div class="text-xs text-slate-500">Paid</div>
                    <div class="mt-1 text-lg font-bold text-slate-900">{{ number_format($ticketPurchase->paid_amount, 2) }}</div>
                </div>
                <div class="rounded-xl border border-slate-200 p-4">
                    <div class="text-xs text-slate-500">Due</div>
                    <div class="mt-1 text-lg font-bold text-rose-700">{{ number_format($ticketPurchase->due_amount, 2) }}</div>
                </div>
                <div class="rounded-xl border border-slate-200 p-4">
                    <div class="text-xs text-slate-500">Vendor / Customer</div>
                    <div class="mt-1 text-sm font-semibold text-slate-900">
                        {{ optional($ticketPurchase->vendor)->name ?? '-' }}
                    </div>
                    <div class="text-sm text-slate-600">
                        {{ optional($ticketPurchase->customer)->name ?? '-' }}
                    </div>
                </div>
            </div>
        </div>

        {{-- Add payment --}}
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-base font-bold text-slate-900">Add Payment</h2>

            <form class="mt-4 grid gap-4 sm:grid-cols-3"
                  method="POST"
                  action="{{ route('ticket_purchases.add_payment', $ticketPurchase->id) }}">
                @csrf

                <div>
                    <label class="text-sm font-semibold text-slate-700">Account</label>
                    <select name="account_id"
                            class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-slate-300">
                        <option value="">Select account</option>
                        @foreach ($accounts as $account)
                            <option value="{{ $account->id }}" @selected(old('account_id') == $account->id)>
                                {{ $account->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="text-sm font-semibold text-slate-700">Paid Amount</label>
                    <input type="number" step="0.01" min="0.01" name="paid"
                           value="{{ old('paid') }}"
                           class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-slate-300"
                           placeholder="0.00">
                </div>

                <div class="flex items-end">
                    <button type="submit"
                            class="w-full rounded-xl bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-800">
                        Add Payment
                    </button>
                </div>
            </form>
        </div>

        {{-- History table --}}
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-base font-bold text-slate-900">History</h2>

            <div class="mt-4 overflow-x-auto">
                <table class="min-w-full text-left text-sm">
                    <thead>
                        <tr class="border-b border-slate-200 text-slate-600">
                            <th class="py-3 pr-4">Date</th>
                            <th class="py-3 pr-4">Account</th>
                            <th class="py-3 pr-4">Paid</th>
                            <th class="py-3 pr-4">Due After</th>
                            <th class="py-3 pr-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($paymentHistory as $row)
                            <tr>
                                <td class="py-3 pr-4 text-slate-700">
                                    {{ $row->created_at?->format('d M Y, h:i A') }}
                                </td>
                                <td class="py-3 pr-4 font-semibold text-slate-900">
                                    {{ optional($row->account)->name ?? '-' }}
                                </td>
                                <td class="py-3 pr-4 font-semibold text-emerald-700">
                                    {{ number_format($row->paid, 2) }}
                                </td>
                                <td class="py-3 pr-4 font-semibold text-rose-700">
                                    {{ number_format($row->due, 2) }}
                                </td>
                                <td class="py-3 pr-4">
    <a class="rounded-lg border border-slate-200 px-3 py-1 text-xs font-semibold text-slate-700 hover:bg-slate-50"
       href="{{ route('ticket_purchases.payment_history.edit', $row->id) }}">
        Edit
    </a>
</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="py-6 text-center text-slate-500">
                                    No payment history found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
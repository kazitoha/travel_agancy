@extends('admin.layout.app')

@section('admin-content')
    <div class="space-y-6">
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="flex items-center justify-between gap-3">
                <div>
                    <h1 class="text-xl font-bold text-slate-900">Edit ticket sale</h1>
                    <p class="mt-1 text-sm text-slate-500">Update ticket sale details.</p>
                </div>
                <a href="{{ route('ticket_sales.index') }}"
                    class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50">
                    Back
                </a>
            </div>

            @if ($errors->any())
                <div class="mt-4 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">
                    {{ $errors->first() }}
                </div>
            @endif
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <form class="space-y-4" id="account-form"  method="POST" action="{{ route('ticket_sales.update', $ticketSale->id) }}">
                @csrf
                @method('PUT')

                <div>
                    <label class="text-sm font-semibold text-slate-700">Ticket purchase</label>
                    <select name="purchase_id"
                        class="mt-2 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm outline-none ring-blue-200 focus:border-blue-300 focus:ring-4">
                        <option value="">Select purchase (optional)</option>
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
                            <option value="{{ $purchase->id }}" @selected(old('purchase_id', $ticketSale->purchase_id) == $purchase->id)>
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
                            <option value="{{ $customer->id }}" @selected(old('customer_id', $ticketSale->customer_id) == $customer->id)>
                                {{ $customer->name }}
                            </option>
                        @endforeach
                        </select>
                </div>

                <div>
                    <label class="text-sm font-semibold text-slate-700">Reference (optional)</label>
                    <select name="reference_id"
                        class="mt-2 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm outline-none ring-blue-200 focus:border-blue-300 focus:ring-4">
                        <option value="">Select reference (optional)</option>
                        @foreach ($references as $reference)
                            <option value="{{ $reference->id }}" @selected(old('reference_id', $ticketSale->reference_id) == $reference->id)>
                                {{ $reference->company_name }}
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
                            <option value="{{ $account->id }}" @selected(old('account_id', $ticketSale->account_id) == $account->id)>
                                {{ $account->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="text-sm font-semibold text-slate-700">Sell price</label>
                    <input  name="sell_price" step="0.01" min="0"
                        value="{{ old('sell_price', $ticketSale->sell_price) }}"
                        class="mt-2 w-full amount-input rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm outline-none ring-blue-200 focus:border-blue-300 focus:ring-4"
                        required>
                </div>

                <div>
                    <label class="text-sm font-semibold text-slate-700">Paid</label>
                    <input  name="paid" step="0.01" min="0"
                        value="{{ old('paid', $ticketSale->paid) }}"
                        class="mt-2 w-full amount-input rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm outline-none ring-blue-200 focus:border-blue-300 focus:ring-4">
                </div>

               
                <div>
                    <label class="text-sm font-semibold text-slate-700">Issue date</label>
                    <input type="date" name="issue_date"
                        value="{{ old('issue_date', optional($ticketSale->issue_date)->format('Y-m-d')) }}"
                        class="mt-2 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm outline-none ring-blue-200 focus:border-blue-300 focus:ring-4">
                </div>

                <div class="flex items-center justify-end gap-2">
                    <a href="{{ route('ticket_sales.index') }}"
                        class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                        Cancel
                    </a>
                    <button type="submit"
                        class="rounded-xl bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-800">
                        Save changes
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

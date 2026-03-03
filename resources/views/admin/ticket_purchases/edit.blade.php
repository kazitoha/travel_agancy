@extends('admin.layout.app')

@section('admin-content')
    <div class="space-y-6">
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="flex items-center justify-between gap-3">
                <div>
                    <h1 class="text-xl font-bold text-slate-900">Edit ticket purchase</h1>
                    <p class="mt-1 text-sm text-slate-500">Update ticket purchase details.</p>
                </div>
                <a href="{{ route('ticket_purchases.index') }}"
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
            <form class="space-y-4" method="POST" action="{{ route('ticket_purchases.update', $ticketPurchase->id) }}">
                @csrf
                @method('PUT')

                <div>
                    <label class="text-sm font-semibold text-slate-700">Vendor</label>
                    <select name="vendor_id"
                        class="mt-2 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm outline-none ring-blue-200 focus:border-blue-300 focus:ring-4">
                        <option value="">Select vendor (optional)</option>
                        @foreach ($vendors as $vendor)
                            <option value="{{ $vendor->id }}" @selected(old('vendor_id', $ticketPurchase->vendor_id) == $vendor->id)>
                                {{ $vendor->name }}
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
                            <option value="{{ $customer->id }}" @selected(old('customer_id', $ticketPurchase->customer_id) == $customer->id)>
                                {{ $customer->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="text-sm font-semibold text-slate-700">Account (payment from)</label>
                    <select name="account_id"
                        class="mt-2 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm outline-none ring-blue-200 focus:border-blue-300 focus:ring-4">
                        <option value="">Select account (optional)</option>
                        @foreach ($accounts as $account)
                            <option value="{{ $account->id }}" @selected(old('account_id', $ticketPurchase->account_id) == $account->id)>
                                {{ $account->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="text-sm font-semibold text-slate-700">Flight date</label>
                    <input type="date" name="flight_date"
                        value="{{ old('flight_date', optional($ticketPurchase->flight_date)->format('Y-m-d')) }}"
                        class="mt-2 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm outline-none ring-blue-200 focus:border-blue-300 focus:ring-4"
                        required>
                </div>

                <div>
                    <label class="text-sm font-semibold text-slate-700">Sector</label>
                    <input type="text" name="sector" value="{{ old('sector', $ticketPurchase->sector) }}"
                        class="mt-2 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm outline-none ring-blue-200 focus:border-blue-300 focus:ring-4"
                        required>
                </div>

                <div>
                    <label class="text-sm font-semibold text-slate-700">Carrier</label>
                    <input type="text" name="carrier" value="{{ old('carrier', $ticketPurchase->carrier) }}"
                        class="mt-2 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm outline-none ring-blue-200 focus:border-blue-300 focus:ring-4"
                        required>
                </div>

                <div>
                    <label class="text-sm font-semibold text-slate-700">Net fare</label>
                    <input type="number" name="net_fare" step="0.01" min="0"
                        value="{{ old('net_fare', $ticketPurchase->net_fare) }}"
                        class="mt-2 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm outline-none ring-blue-200 focus:border-blue-300 focus:ring-4"
                        required>
                </div>

                <div>
                    <label class="text-sm font-semibold text-slate-700">Paid</label>
                    <input type="number" name="paid_amount" step="0.01" min="0"
                        value="{{ old('paid_amount', $ticketPurchase->paid_amount) }}"
                        class="mt-2 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm outline-none ring-blue-200 focus:border-blue-300 focus:ring-4">
                </div>

             
                <div>
                    <label class="text-sm font-semibold text-slate-700">Issue date</label>
                    <input type="date" name="issue_date"
                        value="{{ old('issue_date', optional($ticketPurchase->issue_date)->format('Y-m-d')) }}"
                        class="mt-2 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm outline-none ring-blue-200 focus:border-blue-300 focus:ring-4">
                </div>

                <div>
                    <label class="text-sm font-semibold text-slate-700">Notes</label>
                    <textarea name="notes" rows="3"
                        class="mt-2 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm outline-none ring-blue-200 focus:border-blue-300 focus:ring-4">{{ old('notes', $ticketPurchase->notes) }}</textarea>
                </div>

                <div class="flex items-center justify-end gap-2">
                    <a href="{{ route('ticket_purchases.index') }}"
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

@extends('admin.layout.app')

@section('admin-content')
    <div class="space-y-6">
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <h1 class="text-xl font-bold text-slate-900">Edit Payment</h1>
            <p class="mt-1 text-sm text-slate-500">Update account and paid amount for this payment.</p>

            @if ($errors->any())
                <div class="mt-4 rounded-xl border border-rose-200 bg-rose-50 p-3 text-rose-800">
                    <ul class="list-disc pl-5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form class="mt-6 grid gap-4 sm:grid-cols-2"
                  method="POST"
                  action="{{ route('ticket_purchases.payment_history.update', $history->id) }}">
                @csrf
                @method('PUT')

                <div>
                    <label class="text-sm font-semibold text-slate-700">Account</label>
                    <select name="account_id"
                            class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm">
                        @foreach ($accounts as $account)
                            <option value="{{ $account->id }}"
                                @selected(old('account_id', $history->account_id) == $account->id)>
                                {{ $account->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="text-sm font-semibold text-slate-700">Paid</label>
                    <input type="number" step="0.01" min="0.01"
                           name="paid"
                           value="{{ old('paid', $history->paid) }}"
                           class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm">
                </div>

                <div class="sm:col-span-2 flex gap-3">
                    <a href="{{ route('ticket_purchases.payment_history', $history->ticket_purchase_id) }}"
                       class="rounded-xl border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                        Cancel
                    </a>
                    <button type="submit"
                            class="rounded-xl bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-800">
                        Update Payment
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
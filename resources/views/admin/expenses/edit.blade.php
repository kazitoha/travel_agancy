@extends('admin.layout.app')

@section('admin-content')
    <div class="space-y-6">
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="flex items-center justify-between gap-3">
                <div>
                    <h1 class="text-xl font-bold text-slate-900">Edit expense</h1>
                    <p class="mt-1 text-sm text-slate-500">Update expense details and balance impact.</p>
                </div>
                <a href="{{ route('expenses.index') }}"
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
            <form class="space-y-4" method="POST" action="{{ route('expenses.update', $expense->id) }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div>
                    <label class="text-sm font-semibold text-slate-700">Account</label>
                    <select name="account_id"
                        class="mt-2 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm outline-none ring-blue-200 focus:border-blue-300 focus:ring-4"
                        required>
                        <option value="">Select account</option>
                        @foreach ($accounts as $account)
                            <option value="{{ $account->id }}" @selected((string) old('account_id', $expense->account_id) === (string) $account->id)>
                                {{ $account->name }} ({{ $account->type }}) - {{ number_format((float) $account->current_balance, 2) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div>
                        <label class="text-sm font-semibold text-slate-700">Amount</label>
                        <input type="number" name="amount" value="{{ old('amount', (float) $expense->amount) }}" step="0.01"
                            min="0.01"
                            class="mt-2 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm outline-none ring-blue-200 focus:border-blue-300 focus:ring-4"
                            required>
                    </div>

                    <div>
                        <label class="text-sm font-semibold text-slate-700">Category</label>
                        <input type="text" name="category" value="{{ old('category', $expense->category) }}"
                            class="mt-2 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm outline-none ring-blue-200 focus:border-blue-300 focus:ring-4"
                            required>
                    </div>
                </div>

                <div>
                    <label class="text-sm font-semibold text-slate-700">Date and time</label>
                    <input type="datetime-local" name="spent_at"
                        value="{{ old('spent_at', optional($expense->spent_at)->format('Y-m-d\TH:i')) }}"
                        class="mt-2 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm outline-none ring-blue-200 focus:border-blue-300 focus:ring-4"
                        required>
                </div>

                <div>
                    <label class="text-sm font-semibold text-slate-700">Note</label>
                    <textarea name="note" rows="3"
                        class="mt-2 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm outline-none ring-blue-200 focus:border-blue-300 focus:ring-4">{{ old('note', $expense->note) }}</textarea>
                </div>

                <div>
                    <label class="text-sm font-semibold text-slate-700">Attachment</label>
                    <input type="file" name="attachment"
                        class="mt-2 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm outline-none ring-blue-200 focus:border-blue-300 focus:ring-4"
                        accept=".jpg,.jpeg,.png,.webp,.pdf">
                    @if ($expense->attachment_path)
                        <a class="mt-2 inline-block text-xs font-semibold text-blue-600 hover:underline"
                            href="{{ asset('storage/' . $expense->attachment_path) }}" target="_blank">Current attachment</a>
                    @endif
                </div>

                <div class="flex items-center justify-end gap-2">
                    <a href="{{ route('expenses.index') }}"
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

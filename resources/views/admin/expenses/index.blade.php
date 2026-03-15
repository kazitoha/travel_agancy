@extends('admin.layout.app')

@section('admin-content')
    <div class="space-y-6">
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <h1 class="text-xl font-bold text-slate-900">Expenses</h1>
            <p class="mt-1 text-sm text-slate-500">Log expenses and automatically deduct from selected account balance.</p>

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
                <div class="text-sm font-bold text-slate-900">Add expense</div>
                <div class="mt-1 text-xs text-slate-500">Account is required and must be active.</div>

                <form class="mt-5 space-y-4" id="account-form"  method="POST" action="{{ route('expenses.store') }}" enctype="multipart/form-data">
                    @csrf

                    <div>
    <label class="text-sm font-semibold text-slate-700">Account</label>
    <select name="account_id"
            class="searchable-select mt-2 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm outline-none ring-blue-200 focus:border-blue-300 focus:ring-4"
            required>
        <option value="">Select account</option>
        @foreach ($accounts as $account)
            <option value="{{ $account->id }}" @selected((string) old('account_id') === (string) $account->id)>
                {{ $account->name }} ({{ $account->type }}) - {{ number_format((float) $account->current_balance, 2) }}
            </option>
        @endforeach
    </select>
</div>

                    <div>
                        <label class="text-sm font-semibold text-slate-700">Amount</label>
                        <input  name="amount" value="{{ old('amount') }}" step="0.01" min="0.01"
                            class="mt-2 w-full amount-input rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm outline-none ring-blue-200 focus:border-blue-300 focus:ring-4"
                            required>
                    </div>

                    <div>
                        <label class="text-sm font-semibold text-slate-700">Category</label>
                        <input type="text" name="category" value="{{ old('category') }}"
                            class="mt-2 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm outline-none ring-blue-200 focus:border-blue-300 focus:ring-4"
                            placeholder="Food / Transport / Rent" required>
                    </div>

                    <div>
                        <label class="text-sm font-semibold text-slate-700">Date and time</label>
                        <input type="datetime-local" name="spent_at"
                            value="{{ old('spent_at', now()->format('Y-m-d\TH:i')) }}"
                            class="mt-2 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm outline-none ring-blue-200 focus:border-blue-300 focus:ring-4"
                            required>
                    </div>

                    <div>
                        <label class="text-sm font-semibold text-slate-700">Note</label>
                        <textarea name="note" rows="3"
                            class="mt-2 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm outline-none ring-blue-200 focus:border-blue-300 focus:ring-4"
                            placeholder="Optional note">{{ old('note') }}</textarea>
                    </div>

                    <div>
                        <label class="text-sm font-semibold text-slate-700">Attachment</label>
                        <input type="file" name="attachment"
                            class="mt-2 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm outline-none ring-blue-200 focus:border-blue-300 focus:ring-4"
                            accept=".jpg,.jpeg,.png,.webp,.pdf">
                    </div>

                    <button type="submit"
                        class="w-full rounded-xl bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white hover:bg-slate-800">
                        Save expense
                    </button>
                </form>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm lg:col-span-2">
                <div class="text-sm font-bold text-slate-900">Recent expenses</div>

                <div class="mt-5 overflow-hidden rounded-2xl border border-slate-200">
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead class="bg-white">
                                <tr class="text-left text-xs uppercase tracking-wide text-slate-400">
                                    <th class="px-4 py-3">Date/time</th>
                                    <th class="px-4 py-3">Account</th>
                                    <th class="px-4 py-3">Category</th>
                                    <th class="px-4 py-3">Amount</th>
                                    <th class="px-4 py-3">Note</th>
                                    <th class="px-4 py-3">Attachment</th>
                                    <th class="px-4 py-3 text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @forelse ($expenses as $expense)
                                    <tr class="bg-white hover:bg-slate-50">
                                        <td class="px-4 py-3 text-xs text-slate-600">{{ $expense->spent_at?->format('M j, Y g:i A') }}</td>
                                        <td class="px-4 py-3 text-slate-700">
                                            {{ $expense->account?->name ?? 'N/A' }}
                                            <span class="text-xs text-slate-400">({{ $expense->account?->type ?? '-' }})</span>
                                        </td>
                                        <td class="px-4 py-3 text-slate-700">{{ $expense->category }}</td>
                                        <td class="px-4 py-3 font-semibold text-rose-700">-{{ number_format((float) $expense->amount, 2) }}</td>
                                        <td class="px-4 py-3 text-slate-600">{{ $expense->note ?: '—' }}</td>
                                        <td class="px-4 py-3">
                                            @if ($expense->attachment_path)
                                                <a class="text-xs font-semibold text-blue-600 hover:underline"
                                                    href="{{ asset('storage/' . $expense->attachment_path) }}" target="_blank">View</a>
                                            @else
                                                <span class="text-xs text-slate-400">—</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3">
                                            <div class="flex items-center justify-end gap-2">
                                                <a href="{{ route('expenses.edit', $expense->id) }}"
                                                    class="rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50">
                                                    Edit
                                                </a>
                                                <form method="POST" action="{{ route('expenses.destroy', $expense->id) }}"
                                                    onsubmit="return confirm('Delete this expense?');">
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
                                        <td colspan="7" class="px-6 py-10 text-center text-sm text-slate-500">No expenses yet.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>document.addEventListener('DOMContentLoaded', function() {
    const elements = document.querySelectorAll('.searchable-select');
    
    elements.forEach(el => {
        new Choices(el, {
            searchEnabled: true,
            itemSelectText: '',
            shouldSort: false,
            classNames: {
                containerOuter: 'choices mt-2',
                containerInner: 'rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm outline-none focus:ring-4 focus:ring-blue-200',
            }
        });
    });
});</script>
@endsection

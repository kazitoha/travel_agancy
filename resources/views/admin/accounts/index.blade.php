@extends('admin.layout.app')

@section('admin-content')
    <div class="space-y-6">
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <h1 class="text-xl font-bold text-slate-900">Accounts</h1>
            <p class="mt-1 text-sm text-slate-500">Create and manage your wallets/accounts.</p>

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
                <div class="text-sm font-bold text-slate-900">Add account</div>
                <div class="mt-1 text-xs text-slate-500">Current balance is auto-set from opening balance.</div>

                <form class="mt-5 space-y-4" id="account-form" method="POST" action="{{ route('accounts.store') }}" enctype="multipart/form-data">
                    @csrf
                    <div>
                        <label class="text-sm font-semibold text-slate-700">Account name</label>
                        <input type="text" name="name" value="{{ old('name') }}"
                            class="mt-2 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm outline-none ring-blue-200 focus:border-blue-300 focus:ring-4"
                            placeholder="Cash / DBBL / bKash" required>
                    </div>

                    <div>
                        <label class="text-sm font-semibold text-slate-700">Type</label>
                        <select name="type"
                            class="mt-2 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm outline-none ring-blue-200 focus:border-blue-300 focus:ring-4"
                            required>
                            <option value="">Select account type</option>
                            @foreach ($accountTypes as $value => $label)
                                <option value="{{ $value }}" @selected(old('type') === $value)>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="text-sm font-semibold text-slate-700">Opening balance</label>
                        <input  name="opening_balance"  value="{{ old('opening_balance', 0) }}" step="0.01"
                            min="0"
                            class="mt-2 w-full amount-input rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm outline-none ring-blue-200 focus:border-blue-300 focus:ring-4"
                            required>
                    </div>

                    <div>
                        <label class="text-sm font-semibold text-slate-700">Status</label>
                        <select name="status"
                            class="mt-2 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm outline-none ring-blue-200 focus:border-blue-300 focus:ring-4"
                            required>
                            <option value="active" @selected(old('status', 'active') === 'active')>Active</option>
                            <option value="inactive" @selected(old('status') === 'inactive')>Inactive</option>
                        </select>
                    </div>

                    <div>
                        <label class="text-sm font-semibold text-slate-700">Logo (optional)</label>
                        <input type="file" name="logo" accept="image/*"
                            class="mt-2 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm outline-none ring-blue-200 focus:border-blue-300 focus:ring-4">
                    </div>

                    <button type="submit"
                        class="w-full rounded-xl bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white hover:bg-slate-800">
                        Create account
                    </button>
                </form>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm lg:col-span-2">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <div class="text-sm font-bold text-slate-900">Account list</div>
                        <div class="mt-1 text-xs text-slate-500">Total: {{ $accounts->count() }}</div>
                    </div>
                </div>

                <div class="mt-5 overflow-hidden rounded-2xl border border-slate-200">
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead class="bg-white">
                                <tr class="text-left text-xs uppercase tracking-wide text-slate-400">
                                    <th class="px-4 py-3">Logo</th>
                                    <th class="px-4 py-3">Account name</th>
                                    <th class="px-4 py-3">Type</th>
                                    <th class="px-4 py-3">Opening</th>
                                    <th class="px-4 py-3">Current</th>
                                    <th class="px-4 py-3">Status</th>
                                    <th class="px-4 py-3">Created date</th>
                                    <th class="px-4 py-3 text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @forelse ($accounts as $account)
                                    <tr class="bg-white hover:bg-slate-50">
                                        <td class="px-4 py-3">
                                            @if ($account->logo)
                                                <img src="{{ asset('storage/' . $account->logo) }}"
                                                    alt="{{ $account->name }} logo"
                                                    class="h-8 w-8 rounded-full object-cover">
                                            @else
                                                <div class="h-8 w-8 rounded-full bg-slate-100"></div>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 font-semibold text-slate-900">{{ $account->name }}</td>
                                        <td class="px-4 py-3 text-slate-700">
                                            {{ $accountTypes[$account->type] ?? ucfirst(str_replace('_', ' ', $account->type)) }}
                                        </td>
                                        <td class="px-4 py-3 text-slate-700">{{ number_format((float) $account->opening_balance, 2) }}</td>
                                        <td class="px-4 py-3 font-semibold text-slate-900">
                                            {{ number_format((float) $account->current_balance, 2) }}</td>
                                        <td class="px-4 py-3">
                                            <span
                                                class="inline-flex items-center rounded-full border px-2.5 py-1 text-xs font-semibold capitalize {{ $account->status === 'active' ? 'border-emerald-200 bg-emerald-50 text-emerald-700' : 'border-slate-200 bg-slate-100 text-slate-700' }}">
                                                {{ $account->status }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 text-xs text-slate-500">{{ $account->created_at?->format('M j, Y g:i A') }}</td>
                                        <td class="px-4 py-3">
                                            <div class="flex items-center justify-end gap-2">
                                                <a href="{{ route('accounts.edit', $account->id) }}"
                                                    class="rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50">
                                                    Edit
                                                </a>
                                                <form method="POST" action="{{ route('accounts.destroy', $account->id) }}"
                                                    onsubmit="return confirm('Delete this account?');">
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
                                        <td colspan="8" class="px-6 py-10 text-center text-sm text-slate-500">No accounts yet.</td>
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

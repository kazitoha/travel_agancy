@extends('admin.layout.app')

@section('admin-content')
    @php
        $totalExpenses = $expenses->count();
        $totalAmount = $expenses->sum('amount');
        $withAttachment = $expenses->filter(fn($item) => filled($item->attachment_path))->count();
        $todayExpenses = $expenses->filter(fn($item) => optional($item->spent_at)->isToday())->sum('amount');
    @endphp

    <div class="space-y-6">
        <!-- Hero Header -->
        <div class="relative overflow-hidden rounded-[28px] border border-slate-200 bg-gradient-to-br from-slate-950 via-slate-900 to-slate-800 p-6 shadow-sm sm:p-8">
            <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_right,rgba(255,255,255,0.14),transparent_32%)]"></div>
            <div class="absolute -right-16 -top-16 h-40 w-40 rounded-full bg-white/5 blur-3xl"></div>
            <div class="absolute -bottom-12 left-10 h-32 w-32 rounded-full bg-rose-400/10 blur-3xl"></div>

            <div class="relative flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
                <div class="max-w-3xl">
                    <div class="inline-flex items-center rounded-full border border-white/10 bg-white/5 px-3 py-1 text-xs font-semibold uppercase tracking-[0.2em] text-slate-300">
                        Expense Management
                    </div>

                    <h1 class="mt-4 text-2xl font-bold tracking-tight text-white sm:text-3xl">
                        Expenses Dashboard
                    </h1>

                    <p class="mt-3 max-w-2xl text-sm leading-6 text-slate-300">
                        Log expenses, deduct balances from active accounts automatically, and keep all spending records with notes and attachments in one place.
                    </p>
                </div>

                <div class="flex flex-wrap items-center gap-3">
                    <div class="rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-center">
                        <div class="text-xs font-medium text-slate-300">Records</div>
                        <div class="mt-1 text-lg font-bold text-white">{{ number_format($totalExpenses) }}</div>
                    </div>

                    <button command="show-modal" commandfor="expense-dialog"
                        class="inline-flex items-center gap-2 rounded-2xl bg-white px-5 py-3 text-sm font-semibold text-slate-900 shadow-lg shadow-black/10 transition hover:bg-slate-100">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                        </svg>
                        Add expense
                    </button>
                </div>
            </div>
        </div>

        <!-- Alerts -->
        <div class="space-y-3">
            @if (session('success'))
                <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800 shadow-sm">
                    {{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div class="rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-medium text-rose-800 shadow-sm">
                    {{ session('error') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-medium text-rose-800 shadow-sm">
                    {{ $errors->first() }}
                </div>
            @endif
        </div>

        <!-- Stats -->
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <div class="group rounded-3xl border border-slate-200 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Total Expenses</p>
                        <h3 class="mt-3 text-2xl font-bold text-slate-900">{{ number_format($totalExpenses) }}</h3>
                        <p class="mt-1 text-sm text-slate-500">All logged expense records</p>
                    </div>
                    <div class="rounded-2xl bg-slate-100 p-3 text-slate-600">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M3 7.5A2.25 2.25 0 015.25 5.25h13.5A2.25 2.25 0 0121 7.5v9A2.25 2.25 0 0118.75 18.75H5.25A2.25 2.25 0 013 16.5v-9Z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 9h18" />
                        </svg>
                    </div>
                </div>
            </div>

            <div class="group rounded-3xl border border-slate-200 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Total Amount</p>
                        <h3 class="mt-3 text-2xl font-bold text-rose-600">{{ number_format((float) $totalAmount, 2) }}</h3>
                        <p class="mt-1 text-sm text-slate-500">Overall expense amount</p>
                    </div>
                    <div class="rounded-2xl bg-rose-50 p-3 text-rose-600">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M12 6v12m0 0 4-4m-4 4-4-4M21 12A9 9 0 103 12a9 9 0 0018 0z" />
                        </svg>
                    </div>
                </div>
            </div>

            <div class="group rounded-3xl border border-slate-200 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">With Attachment</p>
                        <h3 class="mt-3 text-2xl font-bold text-blue-600">{{ number_format($withAttachment) }}</h3>
                        <p class="mt-1 text-sm text-slate-500">Receipts or files attached</p>
                    </div>
                    <div class="rounded-2xl bg-blue-50 p-3 text-blue-600">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M18.375 12.739 11.318 19.8a4.5 4.5 0 01-6.364-6.364l8.47-8.47a3 3 0 114.243 4.243l-8.485 8.485a1.5 1.5 0 11-2.121-2.121l7.425-7.425" />
                        </svg>
                    </div>
                </div>
            </div>

            <div class="group rounded-3xl border border-slate-200 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Today</p>
                        <h3 class="mt-3 text-2xl font-bold text-slate-900">{{ number_format((float) $todayExpenses, 2) }}</h3>
                        <p class="mt-1 text-sm text-slate-500">Spent today</p>
                    </div>
                    <div class="rounded-2xl bg-amber-50 p-3 text-amber-600">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M12 6v6l4 2m6-2a10 10 0 11-20 0 10 10 0 0120 0z" />
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal -->
        <el-dialog>
            <dialog id="expense-dialog" aria-labelledby="expense-dialog-title"
                class="fixed inset-0 z-50 size-auto max-h-none max-w-none overflow-y-auto bg-transparent backdrop:bg-transparent">
                <el-dialog-backdrop
                    class="fixed inset-0 bg-slate-950/60 backdrop-blur-sm transition-opacity data-closed:opacity-0 data-enter:duration-300 data-enter:ease-out data-leave:duration-200 data-leave:ease-in">
                </el-dialog-backdrop>

                <div tabindex="0" class="flex min-h-full items-center justify-center p-4 text-center focus:outline-none sm:p-6">
                    <el-dialog-panel
                        class="relative w-full max-w-5xl transform overflow-hidden rounded-[28px] bg-white text-left shadow-2xl outline outline-1 outline-slate-200 transition-all data-closed:translate-y-4 data-closed:opacity-0 data-enter:duration-300 data-enter:ease-out data-leave:duration-200 data-leave:ease-in data-closed:sm:translate-y-0 data-closed:sm:scale-95">

                        <div class="border-b border-slate-200 bg-gradient-to-r from-slate-50 to-white px-6 py-5 sm:px-8">
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <div class="inline-flex items-center rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-slate-500">
                                        Create New
                                    </div>
                                    <h3 id="expense-dialog-title" class="mt-3 text-xl font-bold text-slate-900">
                                        Add Expense
                                    </h3>
                                    <p class="mt-1 text-sm text-slate-500">
                                        Log an expense and deduct it from the selected active account balance.
                                    </p>
                                </div>

                                <button type="button" command="close" commandfor="expense-dialog"
                                    class="inline-flex h-11 w-11 items-center justify-center rounded-2xl border border-slate-200 bg-white text-slate-500 shadow-sm transition hover:bg-slate-100 hover:text-slate-700">
                                    ✕
                                </button>
                            </div>
                        </div>

                        <form class="px-6 py-6 sm:px-8" id="account-form" method="POST" action="{{ route('expenses.store') }}" enctype="multipart/form-data">
                            @csrf

                            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                                <div class="md:col-span-2">
                                    <label class="text-sm font-semibold text-slate-700">Account</label>
                                    <select name="account_id"
                                        class="searchable-select mt-2 w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700 outline-none ring-blue-200 transition focus:border-blue-300 focus:bg-white focus:ring-4"
                                        required>
                                        <option value="">Select account</option>
                                        @foreach ($accounts as $account)
                                            <option value="{{ $account->id }}" @selected((string) old('account_id') === (string) $account->id)>
                                                {{ $account->name }} ({{ $account->type }}) - {{ number_format((float) $account->current_balance, 2) }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <p class="mt-2 text-xs text-slate-500">Account is required and must be active.</p>
                                </div>

                                <div>
                                    <label class="text-sm font-semibold text-slate-700">Amount</label>
                                    <input type="number" name="amount" value="{{ old('amount') }}" step="0.01" min="0.01"
                                        required
                                        class="mt-2 w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700 outline-none ring-blue-200 transition focus:border-blue-300 focus:bg-white focus:ring-4">
                                </div>

                                <div>
                                    <label class="text-sm font-semibold text-slate-700">Category</label>
                                    <input type="text" name="category" value="{{ old('category') }}"
                                        placeholder="Food / Transport / Rent" required
                                        class="mt-2 w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700 outline-none ring-blue-200 transition focus:border-blue-300 focus:bg-white focus:ring-4">
                                </div>

                                <div>
                                    <label class="text-sm font-semibold text-slate-700">Date and time</label>
                                    <input type="datetime-local" name="spent_at"
                                        value="{{ old('spent_at', now()->format('Y-m-d\TH:i')) }}" required
                                        class="mt-2 w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700 outline-none ring-blue-200 transition focus:border-blue-300 focus:bg-white focus:ring-4">
                                </div>

                                <div>
                                    <label class="text-sm font-semibold text-slate-700">Attachment</label>
                                    <input type="file" name="attachment"
                                        accept=".jpg,.jpeg,.png,.webp,.pdf"
                                        class="mt-2 w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700 file:mr-4 file:rounded-xl file:border-0 file:bg-slate-900 file:px-3 file:py-2 file:text-sm file:font-semibold file:text-white hover:file:bg-slate-800">
                                </div>

                                <div class="md:col-span-2">
                                    <label class="text-sm font-semibold text-slate-700">Note</label>
                                    <textarea name="note" rows="4"
                                        placeholder="Optional note"
                                        class="mt-2 w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700 outline-none ring-blue-200 transition focus:border-blue-300 focus:bg-white focus:ring-4">{{ old('note') }}</textarea>
                                </div>
                            </div>

                            <div class="mt-8 flex flex-col-reverse gap-3 border-t border-slate-200 pt-5 sm:flex-row sm:justify-end">
                                <button type="button" command="close" commandfor="expense-dialog"
                                    class="inline-flex justify-center rounded-2xl border border-slate-200 bg-white px-5 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                                    Cancel
                                </button>

                                <button type="submit"
                                    class="inline-flex justify-center rounded-2xl bg-slate-900 px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-slate-800">
                                    Save expense
                                </button>
                            </div>
                        </form>
                    </el-dialog-panel>
                </div>
            </dialog>
        </el-dialog>

        <!-- Table -->
        <div class="overflow-hidden rounded-[28px] border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-200 px-6 py-5">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h2 class="text-lg font-bold text-slate-900">Recent Expenses</h2>
                        <p class="text-sm text-slate-500">Review expenses, check attachments, and manage records.</p>
                    </div>

                    <div class="inline-flex items-center rounded-full bg-slate-100 px-3 py-1.5 text-xs font-semibold text-slate-600">
                        {{ number_format($totalExpenses) }} records
                    </div>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-slate-50">
                        <tr class="text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                            <th class="px-5 py-4">Date / Time</th>
                            <th class="px-5 py-4">Account</th>
                            <th class="px-5 py-4">Category</th>
                            <th class="px-5 py-4">Amount</th>
                            <th class="px-5 py-4">Note</th>
                            <th class="px-5 py-4">Attachment</th>
                            <th class="px-5 py-4 text-right">Actions</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-100 bg-white">
                        @forelse ($expenses as $expense)
                            <tr class="transition hover:bg-slate-50/80">
                                <td class="px-5 py-4">
                                    <div class="font-semibold text-slate-900">
                                        {{ $expense->spent_at?->format('M j, Y') ?? '—' }}
                                    </div>
                                    <div class="mt-1 text-xs text-slate-500">
                                        {{ $expense->spent_at?->format('g:i A') ?? '—' }}
                                    </div>
                                </td>

                                <td class="px-5 py-4">
                                    <div class="font-semibold text-slate-900">
                                        {{ $expense->account?->name ?? 'N/A' }}
                                    </div>
                                    <div class="mt-1 text-xs text-slate-500">
                                        {{ $expense->account?->type ?? '-' }}
                                    </div>
                                </td>

                                <td class="px-5 py-4 text-slate-700">
                                    {{ $expense->category }}
                                </td>

                                <td class="px-5 py-4">
                                    <span class="inline-flex rounded-full bg-rose-50 px-3 py-1 text-xs font-semibold text-rose-700 ring-1 ring-inset ring-rose-200">
                                        -{{ number_format((float) $expense->amount, 2) }}
                                    </span>
                                </td>

                                <td class="max-w-[260px] px-5 py-4 text-slate-700">
                                    <p class="truncate" title="{{ $expense->note }}">
                                        {{ $expense->note ?: '—' }}
                                    </p>
                                </td>

                                <td class="px-5 py-4">
                                    @if ($expense->attachment_path)
                                        <a href="{{ asset('storage/' . $expense->attachment_path) }}" target="_blank"
                                            class="inline-flex items-center rounded-xl border border-blue-200 bg-blue-50 px-3 py-2 text-xs font-semibold text-blue-700 transition hover:bg-blue-100">
                                            View file
                                        </a>
                                    @else
                                        <span class="text-sm text-slate-400">—</span>
                                    @endif
                                </td>

                                <td class="px-5 py-4">
                                    <div class="flex flex-wrap items-center justify-end gap-2">
                                        <a href="{{ route('expenses.edit', $expense->id) }}"
                                            class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-semibold text-slate-700 transition hover:bg-slate-100">
                                            Edit
                                        </a>

                                        <form method="POST" action="{{ route('expenses.destroy', $expense->id) }}"
                                            onsubmit="return confirm('Delete this expense?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                class="rounded-xl border border-rose-200 bg-rose-50 px-3 py-2 text-xs font-semibold text-rose-700 transition hover:bg-rose-100">
                                                Delete
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-20">
                                    <div class="flex flex-col items-center justify-center text-center">
                                        <div class="flex h-16 w-16 items-center justify-center rounded-full bg-slate-100 text-slate-400">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none"
                                                viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M21 8.25V6.108c0-1.135-.845-2.1-1.973-2.193A48.424 48.424 0 0012 3.75c-2.331 0-4.616.164-6.777.474C4.095 4.318 3.25 5.283 3.25 6.418V8.25m17.75 0v10.5A2.25 2.25 0 0118.75 21H5.25A2.25 2.25 0 013 18.75V8.25m18 0H3" />
                                            </svg>
                                        </div>

                                        <h3 class="mt-4 text-base font-semibold text-slate-900">No expenses yet</h3>
                                        <p class="mt-1 max-w-md text-sm text-slate-500">
                                            Start logging expenses to track account deductions, notes, and attachments in one place.
                                        </p>

                                        <button command="show-modal" commandfor="expense-dialog"
                                            class="mt-5 inline-flex items-center rounded-2xl bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white hover:bg-slate-800">
                                            Add expense
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const elements = document.querySelectorAll('.searchable-select');

            elements.forEach(el => {
                new Choices(el, {
                    searchEnabled: true,
                    itemSelectText: '',
                    shouldSort: false,
                    classNames: {
                        containerOuter: 'choices mt-2',
                        containerInner: 'rounded-2xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm outline-none focus:ring-4 focus:ring-blue-200',
                    }
                });
            });
        });
    </script>
@endsection
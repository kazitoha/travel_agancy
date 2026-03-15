@extends('admin.layout.app')

@section('admin-content')
    @php
        $totalSales = $ticketSales->count();
        $totalSell = $ticketSales->sum('sell_price');
        $totalPaid = $ticketSales->sum('paid');
        $totalDue = $ticketSales->sum('due');

        $activeFiltersCount = collect([
            request('customer_id'),
            request('reference_id'),
            request('account_id'),
            request('date_from'),
            request('date_to'),
        ])->filter(fn ($value) => filled($value))->count();
    @endphp

    <div class="space-y-6">
        <!-- Hero Header -->
        <div class="relative overflow-hidden rounded-[28px] border border-slate-200 bg-gradient-to-br from-slate-950 via-slate-900 to-slate-800 p-6 shadow-sm sm:p-8">
            <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_right,rgba(255,255,255,0.14),transparent_32%)]"></div>
            <div class="absolute -right-16 -top-16 h-40 w-40 rounded-full bg-white/5 blur-3xl"></div>
            <div class="absolute -bottom-12 left-10 h-32 w-32 rounded-full bg-blue-400/10 blur-3xl"></div>

            <div class="relative flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
                <div class="max-w-3xl">
                    <div class="inline-flex items-center rounded-full border border-white/10 bg-white/5 px-3 py-1 text-xs font-semibold uppercase tracking-[0.2em] text-slate-300">
                        Ticket Management
                    </div>

                    <h1 class="mt-4 text-2xl font-bold tracking-tight text-white sm:text-3xl">
                        Ticket Sales Dashboard
                    </h1>

                    <p class="mt-3 max-w-2xl text-sm leading-6 text-slate-300">
                        Manage ticket sales, monitor paid and due balances, and keep customer, account, and reference data organized from one clean workspace.
                    </p>
                </div>

                <div class="flex flex-wrap items-center gap-3">
                    <div class="rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-center">
                        <div class="text-xs font-medium text-slate-300">Records</div>
                        <div class="mt-1 text-lg font-bold text-white">{{ number_format($totalSales) }}</div>
                    </div>

                    <button command="show-modal" commandfor="ticket-sale-dialog"
                        class="inline-flex items-center gap-2 rounded-2xl bg-white px-5 py-3 text-sm font-semibold text-slate-900 shadow-lg shadow-black/10 transition hover:bg-slate-100">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                        </svg>
                        Add ticket sale
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

    

        <!-- Modal -->
        <el-dialog>
            <dialog id="ticket-sale-dialog" aria-labelledby="ticket-sale-dialog-title"
                class="fixed inset-0 z-50 size-auto max-h-none max-w-none overflow-y-auto bg-transparent backdrop:bg-transparent">
                <el-dialog-backdrop
                    class="fixed inset-0 bg-slate-950/60 backdrop-blur-sm transition-opacity data-closed:opacity-0 data-enter:duration-300 data-enter:ease-out data-leave:duration-200 data-leave:ease-in">
                </el-dialog-backdrop>

                <div tabindex="0" class="flex min-h-full items-center justify-center p-4 text-center focus:outline-none sm:p-6">
                    <el-dialog-panel
                        class="relative w-full max-w-4xl transform overflow-hidden rounded-[28px] bg-white text-left shadow-2xl outline outline-1 outline-slate-200 transition-all data-closed:translate-y-4 data-closed:opacity-0 data-enter:duration-300 data-enter:ease-out data-leave:duration-200 data-leave:ease-in data-closed:sm:translate-y-0 data-closed:sm:scale-95">

                        <div class="border-b border-slate-200 bg-gradient-to-r from-slate-50 to-white px-6 py-5 sm:px-8">
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <div class="inline-flex items-center rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-slate-500">
                                        Create New
                                    </div>
                                    <h3 id="ticket-sale-dialog-title" class="mt-3 text-xl font-bold text-slate-900">
                                        Add Ticket Sale
                                    </h3>
                                    <p class="mt-1 text-sm text-slate-500">
                                        Fill in the sale details below and save the record.
                                    </p>
                                </div>

                                <button type="button" command="close" commandfor="ticket-sale-dialog"
                                    class="inline-flex h-11 w-11 items-center justify-center rounded-2xl border border-slate-200 bg-white text-slate-500 shadow-sm transition hover:bg-slate-100 hover:text-slate-700">
                                    ✕
                                </button>
                            </div>
                        </div>

                        <form method="POST" id="account-form" action="{{ route('ticket_sales.store') }}"
                            class="px-6 py-6 sm:px-8">
                            @csrf

                            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                                <div class="md:col-span-2">
                                    <label class="text-sm font-semibold text-slate-700">Ticket purchase</label>
                                    <select name="purchase_id"
                                        class="mt-2 w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700 outline-none ring-blue-200 transition focus:border-blue-300 focus:bg-white focus:ring-4">
                                        <option value="">Select purchase</option>
                                        @foreach ($purchases as $purchase)
                                            @php
                                                $purchaseLabel = trim(
                                                    collect([
                                                        $purchase->sector,
                                                        $purchase->carrier,
                                                        optional($purchase->flight_date)->format('M j, Y'),
                                                        $purchase->vendor?->name,
                                                    ])->filter()->implode(' • '),
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
                                        class="mt-2 w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700 outline-none ring-blue-200 transition focus:border-blue-300 focus:bg-white focus:ring-4">
                                        <option value="">Select customer (optional)</option>
                                        @foreach ($customers as $customer)
                                            <option value="{{ $customer->id }}" @selected(old('customer_id') == $customer->id)>
                                                {{ $customer->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div>
                                    <label class="text-sm font-semibold text-slate-700">Reference</label>
                                    <select name="reference_id"
                                        class="mt-2 w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700 outline-none ring-blue-200 transition focus:border-blue-300 focus:bg-white focus:ring-4">
                                        <option value="">Select reference (optional)</option>
                                        @foreach ($references as $reference)
                                            <option value="{{ $reference->id }}" @selected(old('reference_id') == $reference->id)>
                                                {{ $reference->company_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div>
                                    <label class="text-sm font-semibold text-slate-700">Account (payment to)</label>
                                    <select name="account_id"
                                        class="mt-2 w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700 outline-none ring-blue-200 transition focus:border-blue-300 focus:bg-white focus:ring-4">
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
                                    <input name="sell_price" step="0.01" min="0"
                                        value="{{ old('sell_price', 0) }}" required
                                        class="mt-2 w-full amount-input rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700 outline-none ring-blue-200 transition focus:border-blue-300 focus:bg-white focus:ring-4">
                                </div>

                                <div>
                                    <label class="text-sm font-semibold text-slate-700">Paid</label>
                                    <input  name="paid" step="0.01" min="0"
                                        value="{{ old('paid', 0) }}"
                                        class="mt-2 w-full amount-input rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700 outline-none ring-blue-200 transition focus:border-blue-300 focus:bg-white focus:ring-4">
                                </div>

                                <div>
                                    <label class="text-sm font-semibold text-slate-700">Flight date</label>
                                    <input type="date" name="flight_date" value="{{ old('flight_date') }}"
                                        class="mt-2 w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700 outline-none ring-blue-200 transition focus:border-blue-300 focus:bg-white focus:ring-4">
                                </div>

                                <div>
                                    <label class="text-sm font-semibold text-slate-700">Issue date</label>
                                    <input type="date" name="issue_date" value="{{ old('issue_date') }}"
                                        class="mt-2 w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700 outline-none ring-blue-200 transition focus:border-blue-300 focus:bg-white focus:ring-4">
                                </div>
                            </div>

                            <div class="mt-8 flex flex-col-reverse gap-3 border-t border-slate-200 pt-5 sm:flex-row sm:justify-end">
                                <button type="button" command="close" commandfor="ticket-sale-dialog"
                                    class="inline-flex justify-center rounded-2xl border border-slate-200 bg-white px-5 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                                    Cancel
                                </button>

                                <button type="submit"
                                    class="inline-flex justify-center rounded-2xl bg-slate-900 px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-slate-800">
                                    Save ticket sale
                                </button>
                            </div>
                        </form>
                    </el-dialog-panel>
                </div>
            </dialog>
        </el-dialog>

        <!-- Filters -->
        <div class="rounded-[28px] border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
            <div class="flex flex-col gap-3 border-b border-slate-200 pb-5 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-lg font-bold text-slate-900">Filter Sales</h2>
                    <p class="text-sm text-slate-500">Narrow records by customer, reference, account, and date range.</p>
                </div>

                @if ($activeFiltersCount > 0)
                    <div class="inline-flex items-center rounded-full bg-blue-50 px-3 py-1.5 text-xs font-semibold text-blue-700 ring-1 ring-inset ring-blue-200">
                        {{ $activeFiltersCount }} active filter{{ $activeFiltersCount > 1 ? 's' : '' }}
                    </div>
                @endif
            </div>

            <form method="GET" action="{{ route('ticket_sales.index') }}"
                class="mt-5 grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-6">
                <div class="xl:col-span-2">
                    <label class="text-xs font-semibold uppercase tracking-wide text-slate-500">Customer</label>
                    <select name="customer_id"
                        class="mt-2 w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700 outline-none ring-blue-200 transition focus:border-blue-300 focus:bg-white focus:ring-4">
                        <option value="">All customers</option>
                        @foreach ($customers as $customer)
                            <option value="{{ $customer->id }}" @selected((string) request('customer_id') === (string) $customer->id)>
                                {{ $customer->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="xl:col-span-2">
                    <label class="text-xs font-semibold uppercase tracking-wide text-slate-500">Reference</label>
                    <select name="reference_id"
                        class="mt-2 w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700 outline-none ring-blue-200 transition focus:border-blue-300 focus:bg-white focus:ring-4">
                        <option value="">All references</option>
                        @foreach ($references as $reference)
                            <option value="{{ $reference->id }}" @selected((string) request('reference_id') === (string) $reference->id)>
                                {{ $reference->company_name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="xl:col-span-2">
                    <label class="text-xs font-semibold uppercase tracking-wide text-slate-500">Account</label>
                    <select name="account_id"
                        class="mt-2 w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700 outline-none ring-blue-200 transition focus:border-blue-300 focus:bg-white focus:ring-4">
                        <option value="">All accounts</option>
                        @foreach ($accounts as $account)
                            <option value="{{ $account->id }}" @selected((string) request('account_id') === (string) $account->id)>
                                {{ $account->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="text-xs font-semibold uppercase tracking-wide text-slate-500">Date type</label>
                    <select name="date_type"
                        class="mt-2 w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700 outline-none ring-blue-200 transition focus:border-blue-300 focus:bg-white focus:ring-4">
                        <option value="issue_date" @selected(($dateType ?? 'issue_date') === 'issue_date')>Issue date</option>
                        <option value="flight_date" @selected(($dateType ?? 'issue_date') === 'flight_date')>Flight date</option>
                    </select>
                </div>

                <div>
                    <label class="text-xs font-semibold uppercase tracking-wide text-slate-500">From</label>
                    <input type="date" name="date_from" value="{{ request('date_from') }}"
                        class="mt-2 w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700 outline-none ring-blue-200 transition focus:border-blue-300 focus:bg-white focus:ring-4">
                </div>

                <div>
                    <label class="text-xs font-semibold uppercase tracking-wide text-slate-500">To</label>
                    <input type="date" name="date_to" value="{{ request('date_to') }}"
                        class="mt-2 w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700 outline-none ring-blue-200 transition focus:border-blue-300 focus:bg-white focus:ring-4">
                </div>

                <div class="flex flex-wrap items-end gap-3 md:col-span-2 xl:col-span-6">
                    <button type="submit"
                        class="rounded-2xl bg-slate-900 px-5 py-3 text-sm font-semibold text-white transition hover:bg-slate-800">
                        Apply filters
                    </button>

                    <a href="{{ route('ticket_sales.index') }}"
                        class="rounded-2xl border border-slate-200 bg-white px-5 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-100">
                        Reset
                    </a>
                </div>
            </form>
        </div>



            <!-- Stats -->
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <div class="group rounded-3xl border border-slate-200 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Total Sales</p>
                        <h3 class="mt-3 text-2xl font-bold text-slate-900">{{ number_format($totalSales) }}</h3>
                        <p class="mt-1 text-sm text-slate-500">All recorded ticket sales</p>
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
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Total Sell</p>
                        <h3 class="mt-3 text-2xl font-bold text-slate-900">{{ number_format((float) $totalSell, 2) }}</h3>
                        <p class="mt-1 text-sm text-slate-500">Total sale amount</p>
                    </div>
                    <div class="rounded-2xl bg-blue-50 p-3 text-blue-600">
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
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Total Paid</p>
                        <h3 class="mt-3 text-2xl font-bold text-emerald-600">{{ number_format((float) $totalPaid, 2) }}</h3>
                        <p class="mt-1 text-sm text-slate-500">Collected payments</p>
                    </div>
                    <div class="rounded-2xl bg-emerald-50 p-3 text-emerald-600">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
            </div>

            <div class="group rounded-3xl border border-slate-200 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Total Due</p>
                        <h3 class="mt-3 text-2xl font-bold text-rose-600">{{ number_format((float) $totalDue, 2) }}</h3>
                        <p class="mt-1 text-sm text-slate-500">Outstanding balance</p>
                    </div>
                    <div class="rounded-2xl bg-rose-50 p-3 text-rose-600">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M12 9v3.75m0 3.75h.007v.008H12v-.008zm9-3.758a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
            </div>
        </div>
        <!-- Table -->
        <div class="overflow-hidden rounded-[28px] border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-200 px-6 py-5">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h2 class="text-lg font-bold text-slate-900">Ticket Sale List</h2>
                        <p class="text-sm text-slate-500">Review records, edit entries, and manage payment history.</p>
                    </div>

                    <div class="inline-flex items-center rounded-full bg-slate-100 px-3 py-1.5 text-xs font-semibold text-slate-600">
                        {{ number_format($totalSales) }} records
                    </div>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-slate-50">
                        <tr class="text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                            <th class="px-5 py-4">Purchase</th>
                            <th class="px-5 py-4">Customer</th>
                            <th class="px-5 py-4">Reference</th>
                            <th class="px-5 py-4">Account</th>
                            <th class="px-5 py-4">Sell price</th>
                            <th class="px-5 py-4">Paid</th>
                            <th class="px-5 py-4">Due</th>
                            <th class="px-5 py-4">Flight date</th>
                            <th class="px-5 py-4">Issue date</th>
                            <th class="px-5 py-4 text-right">Actions</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-100 bg-white">
                        @forelse ($ticketSales as $sale)
                            <tr class="transition hover:bg-slate-50/80">
                                <td class="px-5 py-4">
                                    <div class="font-semibold text-slate-900">
                                        {{ $sale->purchase?->sector ?? '—' }}
                                    </div>
                                    <div class="mt-1 text-xs text-slate-500">
                                        {{ $sale->purchase?->carrier ?? 'No carrier' }}
                                    </div>
                                </td>

                                <td class="px-5 py-4 text-slate-700">
                                    {{ $sale->customer?->name ?? '—' }}
                                </td>

                                <td class="px-5 py-4 text-slate-700">
                                    {{ $sale->reference?->company_name ?? '—' }}
                                </td>

                                <td class="px-5 py-4 text-slate-700">
                                    {{ $sale->account?->name ?? '—' }}
                                </td>

                                <td class="px-5 py-4 font-semibold text-slate-900">
                                    {{ number_format((float) $sale->sell_price, 2) }}
                                </td>

                                <td class="px-5 py-4 font-semibold text-emerald-600">
                                    {{ number_format((float) $sale->paid, 2) }}
                                </td>

                                <td class="px-5 py-4">
                                    <span
                                        class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold {{ (float) $sale->due > 0 ? 'bg-rose-50 text-rose-700 ring-1 ring-inset ring-rose-200' : 'bg-emerald-50 text-emerald-700 ring-1 ring-inset ring-emerald-200' }}">
                                        {{ number_format((float) $sale->due, 2) }}
                                    </span>
                                </td>

                                <td class="px-5 py-4 text-slate-700">
                                    {{ $sale->flight_date?->format('M j, Y') ?? '—' }}
                                </td>

                                <td class="px-5 py-4 text-slate-700">
                                    {{ $sale->issue_date?->format('M j, Y') ?? '—' }}
                                </td>

                                <td class="px-5 py-4">
                                    <div class="flex flex-wrap items-center justify-end gap-2">
                                        <a href="{{ route('ticket_sales.payment_history', $sale->id) }}"
                                            class="rounded-xl border border-blue-200 bg-blue-50 px-3 py-2 text-xs font-semibold text-blue-700 transition hover:bg-blue-100">
                                            Payments
                                        </a>

                                        <a href="{{ route('ticket_sales.edit', $sale->id) }}"
                                            class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-semibold text-slate-700 transition hover:bg-slate-100">
                                            Edit
                                        </a>

                                        <form method="POST" action="{{ route('ticket_sales.destroy', $sale->id) }}"
                                            onsubmit="return confirm('Delete this ticket sale?');">
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
                                <td colspan="10" class="px-6 py-20">
                                    <div class="flex flex-col items-center justify-center text-center">
                                        <div class="flex h-16 w-16 items-center justify-center rounded-full bg-slate-100 text-slate-400">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none"
                                                viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M3 7.5A2.25 2.25 0 015.25 5.25h13.5A2.25 2.25 0 0121 7.5v9A2.25 2.25 0 0118.75 18.75H5.25A2.25 2.25 0 013 16.5v-9Z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 9h18" />
                                            </svg>
                                        </div>

                                        <h3 class="mt-4 text-base font-semibold text-slate-900">No ticket sales found</h3>
                                        <p class="mt-1 max-w-md text-sm text-slate-500">
                                            There are no records matching your current filters. Add a new sale or reset filters to see more results.
                                        </p>

                                        <div class="mt-5 flex flex-wrap items-center justify-center gap-3">
                                            <button command="show-modal" commandfor="ticket-sale-dialog"
                                                class="inline-flex items-center rounded-2xl bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white hover:bg-slate-800">
                                                Add ticket sale
                                            </button>

                                            <a href="{{ route('ticket_sales.index') }}"
                                                class="inline-flex items-center rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-100">
                                                Reset filters
                                            </a>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
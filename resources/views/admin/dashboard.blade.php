@extends('admin.layout.app')

@section('admin-content')
    @php
        $hour = now()->hour;
        $greeting = $hour < 12 ? 'Good Morning' : ($hour < 18 ? 'Good Afternoon' : 'Good Evening');
        $displayName = auth()->user()?->name ?? 'Admin';
        $range = $range ?? '7d';
        $scope = $scope ?? 'all';
    @endphp

    <div class="space-y-6">
        <!-- Header / Greeting -->
        <div
            class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-slate-900 via-slate-800 to-slate-900 p-6 text-white shadow-lg">
            <div class="absolute inset-0 opacity-10">
                <div class="absolute -right-10 -top-10 h-40 w-40 rounded-full bg-white blur-3xl"></div>
                <div class="absolute -bottom-10 left-10 h-40 w-40 rounded-full bg-blue-300 blur-3xl"></div>
            </div>

            <div class="relative flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <p class="text-sm font-medium uppercase tracking-wider text-slate-300">Dashboard Overview</p>
                    <h1 class="mt-2 text-2xl font-bold md:text-3xl">
                        {{ $greeting }}, {{ $displayName }} 👋
                    </h1>
                    <p class="mt-2 text-sm text-slate-300">
                        Here’s your financial snapshot for {{ now()->format('F j, Y') }}.
                    </p>
                </div>

                <form method="GET" action="{{ route('dashboard') }}"
                    class="grid w-full gap-3 sm:grid-cols-2 lg:w-auto lg:min-w-[360px]">
                    <div>
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-300">
                            Date Range
                        </label>
                        <select name="range" onchange="this.form.submit()"
                            class="w-full rounded-2xl border border-white/10 bg-white/10 px-4 py-3 text-sm font-medium text-white backdrop-blur-sm outline-none transition focus:border-blue-300 focus:ring-4 focus:ring-blue-400/30">
                            <option value="7d" {{ $range === '7d' ? 'selected' : '' }} class="text-slate-900">Last 7 days</option>
                            <option value="30d" {{ $range === '30d' ? 'selected' : '' }} class="text-slate-900">Last 30 days</option>
                            <option value="month" {{ $range === 'month' ? 'selected' : '' }} class="text-slate-900">This month</option>
                        </select>
                    </div>

                    <div>
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-300">
                            Scope
                        </label>
                        <select name="scope" onchange="this.form.submit()"
                            class="w-full rounded-2xl border border-white/10 bg-white/10 px-4 py-3 text-sm font-medium text-white backdrop-blur-sm outline-none transition focus:border-blue-300 focus:ring-4 focus:ring-blue-400/30">
                            <option value="my" {{ $scope === 'my' ? 'selected' : '' }} class="text-slate-900">My data</option>
                            <option value="team" {{ $scope === 'team' ? 'selected' : '' }} class="text-slate-900">Team</option>
                            <option value="all" {{ $scope === 'all' ? 'selected' : '' }} class="text-slate-900">All</option>
                        </select>
                    </div>
                </form>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
            <a href="{{ route('ticket_sales.index') }}"
                class="group block rounded-3xl border border-emerald-100 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md focus:outline-none focus:ring-4 focus:ring-emerald-200">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-sm font-semibold text-slate-500">Total Income</p>
                        <h3 class="mt-3 text-3xl font-bold tracking-tight text-slate-900">
                            {{ number_format($totalIncome ?? 0, 2) }}
                        </h3>
                    </div>
                    <div class="rounded-2xl bg-emerald-50 p-3 text-emerald-600">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M17 9l-5-5m0 0L7 9m5-5v16" />
                        </svg>
                    </div>
                </div>
                <p class="mt-3 text-xs text-slate-400">All recorded income based on current filters.</p>
                <p class="mt-2 text-xs font-semibold text-emerald-600">View income details</p>
            </a>

            <a href="{{ route('expenses.index') }}"
                class="group block rounded-3xl border border-rose-100 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md focus:outline-none focus:ring-4 focus:ring-rose-200">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-sm font-semibold text-slate-500">Total Expense</p>
                        <h3 class="mt-3 text-3xl font-bold tracking-tight text-slate-900">
                            {{ number_format($totalExpense ?? 0, 2) }}
                        </h3>
                    </div>
                    <div class="rounded-2xl bg-rose-50 p-3 text-rose-600">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M7 15l5 5m0 0l5-5m-5 5V4" />
                        </svg>
                    </div>
                </div>
                <p class="mt-3 text-xs text-slate-400">All tracked expenses based on current filters.</p>
                <p class="mt-2 text-xs font-semibold text-rose-600">View expense details</p>
            </a>

            <div class="rounded-3xl border border-blue-100 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md md:col-span-2 xl:col-span-1">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-sm font-semibold text-slate-500">Total Account Balance</p>
                        <h3 class="mt-3 text-3xl font-bold tracking-tight text-slate-900">
                            {{ number_format($totalAccountBalance ?? 0, 2) }}
                        </h3>
                    </div>
                    <div class="rounded-2xl bg-blue-50 p-3 text-blue-600">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M2.25 8.25h19.5m-18 0A1.5 1.5 0 012.25 6.75v-1.5A1.5 1.5 0 013.75 3.75h16.5a1.5 1.5 0 011.5 1.5v1.5a1.5 1.5 0 01-1.5 1.5m-18 0v8.25A1.5 1.5 0 003.75 18h16.5a1.5 1.5 0 001.5-1.5V8.25" />
                        </svg>
                    </div>
                </div>
                <p class="mt-3 text-xs text-slate-400">Combined balance across all available accounts.</p>
            </div>
        </div>

        <!-- Accounts Table -->
        <div class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
            <div class="flex flex-col gap-2 border-b border-slate-100 px-6 py-5 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-lg font-bold text-slate-900">Account Balances</h2>
                    <p class="text-sm text-slate-500">A quick look at all accounts and their current balances.</p>
                </div>

                <div class="rounded-full bg-slate-100 px-4 py-2 text-xs font-semibold text-slate-600">
                    {{ count($accounts ?? []) }} {{ count($accounts ?? []) === 1 ? 'Account' : 'Accounts' }}
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead class="bg-slate-50">
                        <tr class="text-left text-xs uppercase tracking-wide text-slate-500">
                            <th class="px-6 py-4 font-semibold">Account</th>
                            <th class="px-6 py-4 font-semibold">Type</th>
                            <th class="px-6 py-4 text-right font-semibold">Balance</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($accounts ?? [] as $account)
                            <tr class="transition hover:bg-slate-50/80">
                                <td class="px-6 py-4">
                                    <div class="font-semibold text-slate-800">{{ $account->name }}</div>
                                </td>
                                <td class="px-6 py-4">
                                    <span
                                        class="inline-flex rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600">
                                        {{ ucfirst($account->type ?? 'N/A') }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <span class="text-base font-bold text-slate-900">
                                        {{ number_format($account->current_balance ?? 0, 2) }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="px-6 py-14 text-center">
                                    <div class="mx-auto flex max-w-sm flex-col items-center">
                                        <div class="rounded-full bg-slate-100 p-4 text-slate-400">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none"
                                                viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.178-3.141A7.466 7.466 0 013 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                                            </svg>
                                        </div>
                                        <h3 class="mt-4 text-base font-semibold text-slate-800">No accounts found</h3>
                                        <p class="mt-1 text-sm text-slate-500">
                                            There are no account records available for the selected filters.
                                        </p>
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

@extends('admin.layout.app')

@section('admin-content')
    @php
        $totalVendors = $vendors->count();
        $withEmail = $vendors->filter(fn($item) => filled($item->email))->count();
        $withAddress = $vendors->filter(fn($item) => filled($item->address))->count();
        $recentVendors = $vendors->filter(fn($item) => optional($item->created_at)?->isCurrentMonth())->count();
    @endphp

    <div class="space-y-6">
        <!-- Hero Header -->
        <div class="relative overflow-hidden rounded-[28px] border border-slate-200 bg-gradient-to-br from-slate-950 via-slate-900 to-slate-800 p-6 shadow-sm sm:p-8">
            <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_right,rgba(255,255,255,0.14),transparent_32%)]"></div>
            <div class="absolute -right-16 -top-16 h-40 w-40 rounded-full bg-white/5 blur-3xl"></div>
            <div class="absolute -bottom-12 left-10 h-32 w-32 rounded-full bg-indigo-400/10 blur-3xl"></div>

            <div class="relative flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
                <div class="max-w-3xl">
                    <div class="inline-flex items-center rounded-full border border-white/10 bg-white/5 px-3 py-1 text-xs font-semibold uppercase tracking-[0.2em] text-slate-300">
                        Vendor Management
                    </div>

                    <h1 class="mt-4 text-2xl font-bold tracking-tight text-white sm:text-3xl">
                        Vendors Dashboard
                    </h1>

                    <p class="mt-3 max-w-2xl text-sm leading-6 text-slate-300">
                        Create and manage your vendor list, keep supplier contact details organized, and maintain a clean vendor database from one place.
                    </p>
                </div>

                <div class="flex flex-wrap items-center gap-3">
                    <div class="rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-center">
                        <div class="text-xs font-medium text-slate-300">Records</div>
                        <div class="mt-1 text-lg font-bold text-white">{{ number_format($totalVendors) }}</div>
                    </div>

                    <button command="show-modal" commandfor="vendor-dialog"
                        class="inline-flex items-center gap-2 rounded-2xl bg-white px-5 py-3 text-sm font-semibold text-slate-900 shadow-lg shadow-black/10 transition hover:bg-slate-100">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                        </svg>
                        Add vendor
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
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Total Vendors</p>
                        <h3 class="mt-3 text-2xl font-bold text-slate-900">{{ number_format($totalVendors) }}</h3>
                        <p class="mt-1 text-sm text-slate-500">All saved vendor records</p>
                    </div>
                    <div class="rounded-2xl bg-slate-100 p-3 text-slate-600">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M3.75 21h16.5M4.5 3.75h15A.75.75 0 0120.25 4.5v12.75a.75.75 0 01-.75.75H4.5a.75.75 0 01-.75-.75V4.5a.75.75 0 01.75-.75Z" />
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M8.25 8.25h7.5M8.25 12h7.5" />
                        </svg>
                    </div>
                </div>
            </div>

            <div class="group rounded-3xl border border-slate-200 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">With Email</p>
                        <h3 class="mt-3 text-2xl font-bold text-emerald-600">{{ number_format($withEmail) }}</h3>
                        <p class="mt-1 text-sm text-slate-500">Email details available</p>
                    </div>
                    <div class="rounded-2xl bg-emerald-50 p-3 text-emerald-600">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M21.75 6.75v10.5A2.25 2.25 0 0119.5 19.5h-15A2.25 2.25 0 012.25 17.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15A2.25 2.25 0 002.25 6.75m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0l-7.5-4.615A2.25 2.25 0 012.25 6.993V6.75" />
                        </svg>
                    </div>
                </div>
            </div>

            <div class="group rounded-3xl border border-slate-200 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">With Address</p>
                        <h3 class="mt-3 text-2xl font-bold text-blue-600">{{ number_format($withAddress) }}</h3>
                        <p class="mt-1 text-sm text-slate-500">Address details available</p>
                    </div>
                    <div class="rounded-2xl bg-blue-50 p-3 text-blue-600">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0Z" />
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0Z" />
                        </svg>
                    </div>
                </div>
            </div>

            <div class="group rounded-3xl border border-slate-200 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Added This Month</p>
                        <h3 class="mt-3 text-2xl font-bold text-indigo-600">{{ number_format($recentVendors) }}</h3>
                        <p class="mt-1 text-sm text-slate-500">Recently created vendors</p>
                    </div>
                    <div class="rounded-2xl bg-indigo-50 p-3 text-indigo-600">
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
            <dialog id="vendor-dialog" aria-labelledby="vendor-dialog-title"
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
                                    <h3 id="vendor-dialog-title" class="mt-3 text-xl font-bold text-slate-900">
                                        Add Vendor
                                    </h3>
                                    <p class="mt-1 text-sm text-slate-500">
                                        Add supplier identity and contact details to your vendor list.
                                    </p>
                                </div>

                                <button type="button" command="close" commandfor="vendor-dialog"
                                    class="inline-flex h-11 w-11 items-center justify-center rounded-2xl border border-slate-200 bg-white text-slate-500 shadow-sm transition hover:bg-slate-100 hover:text-slate-700">
                                    ✕
                                </button>
                            </div>
                        </div>

                        <form class="px-6 py-6 sm:px-8" method="POST" action="{{ route('vendors.store') }}">
                            @csrf

                            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                                <div>
                                    <label class="text-sm font-semibold text-slate-700">Name</label>
                                    <input type="text" name="name" value="{{ old('name') }}" required
                                        class="mt-2 w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700 outline-none ring-blue-200 transition focus:border-blue-300 focus:bg-white focus:ring-4">
                                </div>

                                <div>
                                    <label class="text-sm font-semibold text-slate-700">Email</label>
                                    <input type="email" name="email" value="{{ old('email') }}"
                                        class="mt-2 w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700 outline-none ring-blue-200 transition focus:border-blue-300 focus:bg-white focus:ring-4">
                                </div>

                                <div>
                                    <label class="text-sm font-semibold text-slate-700">Mobile</label>
                                    <input type="text" name="mobile" value="{{ old('mobile') }}" required
                                        class="mt-2 w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700 outline-none ring-blue-200 transition focus:border-blue-300 focus:bg-white focus:ring-4">
                                </div>

                                <div class="md:col-span-2">
                                    <label class="text-sm font-semibold text-slate-700">Address</label>
                                    <textarea name="address" rows="4"
                                        class="mt-2 w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700 outline-none ring-blue-200 transition focus:border-blue-300 focus:bg-white focus:ring-4">{{ old('address') }}</textarea>
                                </div>
                            </div>

                            <div class="mt-8 flex flex-col-reverse gap-3 border-t border-slate-200 pt-5 sm:flex-row sm:justify-end">
                                <button type="button" command="close" commandfor="vendor-dialog"
                                    class="inline-flex justify-center rounded-2xl border border-slate-200 bg-white px-5 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                                    Cancel
                                </button>

                                <button type="submit"
                                    class="inline-flex justify-center rounded-2xl bg-slate-900 px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-slate-800">
                                    Add vendor
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
                        <h2 class="text-lg font-bold text-slate-900">Vendor List</h2>
                        <p class="text-sm text-slate-500">View, edit, and manage vendor contact information.</p>
                    </div>

                    <div class="inline-flex items-center rounded-full bg-slate-100 px-3 py-1.5 text-xs font-semibold text-slate-600">
                        {{ number_format($totalVendors) }} records
                    </div>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-slate-50">
                        <tr class="text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                            <th class="px-5 py-4">Name</th>
                            <th class="px-5 py-4">Email</th>
                            <th class="px-5 py-4">Mobile</th>
                            <th class="px-5 py-4">Address</th>
                            <th class="px-5 py-4">Created</th>
                            <th class="px-5 py-4 text-right">Actions</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-100 bg-white">
                        @forelse ($vendors as $vendor)
                            <tr class="transition hover:bg-slate-50/80">
                                <td class="px-5 py-4">
                                    <div class="font-semibold text-slate-900">
                                        {{ $vendor->name }}
                                    </div>
                                </td>

                                <td class="px-5 py-4 text-slate-700">
                                    {{ $vendor->email ?: '—' }}
                                </td>

                                <td class="px-5 py-4 text-slate-700">
                                    {{ $vendor->mobile }}
                                </td>

                                <td class="max-w-[260px] px-5 py-4 text-slate-700">
                                    <p class="truncate" title="{{ $vendor->address }}">
                                        {{ $vendor->address ?: '—' }}
                                    </p>
                                </td>

                                <td class="px-5 py-4">
                                    <div class="text-slate-700">
                                        {{ $vendor->created_at?->format('M j, Y') ?? '—' }}
                                    </div>
                                    <div class="mt-1 text-xs text-slate-500">
                                        {{ $vendor->created_at?->format('g:i A') ?? '' }}
                                    </div>
                                </td>

                                <td class="px-5 py-4">
                                    <div class="flex flex-wrap items-center justify-end gap-2">
                                        <a href="{{ route('vendors.history', $vendor->id) }}"
                                            class="rounded-xl border border-blue-200 bg-blue-50 px-3 py-2 text-xs font-semibold text-blue-700 transition hover:bg-blue-100">
                                            History
                                        </a>

                                        <a href="{{ route('vendors.edit', $vendor->id) }}"
                                            class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-semibold text-slate-700 transition hover:bg-slate-100">
                                            Edit
                                        </a>

                                        <form method="POST" action="{{ route('vendors.destroy', $vendor->id) }}"
                                            onsubmit="return confirm('Delete this vendor?');">
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
                                <td colspan="6" class="px-6 py-20">
                                    <div class="flex flex-col items-center justify-center text-center">
                                        <div class="flex h-16 w-16 items-center justify-center rounded-full bg-slate-100 text-slate-400">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none"
                                                viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M3.75 21h16.5M4.5 3.75h15A.75.75 0 0120.25 4.5v12.75a.75.75 0 01-.75.75H4.5a.75.75 0 01-.75-.75V4.5a.75.75 0 01.75-.75Z" />
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M8.25 8.25h7.5M8.25 12h7.5" />
                                            </svg>
                                        </div>

                                        <h3 class="mt-4 text-base font-semibold text-slate-900">No vendors found</h3>
                                        <p class="mt-1 max-w-md text-sm text-slate-500">
                                            Start by creating your first vendor and keep all supplier details organized in one place.
                                        </p>

                                        <button command="show-modal" commandfor="vendor-dialog"
                                            class="mt-5 inline-flex items-center rounded-2xl bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white hover:bg-slate-800">
                                            Add vendor
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
@endsection
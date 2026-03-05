@extends('admin.layout.app')

@section('admin-content')
    <div class="space-y-6">
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <h1 class="text-xl font-bold text-slate-900">Customers</h1>
            <p class="mt-1 text-sm text-slate-500">Create and manage your customer list.</p>

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
                <div class="text-sm font-bold text-slate-900">Add customer</div>

                <form class="mt-5 space-y-4" method="POST" action="{{ route('customers.store') }}">
                    @csrf

                    <div>
                        <label class="text-sm font-semibold text-slate-700">Passport Number</label>
                        <input type="text" name="passport_number" value="{{ old('passport_number') }}"
                            class="mt-2 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm outline-none ring-blue-200 focus:border-blue-300 focus:ring-4"
                            >
                    </div>

                    <div>
                        <label class="text-sm font-semibold text-slate-700">Name</label>
                        <input type="text" name="name" value="{{ old('name') }}"
                            class="mt-2 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm outline-none ring-blue-200 focus:border-blue-300 focus:ring-4"
                            required>
                    </div>

                
                    <div>
                        <label class="text-sm font-semibold text-slate-700">Email</label>
                        <input type="email" name="email" value="{{ old('email') }}"
                            class="mt-2 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm outline-none ring-blue-200 focus:border-blue-300 focus:ring-4"
                            >
                    </div>

                    <div>
                        <label class="text-sm font-semibold text-slate-700">Phone</label>
                        <input type="text" name="phone" value="{{ old('phone') }}"
                            class="mt-2 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm outline-none ring-blue-200 focus:border-blue-300 focus:ring-4"
                            required>
                    </div>

                    <div>
                        <label class="text-sm font-semibold text-slate-700">Date of Birth</label>
                        <input type="date" name="date_of_birth" value="{{ old('date_of_birth') }}"
                            class="mt-2 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm outline-none ring-blue-200 focus:border-blue-300 focus:ring-4"
                            >
                    </div>

                    <div>
                        <label class="text-sm font-semibold text-slate-700">Address</label>
                        <textarea name="address" rows="3"
                            class="mt-2 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm outline-none ring-blue-200 focus:border-blue-300 focus:ring-4"
                            >{{ old('address') }}</textarea>
                    </div>

                    <button type="submit"
                        class="w-full rounded-xl bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white hover:bg-slate-800">
                        Add customer
                    </button>
                </form>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm lg:col-span-2">
                <div class="text-sm font-bold text-slate-900">Customer list</div>

                <div class="mt-5 overflow-hidden rounded-2xl border border-slate-200">
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead class="bg-white">
                                <tr class="text-left text-xs uppercase tracking-wide text-slate-400">
                                    <th class="px-4 py-3">sl.</th>
                                    <th class="px-4 py-3">Passport</th>
                                    <th class="px-4 py-3">Name</th>
                                    <th class="px-4 py-3">Email</th>
                                    <th class="px-4 py-3">Phone</th>
                                    <th class="px-4 py-3">DOB</th>
                                    <th class="px-4 py-3">Address</th>
                                    <th class="px-4 py-3 text-right">Actions</th>
                                </tr>
                            </thead>
                         <tbody class="divide-y divide-slate-200">
@forelse ($customers as $customer)
<tr class="bg-white hover:bg-slate-50">
    <td class="px-4 py-3 border border-slate-200 font-semibold text-slate-900">
        {{ $loop->iteration }}
    </td>

    <td class="px-4 py-3 border border-slate-200 font-semibold text-slate-900">
        {{ $customer->passport_number }}
    </td>

    <td class="px-4 py-3 border border-slate-200 text-slate-700">
        {{ $customer->name }}
    </td>

    <td class="px-4 py-3 border border-slate-200 text-slate-700">
        {{ $customer->email }}
    </td>

    <td class="px-4 py-3 border border-slate-200 text-slate-700">
        {{ $customer->phone }}
    </td>

    <td class="px-4 py-3 border border-slate-200 text-slate-700">
        {{ $customer->date_of_birth?->format('M j, Y') }}
    </td>

    <td class="px-4 py-3 border border-slate-200 text-slate-700">
        {{ $customer->address }}
    </td>

    <td class="px-4 py-3 border border-slate-200">
        <div class="flex items-center justify-end gap-2">
            <a href="{{ route('customers.edit', $customer->id) }}"
               class="rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50">
                Edit
            </a>

            <form method="POST" action="{{ route('customers.destroy', $customer->id) }}"
                  onsubmit="return confirm('Delete this customer?');">
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
    <td colspan="8" class="px-6 py-10 text-center text-sm text-slate-500">
        No customers found.
    </td>
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

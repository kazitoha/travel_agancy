@extends('admin.layout.app')

@section('admin-content')
    <div class="space-y-6">
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="flex items-center justify-between gap-3">
                <div>
                    <h1 class="text-xl font-bold text-slate-900">Edit customer</h1>
                    <p class="mt-1 text-sm text-slate-500">Update customer details.</p>
                </div>
                <a href="{{ route('customers.index') }}"
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
            <form class="space-y-4" method="POST" action="{{ route('customers.update', $customer->id) }}">
                @csrf
                @method('PUT')

                <div>
                    <label class="text-sm font-semibold text-slate-700">Passport Number</label>
                    <input type="text" name="passport_number" value="{{ old('passport_number', $customer->passport_number) }}"
                        class="mt-2 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm outline-none ring-blue-200 focus:border-blue-300 focus:ring-4"
                        >
                </div>

                <div>
                    <label class="text-sm font-semibold text-slate-700">Name</label>
                    <input type="text" name="name" value="{{ old('name', $customer->name) }}"
                        class="mt-2 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm outline-none ring-blue-200 focus:border-blue-300 focus:ring-4"
                        required>
                </div>
                <div>
                    <label class="text-sm font-semibold text-slate-700">Email</label>
                    <input type="email" name="email" value="{{ old('email', $customer->email) }}"
                        class="mt-2 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm outline-none ring-blue-200 focus:border-blue-300 focus:ring-4"
                        >
                </div>

                <div>
                    <label class="text-sm font-semibold text-slate-700">Phone</label>
                    <input type="text" name="phone" value="{{ old('phone', $customer->phone) }}"
                        class="mt-2 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm outline-none ring-blue-200 focus:border-blue-300 focus:ring-4"
                        required>
                </div>

                <div>
                    <label class="text-sm font-semibold text-slate-700">Date of Birth</label>
                    <input type="date" name="date_of_birth" value="{{ old('date_of_birth', $customer->date_of_birth?->format('Y-m-d')) }}"
                        class="mt-2 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm outline-none ring-blue-200 focus:border-blue-300 focus:ring-4"
                        >
                </div>

                <div>
                    <label class="text-sm font-semibold text-slate-700">Address</label>
                    <textarea name="address" rows="3"
                        class="mt-2 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm outline-none ring-blue-200 focus:border-blue-300 focus:ring-4"
                        >{{ old('address', $customer->address) }}</textarea>
                </div>

                <div class="flex items-center justify-end gap-2">
                    <a href="{{ route('customers.index') }}"
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

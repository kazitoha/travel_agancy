@extends('admin.layout.app')

@section('admin-content')
    <div class="space-y-6">
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="flex items-center justify-between gap-3">
                <div>
                    <h1 class="text-xl font-bold text-slate-900">Edit reference</h1>
                    <p class="mt-1 text-sm text-slate-500">Update reference details.</p>
                </div>
                <a href="{{ route('references.index') }}"
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
            <form class="space-y-4" method="POST" action="{{ route('references.update', $reference->id) }}">
                @csrf
                @method('PUT')

                <div>
                    <label class="text-sm font-semibold text-slate-700">Company name</label>
                    <input type="text" name="company_name" value="{{ old('company_name', $reference->company_name) }}"
                        class="mt-2 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm outline-none ring-blue-200 focus:border-blue-300 focus:ring-4"
                        required>
                </div>

                <div>
                    <label class="text-sm font-semibold text-slate-700">Contact person</label>
                    <input type="text" name="contact_person_name"
                        value="{{ old('contact_person_name', $reference->contact_person_name) }}"
                        class="mt-2 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm outline-none ring-blue-200 focus:border-blue-300 focus:ring-4">
                </div>

                <div>
                    <label class="text-sm font-semibold text-slate-700">Phone</label>
                    <input type="text" name="phone" value="{{ old('phone', $reference->phone) }}"
                        class="mt-2 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm outline-none ring-blue-200 focus:border-blue-300 focus:ring-4">
                </div>

                <div>
                    <label class="text-sm font-semibold text-slate-700">Address</label>
                    <textarea name="address" rows="3"
                        class="mt-2 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm outline-none ring-blue-200 focus:border-blue-300 focus:ring-4">{{ old('address', $reference->address) }}</textarea>
                </div>

                <div class="flex items-center justify-end gap-2">
                    <a href="{{ route('references.index') }}"
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

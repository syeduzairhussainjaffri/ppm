@extends('ppm.layout')

@section('title', 'Add Customer / Hospital')
@section('page_category', 'Customer Directory')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Add Customer / Hospital</h1>
            <p class="text-sm text-slate-500 mt-0.5">Register a new healthcare facility or department.</p>
        </div>
        <a href="{{ route('customers.index') }}" class="px-4 py-2 border border-slate-300 rounded-lg text-sm font-semibold text-slate-700 hover:bg-slate-50 transition">
            <i class="fa-solid fa-arrow-left mr-1.5"></i> Back
        </a>
    </div>

    @if ($errors->any())
        <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-sm">
            <ul class="list-disc list-inside">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('customers.store') }}" method="POST" class="bg-white border border-slate-200 rounded-2xl shadow-sm p-6 sm:p-8 space-y-5">
        @csrf

        <div>
            <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Customer / Hospital Name <span class="text-rose-500">*</span></label>
            <input type="text" name="name" required value="{{ old('name') }}" placeholder="e.g. Director Health Services Karachi, Sindh" 
                   class="w-full px-3.5 py-2.5 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-teal-500">
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Department</label>
                <input type="text" name="department" value="{{ old('department') }}" placeholder="e.g. Biomedical / Gynae / ICU" 
                       class="w-full px-3.5 py-2.5 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-teal-500">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Contact Person</label>
                <input type="text" name="contact_person" value="{{ old('contact_person') }}" placeholder="e.g. Engr. Daniyal" 
                       class="w-full px-3.5 py-2.5 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-teal-500">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Contact Number</label>
                <input type="text" name="contact_no" value="{{ old('contact_no') }}" placeholder="e.g. 0334-2834216" 
                       class="w-full px-3.5 py-2.5 border border-slate-300 rounded-lg text-sm font-mono focus:ring-2 focus:ring-teal-500">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Email Address</label>
                <input type="email" name="email" value="{{ old('email') }}" placeholder="e.g. info@dhsk.gov.pk" 
                       class="w-full px-3.5 py-2.5 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-teal-500">
            </div>
        </div>

        <div>
            <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Physical Address / City</label>
            <input type="text" name="address" value="{{ old('address') }}" placeholder="e.g. Karachi, Sindh" 
                   class="w-full px-3.5 py-2.5 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-teal-500">
        </div>

        <div>
            <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Notes</label>
            <textarea name="notes" rows="2" placeholder="Additional details..." class="w-full px-3.5 py-2.5 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-teal-500">{{ old('notes') }}</textarea>
        </div>

        <div class="pt-4 border-t border-slate-200 flex justify-end gap-3">
            <a href="{{ route('customers.index') }}" class="px-5 py-2.5 rounded-lg border border-slate-300 text-slate-700 text-sm font-semibold hover:bg-slate-50">Cancel</a>
            <button type="submit" class="px-6 py-2.5 bg-teal-600 hover:bg-teal-700 text-white rounded-lg text-sm font-semibold shadow-sm">Save Customer</button>
        </div>
    </form>
</div>
@endsection

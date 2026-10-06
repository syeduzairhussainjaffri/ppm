@extends('ppm.layout')

@section('title', 'Add Product / Equipment')
@section('page_category', 'Product Catalog')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Add Product / Equipment</h1>
            <p class="text-sm text-slate-500 mt-0.5">Register a new biomedical equipment specification.</p>
        </div>
        <a href="{{ route('products.index') }}" class="px-4 py-2 border border-slate-300 rounded-lg text-sm font-semibold text-slate-700 hover:bg-slate-50 transition">
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

    <form action="{{ route('products.store') }}" method="POST" class="bg-white border border-slate-200 rounded-2xl shadow-sm p-6 sm:p-8 space-y-5">
        @csrf

        <div>
            <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Product Description / Name <span class="text-rose-500">*</span></label>
            <input type="text" name="name" required value="{{ old('name') }}" placeholder="e.g. Oxygen Supply Line 400 Beds" 
                   class="w-full px-3.5 py-2.5 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500">
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Equipment Name</label>
                <input type="text" name="equipment_name" value="{{ old('equipment_name') }}" placeholder="e.g. Oxygen Supply Line System" 
                       class="w-full px-3.5 py-2.5 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Model / Make</label>
                <input type="text" name="model_make" value="{{ old('model_make') }}" placeholder="e.g. Noxerior, Italy" 
                       class="w-full px-3.5 py-2.5 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Category</label>
                <input type="text" name="category" value="{{ old('category') }}" placeholder="e.g. Medical Gas System, Life Support" 
                       class="w-full px-3.5 py-2.5 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Default PPM Interval (Days)</label>
                <input type="number" name="default_ppm_days" value="{{ old('default_ppm_days', 30) }}" min="1" 
                       class="w-full px-3.5 py-2.5 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500">
            </div>
        </div>

        <div>
            <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Technical Notes / Description</label>
            <textarea name="description" rows="3" placeholder="Specifications, warranty terms, maintenance notes..." class="w-full px-3.5 py-2.5 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500">{{ old('description') }}</textarea>
        </div>

        <div class="pt-4 border-t border-slate-200 flex justify-end gap-3">
            <a href="{{ route('products.index') }}" class="px-5 py-2.5 rounded-lg border border-slate-300 text-slate-700 text-sm font-semibold hover:bg-slate-50">Cancel</a>
            <button type="submit" class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm font-semibold shadow-sm">Save Product</button>
        </div>
    </form>
</div>
@endsection

@extends('ppm.layout')

@section('title', 'Edit Product - ' . $product->name)
@section('page_category', 'Product Catalog')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Edit Product / Equipment</h1>
            <p class="text-sm text-slate-500 mt-0.5">Editing: {{ $product->name }}</p>
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

    <form action="{{ route('products.update', $product) }}" method="POST" class="bg-white border border-slate-200 rounded-2xl shadow-sm p-6 sm:p-8 space-y-5">
        @csrf
        @method('PUT')

        <div>
            <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Product Description / Name <span class="text-rose-500">*</span></label>
            <input type="text" name="name" required value="{{ old('name', $product->name) }}" 
                   class="w-full px-3.5 py-2.5 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500">
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Equipment Name</label>
                <input type="text" name="equipment_name" value="{{ old('equipment_name', $product->equipment_name) }}" 
                       class="w-full px-3.5 py-2.5 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Model / Make</label>
                <input type="text" name="model_make" value="{{ old('model_make', $product->model_make) }}" 
                       class="w-full px-3.5 py-2.5 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Category</label>
                <input type="text" name="category" value="{{ old('category', $product->category) }}" 
                       class="w-full px-3.5 py-2.5 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Default PPM Interval (Days)</label>
                <input type="number" name="default_ppm_days" value="{{ old('default_ppm_days', $product->default_ppm_days) }}" min="1" 
                       class="w-full px-3.5 py-2.5 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500">
            </div>
        </div>

        <div>
            <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Technical Notes / Description</label>
            <textarea name="description" rows="3" class="w-full px-3.5 py-2.5 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500">{{ old('description', $product->description) }}</textarea>
        </div>

        <div class="pt-4 border-t border-slate-200 flex justify-end gap-3">
            <a href="{{ route('products.index') }}" class="px-5 py-2.5 rounded-lg border border-slate-300 text-slate-700 text-sm font-semibold hover:bg-slate-50">Cancel</a>
            <button type="submit" class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm font-semibold shadow-sm">Update Product</button>
        </div>
    </form>
</div>
@endsection

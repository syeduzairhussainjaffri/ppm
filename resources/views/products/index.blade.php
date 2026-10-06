@extends('ppm.layout')

@section('title', 'Registered Products & Equipment')
@section('page_category', 'Product Catalog')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Products &amp; Equipment Catalog</h1>
            <p class="text-sm text-slate-500 mt-1">Manage biomedical equipment models, default maintenance cycles, and specifications.</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('products.create') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-lg shadow-sm transition">
                <i class="fa-solid fa-plus mr-2"></i> Add Product / Equipment
            </a>
        </div>
    </div>

    <!-- Search Bar -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm">
        <form action="{{ route('products.index') }}" method="GET" class="flex gap-3">
            <div class="relative flex-grow">
                <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-slate-400">
                    <i class="fa-solid fa-magnifying-glass"></i>
                </span>
                <input type="text" name="search" value="{{ $search }}" placeholder="Search product name, equipment, model, or category..." 
                       class="w-full pl-10 pr-4 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500">
            </div>
            <button type="submit" class="px-5 py-2 bg-slate-900 text-white rounded-lg text-sm font-medium hover:bg-slate-800 transition">
                Search
            </button>
            @if($search)
                <a href="{{ route('products.index') }}" class="px-3 py-2 bg-slate-100 text-slate-600 rounded-lg text-sm font-medium hover:bg-slate-200 transition">
                    Reset
                </a>
            @endif
        </form>
    </div>

    <!-- Products Table -->
    <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm text-left">
                <thead class="bg-slate-50 text-slate-600 font-semibold uppercase text-xs tracking-wider">
                    <tr>
                        <th class="py-3.5 px-4">Product Description / Name</th>
                        <th class="py-3.5 px-4">Equipment Name</th>
                        <th class="py-3.5 px-4">Model / Make</th>
                        <th class="py-3.5 px-4">Category</th>
                        <th class="py-3.5 px-4 text-center">Default PPM Interval</th>
                        <th class="py-3.5 px-4 text-center">Service Records</th>
                        <th class="py-3.5 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 text-slate-700">
                    @forelse($products as $p)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="py-4 px-4 font-semibold text-slate-950">
                                <div class="text-base text-slate-950">{{ $p->name }}</div>
                                @if($p->description)
                                    <div class="text-xs text-slate-400 mt-0.5 line-clamp-1">{{ $p->description }}</div>
                                @endif
                            </td>
                            <td class="py-4 px-4 text-slate-800 font-medium">
                                {{ $p->equipment_name ?: '-' }}
                            </td>
                            <td class="py-4 px-4 text-slate-800">
                                <span class="bg-slate-100 text-slate-800 px-2 py-0.5 rounded font-mono text-xs border border-slate-200">
                                    {{ $p->model_make ?: '-' }}
                                </span>
                            </td>
                            <td class="py-4 px-4">
                                @if($p->category)
                                    <span class="bg-blue-50 text-blue-700 px-2.5 py-1 rounded-md text-xs font-semibold border border-blue-200">
                                        {{ $p->category }}
                                    </span>
                                @else
                                    <span class="text-slate-400">-</span>
                                @endif
                            </td>
                            <td class="py-4 px-4 text-center">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-50 text-amber-800 border border-amber-200">
                                    <i class="fa-regular fa-clock mr-1"></i> {{ $p->default_ppm_days ?: 30 }} Days
                                </span>
                            </td>
                            <td class="py-4 px-4 text-center">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-teal-50 text-teal-700 border border-teal-200">
                                    {{ $p->ppm_records_count }} Records
                                </span>
                            </td>
                            <td class="py-4 px-4 whitespace-nowrap text-right space-x-1.5">
                                <a href="{{ route('products.edit', $p) }}" title="Edit Product" class="inline-flex items-center px-2.5 py-1.5 rounded-md bg-slate-100 text-slate-700 hover:bg-slate-200 text-xs font-medium transition">
                                    <i class="fa-solid fa-pen-to-square mr-1"></i> Edit
                                </a>
                                <form action="{{ route('products.destroy', $p) }}" method="POST" class="inline-block" onsubmit="return confirm('Delete this product?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" title="Delete Product" class="px-2.5 py-1.5 rounded-md bg-rose-50 text-rose-600 hover:bg-rose-100 text-xs font-medium transition">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-12 text-slate-400">
                                <p class="text-base font-semibold text-slate-600">No products registered yet</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($products->hasPages())
            <div class="p-4 border-t border-slate-200">
                {{ $products->links() }}
            </div>
        @endif
    </div>
</div>
@endsection

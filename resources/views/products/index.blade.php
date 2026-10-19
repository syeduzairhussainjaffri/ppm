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
        <div class="flex items-center gap-2 sm:gap-3 flex-wrap">
            @can('view_reports')
            <a href="{{ route('reports.product') }}" class="inline-flex items-center px-3.5 py-2 bg-slate-800 hover:bg-slate-900 text-white text-sm font-semibold rounded-lg shadow-sm transition">
                <i class="fa-solid fa-boxes-stacked mr-2 text-blue-400"></i> Product Reports
            </a>
            @endcan
            @can('export_products')
            <a href="{{ route('products.export') }}" class="inline-flex items-center px-3.5 py-2 bg-white border border-slate-300 rounded-lg text-sm font-semibold text-slate-700 hover:bg-slate-50 transition shadow-xs">
                <i class="fa-solid fa-file-arrow-down mr-2 text-emerald-600"></i> Export CSV
            </a>
            @endcan
            @can('import_products')
            <button type="button" onclick="document.getElementById('productImportModal').classList.remove('hidden')" class="inline-flex items-center px-3.5 py-2 bg-white border border-slate-300 rounded-lg text-sm font-semibold text-slate-700 hover:bg-slate-50 transition shadow-xs">
                <i class="fa-solid fa-file-arrow-up mr-2 text-blue-600"></i> Import CSV
            </button>
            @endcan
            @can('create_products')
            <a href="{{ route('products.create') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-lg shadow-sm transition">
                <i class="fa-solid fa-plus mr-2"></i> Add Product
            </a>
            @endcan
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
                                @can('edit_products')
                                <a href="{{ route('products.edit', $p) }}" title="Edit Product" class="inline-flex items-center px-2.5 py-1.5 rounded-md bg-slate-100 text-slate-700 hover:bg-slate-200 text-xs font-medium transition">
                                    <i class="fa-solid fa-pen-to-square mr-1"></i> Edit
                                </a>
                                @endcan
                                @can('delete_products')
                                <form action="{{ route('products.destroy', $p) }}" method="POST" class="inline-block" onsubmit="return confirm('Delete this product?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" title="Delete Product" class="px-2.5 py-1.5 rounded-md bg-rose-50 text-rose-600 hover:bg-rose-100 text-xs font-medium transition">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </form>
                                @endcan
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

    <!-- Product Import Modal -->
    @can('import_products')
    <div id="productImportModal" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-xs hidden">
        <div class="bg-white rounded-2xl shadow-2xl border border-slate-200 w-full max-w-lg p-6 mx-4">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center text-sm font-bold">
                        <i class="fa-solid fa-file-arrow-up"></i>
                    </div>
                    <h3 class="text-base font-bold text-slate-800">Import Products from CSV</h3>
                </div>
                <button type="button" onclick="document.getElementById('productImportModal').classList.add('hidden')" class="text-slate-400 hover:text-slate-600">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <form action="{{ route('products.import') }}" method="POST" enctype="multipart/form-data" class="mt-4 space-y-4">
                @csrf
                <div class="p-3 bg-blue-50 border border-blue-200 rounded-xl text-xs text-blue-900">
                    <p class="font-bold flex items-center gap-1.5 mb-1"><i class="fa-solid fa-circle-info"></i> CSV Format Note</p>
                    <p>Expected columns: <code>Product Name, Equipment Name, Model/Make, Category, Default PPM Days, Description</code></p>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Select CSV File</label>
                    <input type="file" name="file" accept=".csv,text/csv" required class="block w-full text-xs text-slate-600 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 cursor-pointer border border-slate-200 rounded-xl p-2 bg-slate-50">
                </div>

                <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100">
                    <button type="button" onclick="document.getElementById('productImportModal').classList.add('hidden')" class="px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-lg transition">
                        Cancel
                    </button>
                    <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-xs font-bold shadow-xs transition inline-flex items-center gap-1.5">
                        <i class="fa-solid fa-upload"></i> Upload &amp; Import
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endcan
</div>
@endsection

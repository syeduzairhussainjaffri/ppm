@extends('ppm.layout')

@section('title', 'Registered Customers & Hospitals')
@section('page_category', 'Customer Directory')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Customers &amp; Hospitals Directory</h1>
            <p class="text-sm text-slate-500 mt-1">Manage health facilities, hospital departments, and authorized contact persons.</p>
        </div>
        <div class="flex items-center gap-2 sm:gap-3 flex-wrap">
            @can('view_reports')
            <a href="{{ route('reports.customer') }}" class="inline-flex items-center px-3.5 py-2 bg-slate-800 hover:bg-slate-900 text-white text-sm font-semibold rounded-lg shadow-sm transition">
                <i class="fa-solid fa-chart-pie mr-2 text-teal-400"></i> Customer Reports
            </a>
            @endcan
            @can('export_customers')
            <a href="{{ route('customers.export') }}" class="inline-flex items-center px-3.5 py-2 bg-white border border-slate-300 rounded-lg text-sm font-semibold text-slate-700 hover:bg-slate-50 transition shadow-xs">
                <i class="fa-solid fa-file-arrow-down mr-2 text-emerald-600"></i> Export CSV
            </a>
            @endcan
            @can('import_customers')
            <button type="button" onclick="document.getElementById('customerImportModal').classList.remove('hidden')" class="inline-flex items-center px-3.5 py-2 bg-white border border-slate-300 rounded-lg text-sm font-semibold text-slate-700 hover:bg-slate-50 transition shadow-xs">
                <i class="fa-solid fa-file-arrow-up mr-2 text-blue-600"></i> Import CSV
            </button>
            @endcan
            @can('create_customers')
            <a href="{{ route('customers.create') }}" class="inline-flex items-center px-4 py-2 bg-teal-600 hover:bg-teal-700 text-white text-sm font-semibold rounded-lg shadow-sm transition">
                <i class="fa-solid fa-plus mr-2"></i> Add Customer
            </a>
            @endcan
        </div>
    </div>

    <!-- Search Bar -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm">
        <form action="{{ route('customers.index') }}" method="GET" class="flex gap-3">
            <div class="relative flex-grow">
                <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-slate-400">
                    <i class="fa-solid fa-magnifying-glass"></i>
                </span>
                <input type="text" name="search" value="{{ $search }}" placeholder="Search customer name, department, contact person, or phone..." 
                       class="w-full pl-10 pr-4 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-teal-500">
            </div>
            <button type="submit" class="px-5 py-2 bg-slate-900 text-white rounded-lg text-sm font-medium hover:bg-slate-800 transition">
                Search
            </button>
            @if($search)
                <a href="{{ route('customers.index') }}" class="px-3 py-2 bg-slate-100 text-slate-600 rounded-lg text-sm font-medium hover:bg-slate-200 transition">
                    Reset
                </a>
            @endif
        </form>
    </div>

    <!-- Customer Table -->
    <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm text-left">
                <thead class="bg-slate-50 text-slate-600 font-semibold uppercase text-xs tracking-wider">
                    <tr>
                        <th class="py-3.5 px-4">Hospital / Customer Name</th>
                        <th class="py-3.5 px-4">Department</th>
                        <th class="py-3.5 px-4">Contact Person</th>
                        <th class="py-3.5 px-4">Contact Number</th>
                        <th class="py-3.5 px-4 text-center">Service Records</th>
                        <th class="py-3.5 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 text-slate-700">
                    @forelse($customers as $c)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="py-4 px-4 font-semibold text-slate-900">
                                <div class="text-base text-slate-950">{{ $c->name }}</div>
                                @if($c->address)
                                    <div class="text-xs text-slate-400 mt-0.5"><i class="fa-solid fa-location-dot mr-1"></i>{{ $c->address }}</div>
                                @endif
                            </td>
                            <td class="py-4 px-4">
                                <span class="bg-slate-100 text-slate-700 px-2.5 py-1 rounded-md text-xs font-medium border border-slate-200">
                                    {{ $c->department ?: 'N/A' }}
                                </span>
                            </td>
                            <td class="py-4 px-4 font-medium text-slate-800">
                                {{ $c->contact_person ?: '-' }}
                            </td>
                            <td class="py-4 px-4 font-mono text-xs">
                                {{ $c->contact_no ?: '-' }}
                            </td>
                            <td class="py-4 px-4 text-center">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-teal-50 text-teal-700 border border-teal-200">
                                    {{ $c->ppm_records_count }} Records
                                </span>
                            </td>
                            <td class="py-4 px-4 whitespace-nowrap text-right space-x-1.5">
                                @can('edit_customers')
                                <a href="{{ route('customers.edit', $c) }}" title="Edit Customer" class="inline-flex items-center px-2.5 py-1.5 rounded-md bg-slate-100 text-slate-700 hover:bg-slate-200 text-xs font-medium transition">
                                    <i class="fa-solid fa-pen-to-square mr-1"></i> Edit
                                </a>
                                @endcan
                                @can('delete_customers')
                                <form action="{{ route('customers.destroy', $c) }}" method="POST" class="inline-block" onsubmit="return confirm('Delete this customer?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" title="Delete Customer" class="px-2.5 py-1.5 rounded-md bg-rose-50 text-rose-600 hover:bg-rose-100 text-xs font-medium transition">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </form>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-12 text-slate-400">
                                <p class="text-base font-semibold text-slate-600">No customers registered yet</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($customers->hasPages())
            <div class="p-4 border-t border-slate-200">
                {{ $customers->links() }}
            </div>
        @endif
    </div>

    <!-- Customer Import Modal -->
    @can('import_customers')
    <div id="customerImportModal" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-xs hidden">
        <div class="bg-white rounded-2xl shadow-2xl border border-slate-200 w-full max-w-lg p-6 mx-4">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-lg bg-teal-50 text-teal-600 flex items-center justify-center text-sm font-bold">
                        <i class="fa-solid fa-file-arrow-up"></i>
                    </div>
                    <h3 class="text-base font-bold text-slate-800">Import Customers from CSV</h3>
                </div>
                <button type="button" onclick="document.getElementById('customerImportModal').classList.add('hidden')" class="text-slate-400 hover:text-slate-600">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <form action="{{ route('customers.import') }}" method="POST" enctype="multipart/form-data" class="mt-4 space-y-4">
                @csrf
                <div class="p-3 bg-blue-50 border border-blue-200 rounded-xl text-xs text-blue-900">
                    <p class="font-bold flex items-center gap-1.5 mb-1"><i class="fa-solid fa-circle-info"></i> CSV Format Note</p>
                    <p>Expected columns: <code>Customer Name, Department, Contact Person, Contact No, Address</code></p>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Select CSV File</label>
                    <input type="file" name="file" accept=".csv,text/csv" required class="block w-full text-xs text-slate-600 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-teal-50 file:text-teal-700 hover:file:bg-teal-100 cursor-pointer border border-slate-200 rounded-xl p-2 bg-slate-50">
                </div>

                <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100">
                    <button type="button" onclick="document.getElementById('customerImportModal').classList.add('hidden')" class="px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-lg transition">
                        Cancel
                    </button>
                    <button type="submit" class="px-4 py-2 bg-teal-600 hover:bg-teal-700 text-white rounded-lg text-xs font-bold shadow-xs transition inline-flex items-center gap-1.5">
                        <i class="fa-solid fa-upload"></i> Upload &amp; Import
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endcan
</div>
@endsection

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
        <div class="flex items-center gap-3">
            <a href="{{ route('reports.customer') }}" class="inline-flex items-center px-3.5 py-2 bg-slate-800 hover:bg-slate-900 text-white text-sm font-semibold rounded-lg shadow-sm transition">
                <i class="fa-solid fa-chart-pie mr-2 text-teal-400"></i> Customer Reports
            </a>
            <a href="{{ route('customers.create') }}" class="inline-flex items-center px-4 py-2 bg-teal-600 hover:bg-teal-700 text-white text-sm font-semibold rounded-lg shadow-sm transition">
                <i class="fa-solid fa-plus mr-2"></i> Add Customer
            </a>
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
                                <a href="{{ route('customers.edit', $c) }}" title="Edit Customer" class="inline-flex items-center px-2.5 py-1.5 rounded-md bg-slate-100 text-slate-700 hover:bg-slate-200 text-xs font-medium transition">
                                    <i class="fa-solid fa-pen-to-square mr-1"></i> Edit
                                </a>
                                <form action="{{ route('customers.destroy', $c) }}" method="POST" class="inline-block" onsubmit="return confirm('Delete this customer?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" title="Delete Customer" class="px-2.5 py-1.5 rounded-md bg-rose-50 text-rose-600 hover:bg-rose-100 text-xs font-medium transition">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </form>
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
</div>
@endsection

@extends('ppm.layout')

@section('title', 'Product & Equipment Deployment Report')
@section('page_category', 'Product Reports')

@section('content')
<div class="space-y-6">

    <!-- Header Banner -->
    <div class="bg-gradient-to-r from-slate-900 via-slate-800 to-blue-950 rounded-2xl p-5 text-white shadow-xl flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center space-x-2">
                <span class="bg-blue-500/20 text-blue-300 text-[10px] font-extrabold uppercase px-2.5 py-1 rounded-full border border-blue-500/40 tracking-wider">
                    <i class="fa-solid fa-boxes-stacked mr-1"></i> Equipment Deployment &amp; Inventory Audit
                </span>
                <span class="bg-slate-800 text-slate-300 text-[10px] font-bold px-2 py-0.5 rounded-full border border-slate-700">
                    Product-Wise
                </span>
            </div>
            <h1 class="text-2xl font-black text-white mt-1.5 tracking-tight">Product-Wise Equipment Report</h1>
            <p class="text-xs text-slate-300 mt-1 max-w-2xl leading-relaxed">
                Installed base deployment per medical device model, customer location distribution, serial tracking, and PPM cycle compliance.
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('reports.index') }}" class="px-3 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-bold transition">
                <i class="fa-solid fa-arrow-left mr-1"></i> Reports Hub
            </a>
            <a href="{{ route('reports.export', request()->query()) }}" class="px-3.5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold transition shadow-sm">
                <i class="fa-solid fa-file-csv mr-1.5"></i> Export CSV
            </a>
            <a href="{{ route('reports.print', ['report_title' => 'Product Equipment Deployment Report'] + request()->query()) }}" target="_blank" class="px-4 py-2 rounded-xl bg-blue-600 hover:bg-blue-500 text-white text-xs font-bold transition shadow-md">
                <i class="fa-solid fa-print mr-1.5"></i> Print Equipment Sheet
            </a>
        </div>
    </div>

    <!-- Comprehensive Filter Panel -->
    <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-200">
        <div class="flex items-center justify-between pb-3 mb-4 border-b border-slate-100">
            <div class="flex items-center space-x-2">
                <div class="w-7 h-7 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center text-xs font-bold">
                    <i class="fa-solid fa-filter"></i>
                </div>
                <h3 class="font-bold text-slate-800 text-sm">Advanced Search &amp; Multi-Filters</h3>
            </div>
            @if(request()->anyFilled(['search', 'product_id', 'department', 'service_type', 'status', 'date_from', 'date_to']))
                <a href="{{ route('reports.product') }}" class="text-xs text-rose-600 hover:text-rose-800 font-bold inline-flex items-center">
                    <i class="fa-solid fa-rotate-left mr-1"></i> Reset All Filters
                </a>
            @endif
        </div>

        <form action="{{ route('reports.product') }}" method="GET" class="space-y-4 text-xs">
            <!-- Row 1: Search, Product, Department, Service Type -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                <!-- Search Query -->
                <div>
                    <label class="block font-bold text-slate-700 mb-1">
                        <i class="fa-solid fa-magnifying-glass text-slate-400 mr-1"></i> Search Query
                    </label>
                    <input type="text" 
                           name="search" 
                           value="{{ request('search') }}" 
                           placeholder="Equipment model, make, serial, hospital..." 
                           class="w-full py-2 px-3 rounded-xl border border-slate-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-xs">
                </div>

                <!-- Product Dropdown -->
                <div>
                    <label class="block font-bold text-slate-700 mb-1">
                        <i class="fa-solid fa-boxes-stacked text-slate-400 mr-1"></i> Select Product / Model
                    </label>
                    <select name="product_id" class="w-full py-2 px-3 rounded-xl border border-slate-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-xs font-medium">
                        <option value="">-- All Products ({{ $products->count() }}) --</option>
                        @foreach($products as $p)
                            <option value="{{ $p->id }}" {{ request('product_id') == $p->id ? 'selected' : '' }}>
                                {{ $p->name }} {{ $p->model_make ? "({$p->model_make})" : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Department Dropdown -->
                <div>
                    <label class="block font-bold text-slate-700 mb-1">
                        <i class="fa-solid fa-building text-slate-400 mr-1"></i> Department
                    </label>
                    <select name="department" class="w-full py-2 px-3 rounded-xl border border-slate-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-xs">
                        <option value="">-- All Departments --</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept }}" {{ request('department') == $dept ? 'selected' : '' }}>
                                {{ $dept }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Service Type Dropdown -->
                <div>
                    <label class="block font-bold text-slate-700 mb-1">
                        <i class="fa-solid fa-wrench text-slate-400 mr-1"></i> Service Type
                    </label>
                    <select name="service_type" class="w-full py-2 px-3 rounded-xl border border-slate-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-xs">
                        <option value="">-- All Service Types --</option>
                        @foreach($serviceTypes as $st)
                            <option value="{{ $st }}" {{ request('service_type') == $st ? 'selected' : '' }}>
                                {{ $st }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Row 2: Status, Date Field, Date From, Date To -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 pt-1 border-t border-slate-100">
                <!-- Status Filter -->
                <div>
                    <label class="block font-bold text-slate-700 mb-1">
                        <i class="fa-solid fa-chart-pie text-slate-400 mr-1"></i> Maintenance Status
                    </label>
                    <select name="status" class="w-full py-2 px-3 rounded-xl border border-slate-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-xs font-semibold">
                        <option value="">-- All Statuses --</option>
                        <option value="due_today" {{ request('status') === 'due_today' ? 'selected' : '' }}>🔔 Due Today</option>
                        <option value="overdue" {{ request('status') === 'overdue' ? 'selected' : '' }}>⚠️ Overdue PPM</option>
                        <option value="upcoming" {{ request('status') === 'upcoming' ? 'selected' : '' }}>📅 Upcoming Schedule</option>
                        <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>✅ Completed</option>
                        <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>⏳ Pending / In-Progress</option>
                    </select>
                </div>

                <!-- Date Field Selector -->
                <div>
                    <label class="block font-bold text-slate-700 mb-1">
                        <i class="fa-solid fa-calendar text-slate-400 mr-1"></i> Date Filter Target
                    </label>
                    <select name="date_field" class="w-full py-2 px-3 rounded-xl border border-slate-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-xs font-medium">
                        <option value="next_ppm_date" {{ request('date_field', 'next_ppm_date') === 'next_ppm_date' ? 'selected' : '' }}>Next PPM Date</option>
                        <option value="installation_date" {{ request('date_field') === 'installation_date' ? 'selected' : '' }}>Installation Date</option>
                        <option value="entry_date" {{ request('date_field') === 'entry_date' ? 'selected' : '' }}>Entry Date</option>
                    </select>
                </div>

                <!-- Date Range From -->
                <div>
                    <label class="block font-bold text-slate-700 mb-1">
                        <i class="fa-solid fa-calendar-day text-slate-400 mr-1"></i> Date Range: From
                    </label>
                    <input type="date" 
                           name="date_from" 
                           value="{{ request('date_from') }}" 
                           class="w-full py-2 px-3 rounded-xl border border-slate-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-xs">
                </div>

                <!-- Date Range To -->
                <div>
                    <label class="block font-bold text-slate-700 mb-1">
                        <i class="fa-solid fa-calendar-check text-slate-400 mr-1"></i> Date Range: To
                    </label>
                    <input type="date" 
                           name="date_to" 
                           value="{{ request('date_to') }}" 
                           class="w-full py-2 px-3 rounded-xl border border-slate-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-xs">
                </div>
            </div>

            <!-- Submit Button Row -->
            <div class="flex items-center justify-between pt-2">
                <div class="text-slate-500 text-[11px]">
                    Showing deployment reports for <span class="font-bold text-slate-800">{{ $totals['total_products'] }}</span> product line(s) and <span class="font-bold text-slate-800">{{ $totals['total_installed'] }}</span> installed unit(s) across <span class="font-bold text-slate-800">{{ $totals['unique_hospitals'] }}</span> hospital(s).
                </div>
                <div class="flex items-center space-x-2">
                    @if(request()->anyFilled(['search', 'product_id', 'department', 'service_type', 'status', 'date_from', 'date_to']))
                        <a href="{{ route('reports.product') }}" class="py-2 px-3.5 rounded-xl border border-slate-300 text-slate-600 hover:bg-slate-50 font-bold transition">
                            Clear
                        </a>
                    @endif
                    <button type="submit" class="py-2 px-5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold shadow-xs transition inline-flex items-center">
                        <i class="fa-solid fa-filter mr-1.5"></i> Apply Filters
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Overall KPI Summary Bar / Totals -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
        <!-- Products in Report -->
        <div class="bg-white p-3.5 rounded-2xl border border-slate-200 shadow-xs">
            <div class="flex items-center justify-between text-slate-500 text-[11px] font-bold uppercase">
                <span>Products</span>
                <i class="fa-solid fa-boxes-stacked text-slate-400"></i>
            </div>
            <p class="text-xl font-black text-slate-900 mt-1">{{ $totals['total_products'] }}</p>
            <p class="text-[10px] text-slate-400 mt-0.5">Product Models</p>
        </div>

        <!-- Total Installed Units -->
        <div class="bg-white p-3.5 rounded-2xl border border-slate-200 shadow-xs">
            <div class="flex items-center justify-between text-blue-600 text-[11px] font-bold uppercase">
                <span>Installed Units</span>
                <i class="fa-solid fa-server"></i>
            </div>
            <p class="text-xl font-black text-slate-900 mt-1">{{ $totals['total_installed'] }}</p>
            <p class="text-[10px] text-slate-400 mt-0.5">Deployed Units</p>
        </div>

        <!-- Hospitals Deployed -->
        <div class="bg-white p-3.5 rounded-2xl border border-slate-200 shadow-xs">
            <div class="flex items-center justify-between text-indigo-600 text-[11px] font-bold uppercase">
                <span>Hospitals</span>
                <i class="fa-solid fa-hospital"></i>
            </div>
            <p class="text-xl font-black text-slate-900 mt-1">{{ $totals['unique_hospitals'] }}</p>
            <p class="text-[10px] text-slate-400 mt-0.5">Unique Locations</p>
        </div>

        <!-- Due Today -->
        <div class="bg-white p-3.5 rounded-2xl border border-slate-200 shadow-xs">
            <div class="flex items-center justify-between text-amber-600 text-[11px] font-bold uppercase">
                <span>Due Today</span>
                <i class="fa-solid fa-bell {{ $totals['due_today'] > 0 ? 'animate-bounce text-amber-500' : '' }}"></i>
            </div>
            <p class="text-xl font-black {{ $totals['due_today'] > 0 ? 'text-amber-600' : 'text-slate-900' }} mt-1">
                {{ $totals['due_today'] }}
            </p>
            <p class="text-[10px] text-slate-400 mt-0.5">{{ now()->format('d-M-Y') }}</p>
        </div>

        <!-- Overdue -->
        <div class="bg-white p-3.5 rounded-2xl border border-slate-200 shadow-xs">
            <div class="flex items-center justify-between text-rose-600 text-[11px] font-bold uppercase">
                <span>Overdue</span>
                <i class="fa-solid fa-triangle-exclamation"></i>
            </div>
            <p class="text-xl font-black {{ $totals['overdue'] > 0 ? 'text-rose-600' : 'text-slate-900' }} mt-1">
                {{ $totals['overdue'] }}
            </p>
            <p class="text-[10px] text-slate-400 mt-0.5">Requires Action</p>
        </div>

        <!-- Completed Services -->
        <div class="bg-white p-3.5 rounded-2xl border border-slate-200 shadow-xs">
            <div class="flex items-center justify-between text-emerald-600 text-[11px] font-bold uppercase">
                <span>Completed</span>
                <i class="fa-solid fa-circle-check"></i>
            </div>
            <p class="text-xl font-black text-slate-900 mt-1">{{ $totals['completed'] }}</p>
            <p class="text-[10px] text-slate-400 mt-0.5">Compliant Units</p>
        </div>
    </div>

    <!-- Product Detailed Groups -->
    <div class="space-y-6">
        @forelse($productReports as $report)
            @php
                $records = $report['records'];
            @endphp
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                <!-- Product Card Header -->
                <div class="bg-slate-900 text-white p-4 border-b border-slate-800 flex flex-col md:flex-row md:items-center justify-between gap-3">
                    <div class="flex items-start sm:items-center space-x-3">
                        <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-blue-600 to-indigo-500 text-white font-black text-lg flex items-center justify-center shadow shrink-0">
                            <i class="fa-solid fa-microscope"></i>
                        </div>
                        <div>
                            <div class="flex items-center space-x-2">
                                <h2 class="text-base font-bold tracking-tight text-white">{{ $report['product_name'] }}</h2>
                                @if($report['category'])
                                    <span class="bg-slate-800 text-blue-300 border border-slate-700 text-[10px] font-bold px-2 py-0.5 rounded-md">
                                        {{ $report['category'] }}
                                    </span>
                                @endif
                            </div>
                            <div class="text-xs text-slate-400 flex flex-wrap items-center gap-x-3 gap-y-1 mt-1">
                                <span>
                                    <i class="fa-solid fa-tag text-blue-400 mr-1"></i> Equipment Type: 
                                    <strong class="text-slate-300">{{ $report['equipment_name'] ?: $report['product_name'] }}</strong>
                                </span>
                                <span>&bull;</span>
                                <span>
                                    <i class="fa-solid fa-industry text-blue-400 mr-1"></i> Model/Make: 
                                    <strong class="text-slate-300">{{ $report['model_make'] ?: 'Standard' }}</strong>
                                </span>
                                @if($report['default_ppm_days'])
                                    <span>&bull;</span>
                                    <span>
                                        <i class="fa-solid fa-clock-rotate-left text-blue-400 mr-1"></i> PPM Cycle: 
                                        <strong class="text-slate-300">{{ $report['default_ppm_days'] }} Days</strong>
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Product Subtotal Badges -->
                    <div class="flex flex-wrap items-center gap-1.5 text-xs">
                        <span class="px-2.5 py-1 rounded-lg bg-blue-900 text-blue-200 border border-blue-700 font-bold">
                            Total: {{ $report['total_installed'] }} Units
                        </span>
                        <span class="px-2.5 py-1 rounded-lg bg-slate-800 text-slate-300 border border-slate-700 font-medium">
                            <i class="fa-solid fa-hospital mr-1 text-slate-400"></i> {{ $report['unique_hospitals'] }} Hospitals
                        </span>
                        @if($report['due_today'] > 0)
                            <span class="px-2.5 py-1 rounded-lg bg-amber-500 text-slate-950 font-extrabold animate-pulse">
                                {{ $report['due_today'] }} Due Today
                            </span>
                        @endif
                        @if($report['overdue'] > 0)
                            <span class="px-2.5 py-1 rounded-lg bg-rose-600 text-white font-extrabold">
                                {{ $report['overdue'] }} Overdue
                            </span>
                        @endif
                        @if($report['completed'] > 0)
                            <span class="px-2 py-1 rounded-lg bg-emerald-900/60 text-emerald-300 border border-emerald-700/50 font-bold">
                                {{ $report['completed'] }} Completed
                            </span>
                        @endif
                    </div>
                </div>

                <!-- Hospital Deployments Table for this Product -->
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs text-slate-700">
                        <thead class="bg-slate-50 uppercase text-[10px] font-bold text-slate-600 border-b border-slate-200">
                            <tr>
                                <th class="p-3 w-10 text-center">#</th>
                                <th class="p-3">Hospital / Customer Name</th>
                                <th class="p-3">Department</th>
                                <th class="p-3">Serial No</th>
                                <th class="p-3">Contact Person &amp; Phone</th>
                                <th class="p-3">Service Type</th>
                                <th class="p-3">PO Number</th>
                                <th class="p-3">Installation Date</th>
                                <th class="p-3">Next PPM Date</th>
                                <th class="p-3">Status</th>
                                <th class="p-3 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($records as $index => $r)
                                <tr class="hover:bg-slate-50/80 transition">
                                    <td class="p-3 text-center text-slate-400 font-bold">{{ $index + 1 }}</td>
                                    <td class="p-3">
                                        <div class="font-bold text-slate-900 flex items-center space-x-1.5">
                                            <i class="fa-solid fa-hospital text-blue-500 text-xs"></i>
                                            <span>{{ $r->customer_name }}</span>
                                        </div>
                                    </td>
                                    <td class="p-3 text-slate-600">{{ $r->department ?: 'General' }}</td>
                                    <td class="p-3">
                                        @if($r->serial_no)
                                            <span class="font-mono bg-slate-100 px-2 py-0.5 rounded text-slate-800 font-bold border border-slate-200">
                                                {{ $r->serial_no }}
                                            </span>
                                        @else
                                            <span class="text-slate-400 italic">None</span>
                                        @endif
                                    </td>
                                    <td class="p-3 text-slate-600">
                                        {{ $r->contact_person ?: 'N/A' }}
                                        @if($r->contact_no)
                                            <div class="text-[10px] text-slate-400">{{ $r->contact_no }}</div>
                                        @endif
                                    </td>
                                    <td class="p-3">
                                        <span class="inline-block px-2 py-0.5 rounded text-[10px] font-bold {{ $r->service_type === 'New Installation' ? 'bg-purple-100 text-purple-800' : 'bg-blue-100 text-blue-800' }}">
                                            {{ $r->service_type ?: 'Routine' }}
                                        </span>
                                    </td>
                                    <td class="p-3 text-slate-600 font-medium">{{ $r->purchase_order_no ?: 'N/A' }}</td>
                                    <td class="p-3 text-slate-600">{{ optional($r->installation_date)->format('d-M-Y') ?: 'N/A' }}</td>
                                    <td class="p-3">
                                        @if($r->next_ppm_date)
                                            @if($r->next_ppm_date->isToday())
                                                <span class="inline-flex items-center px-2 py-0.5 bg-amber-100 text-amber-900 font-black rounded border border-amber-300">
                                                    <i class="fa-solid fa-bell text-[10px] mr-1 animate-pulse"></i> DUE TODAY
                                                </span>
                                            @elseif($r->next_ppm_date->isPast() && strtolower($r->status) !== 'completed')
                                                <span class="inline-flex items-center px-2 py-0.5 bg-rose-100 text-rose-900 font-black rounded border border-rose-300">
                                                    <i class="fa-solid fa-triangle-exclamation text-[10px] mr-1"></i> {{ $r->next_ppm_date->format('d-M-Y') }}
                                                </span>
                                            @else
                                                <span class="font-bold text-slate-800">
                                                    {{ $r->next_ppm_date->format('d-M-Y') }}
                                                </span>
                                            @endif
                                        @else
                                            <span class="text-slate-400">N/A</span>
                                        @endif
                                    </td>
                                    <td class="p-3">
                                        @if(strtolower($r->status) === 'completed')
                                            <span class="px-2 py-0.5 bg-emerald-100 text-emerald-800 font-bold rounded-full text-[10px] inline-flex items-center">
                                                <i class="fa-solid fa-check text-[9px] mr-1"></i> Completed
                                            </span>
                                        @else
                                            <span class="px-2 py-0.5 bg-slate-100 text-slate-700 font-bold rounded-full text-[10px] inline-flex items-center">
                                                <i class="fa-solid fa-clock text-[9px] mr-1 text-slate-400"></i> {{ $r->status ?: 'Pending' }}
                                            </span>
                                        @endif
                                    </td>
                                    <td class="p-3 text-right">
                                        <a href="{{ route('ppm.show', $r) }}" class="inline-flex items-center px-2.5 py-1 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-[10px] font-bold shadow-xs transition">
                                            Voucher &rarr;
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="11" class="p-6 text-center text-slate-400 italic">
                                        <i class="fa-solid fa-circle-info mr-1 text-slate-300"></i>
                                        No deployment or installation records found for this product matching the selected filters.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                        <!-- Product Subtotal Row -->
                        @if($records->isNotEmpty())
                            <tfoot class="bg-slate-100 font-bold text-slate-700 border-t border-slate-200">
                                <tr>
                                    <td colspan="3" class="p-3 text-slate-900">
                                        <i class="fa-solid fa-calculator mr-1 text-blue-600"></i>
                                        Model Total: {{ $records->count() }} Unit(s) across {{ $report['unique_hospitals'] }} Hospital(s)
                                    </td>
                                    <td colspan="4" class="p-3 text-slate-600 font-normal">
                                        Completed: <strong class="text-emerald-700">{{ $report['completed'] }}</strong> &bull; 
                                        Pending / Open: <strong class="text-amber-700">{{ $report['pending'] }}</strong>
                                    </td>
                                    <td colspan="4" class="p-3 text-right">
                                        @if($report['due_today'] > 0)
                                            <span class="text-amber-800 bg-amber-200 px-2 py-0.5 rounded text-[10px] mr-1">
                                                {{ $report['due_today'] }} Due Today
                                            </span>
                                        @endif
                                        @if($report['overdue'] > 0)
                                            <span class="text-rose-800 bg-rose-200 px-2 py-0.5 rounded text-[10px]">
                                                {{ $report['overdue'] }} Overdue
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                            </tfoot>
                        @endif
                    </table>
                </div>
            </div>
        @empty
            <div class="bg-white rounded-2xl p-12 text-center text-slate-400 border border-slate-200">
                <i class="fa-solid fa-boxes-stacked text-4xl text-slate-300 mb-2"></i>
                <p class="font-bold text-slate-700 text-sm">No Product Deployment Records Match Criteria</p>
                <p class="text-xs text-slate-400 mt-1">Try adjusting or resetting your search keywords, status, or date range filters.</p>
                <div class="mt-4">
                    <a href="{{ route('reports.product') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-xl text-xs font-bold hover:bg-blue-700 transition">
                        Reset All Filters
                    </a>
                </div>
            </div>
        @endforelse
    </div>

    <!-- Grand Totals Summary Footer Card -->
    @if($productReports->isNotEmpty() && $totals['total_installed'] > 0)
        <div class="bg-slate-900 rounded-2xl p-5 text-white shadow-lg border border-slate-800 flex flex-col md:flex-row items-center justify-between gap-4">
            <div>
                <span class="text-[10px] font-black uppercase text-blue-400 tracking-wider">
                    <i class="fa-solid fa-list-check mr-1"></i> Deployment Report Grand Summary
                </span>
                <h4 class="text-lg font-bold text-white mt-0.5">
                    Total {{ $totals['total_products'] }} Product Model(s) &bull; {{ $totals['total_installed'] }} Installed Unit(s) across {{ $totals['unique_hospitals'] }} Hospital(s)
                </h4>
                <p class="text-xs text-slate-400 mt-1">
                    Completed: <strong class="text-emerald-400">{{ $totals['completed'] }}</strong> | 
                    Pending: <strong class="text-amber-400">{{ $totals['pending'] }}</strong> | 
                    Overdue: <strong class="text-rose-400">{{ $totals['overdue'] }}</strong> | 
                    Due Today: <strong class="text-amber-300">{{ $totals['due_today'] }}</strong>
                </p>
            </div>

            <div class="flex items-center space-x-2.5">
                <a href="{{ route('reports.export', request()->query()) }}" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-white text-xs font-bold rounded-xl transition border border-slate-700">
                    <i class="fa-solid fa-download mr-1.5 text-blue-400"></i> Export CSV
                </a>
                <a href="{{ route('reports.print', ['report_title' => 'Product Equipment Deployment Sheet'] + request()->query()) }}" target="_blank" class="px-4 py-2 bg-blue-600 hover:bg-blue-500 text-white text-xs font-bold rounded-xl transition shadow">
                    <i class="fa-solid fa-print mr-1.5"></i> Print Full Sheet
                </a>
            </div>
        </div>
    @endif

</div>
@endsection

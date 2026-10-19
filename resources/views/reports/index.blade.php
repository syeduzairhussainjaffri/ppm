@extends('ppm.layout')

@section('title', 'Comprehensive Reports & Analytics - BioMed CRM')
@section('page_category', 'Reports & Analytics Center')

@section('content')
<div class="space-y-6">

    <!-- Top Header Banner -->
    <div class="bg-gradient-to-r from-slate-900 via-slate-800 to-teal-950 rounded-2xl p-5 text-white shadow-xl border border-slate-700/60 relative overflow-hidden">
        <div class="absolute -right-10 -bottom-10 opacity-10 text-white text-9xl font-black pointer-events-none">
            <i class="fa-solid fa-chart-pie"></i>
        </div>
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 relative z-10">
            <div>
                <div class="flex items-center space-x-2">
                    <span class="bg-teal-500/20 text-teal-300 text-[10px] font-extrabold uppercase px-2.5 py-1 rounded-full border border-teal-500/40 tracking-wider">
                        <i class="fa-solid fa-chart-line mr-1"></i> Executive Reporting Suite
                    </span>
                    <span class="text-slate-400 text-xs font-mono">{{ now()->format('d-M-Y') }}</span>
                </div>
                <h1 class="text-2xl font-black text-white mt-1.5 tracking-tight">Hospital Equipment &amp; Maintenance Reports</h1>
                <p class="text-xs text-slate-300 mt-1 max-w-2xl leading-relaxed">
                    Filterable step-by-step reporting hub for customer equipment assets, product deployment metrics, PPM maintenance schedules, department allocations, and overdue maintenance tracking.
                </p>
            </div>
            
            <div class="flex items-center space-x-2.5 shrink-0">
                @can('print_reports')
                <a href="{{ route('reports.print', request()->query()) }}" target="_blank" class="inline-flex items-center px-3.5 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-600 text-xs font-bold transition shadow-sm">
                    <i class="fa-solid fa-print text-teal-400 mr-2"></i> Print Official Report
                </a>
                @endcan
                @can('export_reports')
                <a href="{{ route('reports.export', request()->query()) }}" class="inline-flex items-center px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold transition shadow-md hover:shadow-emerald-600/30">
                    <i class="fa-solid fa-file-excel mr-2 text-sm"></i> Download CSV Sheet
                </a>
                @endcan
            </div>
        </div>
    </div>

    <!-- Multi-Dimensional Report Filter Bar -->
    <div class="bg-white rounded-2xl p-4 shadow-sm border border-slate-200">
        <div class="flex items-center justify-between mb-3">
            <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wider flex items-center">
                <i class="fa-solid fa-filter text-teal-600 mr-2"></i> Interactive Report Filters
            </h3>
            @if(request()->anyFilled(['search', 'customer_id', 'product_id', 'department', 'service_type', 'status', 'date_from', 'date_to']))
                <a href="{{ route('reports.index') }}" class="text-xs text-rose-600 hover:text-rose-800 font-semibold flex items-center">
                    <i class="fa-solid fa-rotate-left mr-1"></i> Reset Filters
                </a>
            @endif
        </div>

        <form action="{{ route('reports.index') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-3 text-xs">
            <!-- Global Search -->
            <div class="lg:col-span-2">
                <label class="block font-medium text-slate-700 mb-1">Search Keywords</label>
                <div class="relative">
                    <i class="fa-solid fa-search absolute left-3 top-2.5 text-slate-400"></i>
                    <input type="text" name="search" value="{{ request('search') }}" 
                           placeholder="Customer, equipment, PO #, serial #..." 
                           class="w-full pl-9 pr-3 py-1.5 rounded-lg border border-slate-300 focus:ring-2 focus:ring-teal-500 focus:border-teal-500 text-xs">
                </div>
            </div>

            <!-- Customer Dropdown -->
            <div>
                <label class="block font-medium text-slate-700 mb-1">Customer / Hospital</label>
                <select name="customer_id" class="w-full py-1.5 px-2.5 rounded-lg border border-slate-300 focus:ring-2 focus:ring-teal-500 text-xs">
                    <option value="">-- All Customers --</option>
                    @foreach($customers as $c)
                        <option value="{{ $c->id }}" {{ request('customer_id') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Product Dropdown -->
            <div>
                <label class="block font-medium text-slate-700 mb-1">Product / Equipment</label>
                <select name="product_id" class="w-full py-1.5 px-2.5 rounded-lg border border-slate-300 focus:ring-2 focus:ring-teal-500 text-xs">
                    <option value="">-- All Products --</option>
                    @foreach($products as $p)
                        <option value="{{ $p->id }}" {{ request('product_id') == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Department Dropdown -->
            <div>
                <label class="block font-medium text-slate-700 mb-1">Department</label>
                <select name="department" class="w-full py-1.5 px-2.5 rounded-lg border border-slate-300 focus:ring-2 focus:ring-teal-500 text-xs">
                    <option value="">-- All Departments --</option>
                    @foreach($departments as $d)
                        <option value="{{ $d }}" {{ request('department') == $d ? 'selected' : '' }}>{{ $d }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Status Dropdown -->
            <div>
                <label class="block font-medium text-slate-700 mb-1">PPM Status</label>
                <select name="status" class="w-full py-1.5 px-2.5 rounded-lg border border-slate-300 focus:ring-2 focus:ring-teal-500 text-xs">
                    <option value="">-- All Statuses --</option>
                    <option value="due_today" {{ request('status') == 'due_today' ? 'selected' : '' }}>🔔 Due Today</option>
                    <option value="overdue" {{ request('status') == 'overdue' ? 'selected' : '' }}>⚠️ Overdue</option>
                    <option value="upcoming" {{ request('status') == 'upcoming' ? 'selected' : '' }}>📅 Upcoming</option>
                    <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>✅ Completed</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>⏳ Pending</option>
                </select>
            </div>

            <!-- Service Type Filter -->
            <div>
                <label class="block font-medium text-slate-700 mb-1">Service Type</label>
                <select name="service_type" class="w-full py-1.5 px-2.5 rounded-lg border border-slate-300 focus:ring-2 focus:ring-teal-500 text-xs">
                    <option value="">-- All Service Types --</option>
                    @foreach($serviceTypes as $st)
                        <option value="{{ $st }}" {{ request('service_type') == $st ? 'selected' : '' }}>{{ $st }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Date Type Filter -->
            <div>
                <label class="block font-medium text-slate-700 mb-1">Filter Date By</label>
                <select name="date_field" class="w-full py-1.5 px-2.5 rounded-lg border border-slate-300 focus:ring-2 focus:ring-teal-500 text-xs">
                    <option value="next_ppm_date" {{ request('date_field', 'next_ppm_date') == 'next_ppm_date' ? 'selected' : '' }}>Next PPM Date</option>
                    <option value="installation_date" {{ request('date_field') == 'installation_date' ? 'selected' : '' }}>Installation Date</option>
                    <option value="entry_date" {{ request('date_field') == 'entry_date' ? 'selected' : '' }}>Record Entry Date</option>
                </select>
            </div>

            <!-- Date From -->
            <div>
                <label class="block font-medium text-slate-700 mb-1">Date From</label>
                <input type="date" name="date_from" value="{{ request('date_from') }}" class="w-full py-1.5 px-2.5 rounded-lg border border-slate-300 focus:ring-2 focus:ring-teal-500 text-xs">
            </div>

            <!-- Date To -->
            <div>
                <label class="block font-medium text-slate-700 mb-1">Date To</label>
                <input type="date" name="date_to" value="{{ request('date_to') }}" class="w-full py-1.5 px-2.5 rounded-lg border border-slate-300 focus:ring-2 focus:ring-teal-500 text-xs">
            </div>

            <!-- Filter Buttons -->
            <div class="lg:col-span-2 flex items-end space-x-2">
                <button type="submit" class="flex-1 py-2 px-4 rounded-lg bg-teal-600 hover:bg-teal-700 text-white font-bold text-xs shadow-xs transition flex items-center justify-center">
                    <i class="fa-solid fa-magnifying-glass mr-1.5"></i> Apply Filters
                </button>
                <a href="{{ route('reports.index') }}" class="py-2 px-3 rounded-lg border border-slate-300 text-slate-600 hover:bg-slate-50 font-semibold text-xs transition">
                    Clear
                </a>
            </div>
        </form>
    </div>

    <!-- Metrics Summary Grid -->
    <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-7 gap-3">
        <div class="bg-white rounded-xl p-3.5 border border-slate-200 shadow-xs">
            <div class="flex items-center justify-between text-slate-400 text-xs mb-1">
                <span class="font-semibold text-slate-600">Total Filtered</span>
                <i class="fa-solid fa-folder-open text-teal-600"></i>
            </div>
            <p class="text-xl font-black text-slate-900">{{ number_format($summary['total_records']) }}</p>
            <p class="text-[10px] text-slate-500 mt-0.5">Matching Records</p>
        </div>

        <div class="bg-white rounded-xl p-3.5 border border-slate-200 shadow-xs">
            <div class="flex items-center justify-between text-slate-400 text-xs mb-1">
                <span class="font-semibold text-slate-600">Installations</span>
                <i class="fa-solid fa-screwdriver-wrench text-blue-600"></i>
            </div>
            <p class="text-xl font-black text-blue-600">{{ number_format($summary['new_installations']) }}</p>
            <p class="text-[10px] text-slate-500 mt-0.5">New Machines</p>
        </div>

        <div class="bg-amber-50/70 rounded-xl p-3.5 border border-amber-200 shadow-xs">
            <div class="flex items-center justify-between text-amber-700 text-xs mb-1">
                <span class="font-bold">PPM Due Today</span>
                <i class="fa-solid fa-bell text-amber-600 animate-bounce"></i>
            </div>
            <p class="text-xl font-black text-amber-900">{{ number_format($summary['due_today']) }}</p>
            <p class="text-[10px] text-amber-700 font-medium mt-0.5">Action Needed Today</p>
        </div>

        <div class="bg-rose-50/70 rounded-xl p-3.5 border border-rose-200 shadow-xs">
            <div class="flex items-center justify-between text-rose-700 text-xs mb-1">
                <span class="font-bold">Overdue PPM</span>
                <i class="fa-solid fa-triangle-exclamation text-rose-600"></i>
            </div>
            <p class="text-xl font-black text-rose-900">{{ number_format($summary['overdue']) }}</p>
            <p class="text-[10px] text-rose-700 font-medium mt-0.5">Delayed Maintenance</p>
        </div>

        <div class="bg-white rounded-xl p-3.5 border border-slate-200 shadow-xs">
            <div class="flex items-center justify-between text-slate-400 text-xs mb-1">
                <span class="font-semibold text-slate-600">Upcoming 7 Days</span>
                <i class="fa-solid fa-calendar-week text-teal-600"></i>
            </div>
            <p class="text-xl font-black text-teal-700">{{ number_format($summary['upcoming_7_days']) }}</p>
            <p class="text-[10px] text-slate-500 mt-0.5">Scheduled Soon</p>
        </div>

        <div class="bg-emerald-50/70 rounded-xl p-3.5 border border-emerald-200 shadow-xs">
            <div class="flex items-center justify-between text-emerald-700 text-xs mb-1">
                <span class="font-bold">Completed</span>
                <i class="fa-solid fa-circle-check text-emerald-600"></i>
            </div>
            <p class="text-xl font-black text-emerald-800">{{ number_format($summary['completed']) }}</p>
            <p class="text-[10px] text-emerald-700 font-medium mt-0.5">Service Verified</p>
        </div>

        <div class="bg-white rounded-xl p-3.5 border border-slate-200 shadow-xs col-span-2 sm:col-span-1">
            <div class="flex items-center justify-between text-slate-400 text-xs mb-1">
                <span class="font-semibold text-slate-600">Pending</span>
                <i class="fa-solid fa-clock text-slate-400"></i>
            </div>
            <p class="text-xl font-black text-slate-700">{{ number_format($summary['pending']) }}</p>
            <p class="text-[10px] text-slate-500 mt-0.5">Active Schedules</p>
        </div>
    </div>

    <!-- Chart Visualizations Row -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
        <!-- Customer Distribution Chart -->
        <div class="bg-white rounded-2xl p-4 border border-slate-200 shadow-sm">
            <h4 class="text-xs font-bold text-slate-800 uppercase tracking-wider mb-3 flex items-center">
                <i class="fa-solid fa-hospital text-teal-600 mr-2"></i> Top Customers Distribution
            </h4>
            <div class="h-52 relative">
                <canvas id="customerChart"></canvas>
            </div>
        </div>

        <!-- PPM Status Distribution Chart -->
        <div class="bg-white rounded-2xl p-4 border border-slate-200 shadow-sm">
            <h4 class="text-xs font-bold text-slate-800 uppercase tracking-wider mb-3 flex items-center">
                <i class="fa-solid fa-pie-chart text-amber-500 mr-2"></i> Maintenance Status Ratio
            </h4>
            <div class="h-52 relative">
                <canvas id="statusChart"></canvas>
            </div>
        </div>

        <!-- Department Allocation Chart -->
        <div class="bg-white rounded-2xl p-4 border border-slate-200 shadow-sm">
            <h4 class="text-xs font-bold text-slate-800 uppercase tracking-wider mb-3 flex items-center">
                <i class="fa-solid fa-sitemap text-blue-600 mr-2"></i> Department Assets
            </h4>
            <div class="h-52 relative">
                <canvas id="deptChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Interactive Section Tabs & Data Tables -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        
        <!-- Navigation Tab Headers -->
        <div class="border-b border-slate-200 bg-slate-50 p-2 flex flex-wrap gap-2 text-xs font-bold">
            <button onclick="switchReportTab('customer')" id="btn_tab_customer" class="py-2 px-4 rounded-xl text-teal-800 bg-white shadow-xs border border-slate-200 transition flex items-center">
                <i class="fa-solid fa-hospital-user mr-2 text-teal-600"></i> Customer-Wise Report ({{ $customerSummary->count() }})
            </button>
            <button onclick="switchReportTab('product')" id="btn_tab_product" class="py-2 px-4 rounded-xl text-slate-600 hover:text-slate-900 transition flex items-center">
                <i class="fa-solid fa-boxes-stacked mr-2 text-blue-600"></i> Product-Wise Report ({{ $productSummary->count() }})
            </button>
            <button onclick="switchReportTab('department')" id="btn_tab_department" class="py-2 px-4 rounded-xl text-slate-600 hover:text-slate-900 transition flex items-center">
                <i class="fa-solid fa-building mr-2 text-purple-600"></i> Department Report ({{ $departmentSummary->count() }})
            </button>
            <button onclick="switchReportTab('schedule')" id="btn_tab_schedule" class="py-2 px-4 rounded-xl text-slate-600 hover:text-slate-900 transition flex items-center">
                <i class="fa-solid fa-clock-rotate-left mr-2 text-amber-600"></i> PPM Schedule &amp; Overdue
            </button>
            <button onclick="switchReportTab('all_records')" id="btn_tab_all_records" class="py-2 px-4 rounded-xl text-slate-600 hover:text-slate-900 transition flex items-center">
                <i class="fa-solid fa-list-check mr-2 text-slate-600"></i> Detailed Records ({{ $records->count() }})
            </button>
        </div>

        <div class="p-4">
            
            <!-- TAB 1: Customer-Wise Summary -->
            <div id="content_tab_customer" class="block">
                <div class="flex items-center justify-between mb-3">
                    <div>
                        <h3 class="text-sm font-bold text-slate-900">Customer-Wise Breakdown Report</h3>
                        <p class="text-xs text-slate-500">Summary of installed equipment assets and PPM maintenance schedules per hospital/customer.</p>
                    </div>
                    <a href="{{ route('reports.customer') }}" class="text-xs text-teal-600 hover:text-teal-800 font-bold flex items-center">
                        Open Customer Detailed View &rarr;
                    </a>
                </div>

                <div class="overflow-x-auto border border-slate-200 rounded-xl">
                    <table class="w-full text-left text-xs text-slate-700">
                        <thead class="bg-slate-100 uppercase text-[11px] font-bold text-slate-600 border-b border-slate-200">
                            <tr>
                                <th class="p-3">Customer / Hospital Name</th>
                                <th class="p-3 text-center">Total Equipment</th>
                                <th class="p-3 text-center">New Installs</th>
                                <th class="p-3 text-center text-amber-700 bg-amber-50">Due Today</th>
                                <th class="p-3 text-center text-rose-700 bg-rose-50">Overdue</th>
                                <th class="p-3 text-center">Upcoming</th>
                                <th class="p-3 text-center text-emerald-700">Completed</th>
                                <th class="p-3 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200">
                            @forelse($customerSummary as $cust)
                                <tr class="hover:bg-slate-50/80 transition">
                                    <td class="p-3 font-bold text-slate-900 flex items-center space-x-2">
                                        <div class="w-7 h-7 rounded-lg bg-teal-100 text-teal-800 flex items-center justify-center font-black text-xs shrink-0">
                                            <i class="fa-solid fa-hospital"></i>
                                        </div>
                                        <span>{{ $cust['customer_name'] }}</span>
                                    </td>
                                    <td class="p-3 text-center font-extrabold text-slate-800">
                                        <span class="px-2.5 py-1 bg-slate-100 rounded-lg text-slate-800">{{ $cust['total_equipment'] }}</span>
                                    </td>
                                    <td class="p-3 text-center text-blue-600 font-semibold">{{ $cust['new_installations'] }}</td>
                                    <td class="p-3 text-center bg-amber-50 font-bold text-amber-800">
                                        @if($cust['due_today'] > 0)
                                            <span class="px-2 py-0.5 bg-amber-200 text-amber-900 rounded-full font-black text-[11px]">{{ $cust['due_today'] }}</span>
                                        @else
                                            <span class="text-slate-400">0</span>
                                        @endif
                                    </td>
                                    <td class="p-3 text-center bg-rose-50 font-bold text-rose-800">
                                        @if($cust['overdue'] > 0)
                                            <span class="px-2 py-0.5 bg-rose-200 text-rose-900 rounded-full font-black text-[11px]">{{ $cust['overdue'] }}</span>
                                        @else
                                            <span class="text-slate-400">0</span>
                                        @endif
                                    </td>
                                    <td class="p-3 text-center text-teal-700 font-semibold">{{ $cust['upcoming'] }}</td>
                                    <td class="p-3 text-center text-emerald-700 font-semibold">{{ $cust['completed'] }}</td>
                                    <td class="p-3 text-right">
                                        <a href="{{ route('reports.customer', ['search' => $cust['customer_name']]) }}" class="inline-flex items-center px-2.5 py-1 rounded-lg bg-teal-50 text-teal-700 hover:bg-teal-100 font-bold text-[11px] transition">
                                            View Report &rarr;
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="p-6 text-center text-slate-400">No customer records found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- TAB 2: Product-Wise Summary -->
            <div id="content_tab_product" class="hidden">
                <div class="flex items-center justify-between mb-3">
                    <div>
                        <h3 class="text-sm font-bold text-slate-900">Product / Equipment Deployment Report</h3>
                        <p class="text-xs text-slate-500">Breakdown of product models, installed base counts, and maintenance frequency across hospitals.</p>
                    </div>
                    <a href="{{ route('reports.product') }}" class="text-xs text-blue-600 hover:text-blue-800 font-bold flex items-center">
                        Open Product Detailed View &rarr;
                    </a>
                </div>

                <div class="overflow-x-auto border border-slate-200 rounded-xl">
                    <table class="w-full text-left text-xs text-slate-700">
                        <thead class="bg-slate-100 uppercase text-[11px] font-bold text-slate-600 border-b border-slate-200">
                            <tr>
                                <th class="p-3">Equipment / Product Description</th>
                                <th class="p-3">Model / Make Specs</th>
                                <th class="p-3 text-center">Installed Base</th>
                                <th class="p-3 text-center">Hospitals Served</th>
                                <th class="p-3 text-center text-amber-700 bg-amber-50">Due Today</th>
                                <th class="p-3 text-center text-rose-700 bg-rose-50">Overdue</th>
                                <th class="p-3 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200">
                            @forelse($productSummary as $prod)
                                <tr class="hover:bg-slate-50/80 transition">
                                    <td class="p-3 font-bold text-slate-900 flex items-center space-x-2">
                                        <div class="w-7 h-7 rounded-lg bg-blue-100 text-blue-800 flex items-center justify-center font-black text-xs shrink-0">
                                            <i class="fa-solid fa-stethoscope"></i>
                                        </div>
                                        <span>{{ $prod['product_name'] }}</span>
                                    </td>
                                    <td class="p-3 text-slate-600 italic">{{ $prod['models'] ?: 'Standard' }}</td>
                                    <td class="p-3 text-center font-extrabold">
                                        <span class="px-2.5 py-1 bg-blue-50 text-blue-900 border border-blue-200 rounded-lg">{{ $prod['total_units'] }} Units</span>
                                    </td>
                                    <td class="p-3 text-center font-semibold text-slate-700">{{ $prod['customers_count'] }} Hospitals</td>
                                    <td class="p-3 text-center bg-amber-50 font-bold text-amber-800">
                                        @if($prod['due_today'] > 0)
                                            <span class="px-2 py-0.5 bg-amber-200 text-amber-900 rounded-full font-black text-[11px]">{{ $prod['due_today'] }}</span>
                                        @else
                                            <span class="text-slate-400">0</span>
                                        @endif
                                    </td>
                                    <td class="p-3 text-center bg-rose-50 font-bold text-rose-800">
                                        @if($prod['overdue'] > 0)
                                            <span class="px-2 py-0.5 bg-rose-200 text-rose-900 rounded-full font-black text-[11px]">{{ $prod['overdue'] }}</span>
                                        @else
                                            <span class="text-slate-400">0</span>
                                        @endif
                                    </td>
                                    <td class="p-3 text-right">
                                        <a href="{{ route('reports.product', ['search' => $prod['product_name']]) }}" class="inline-flex items-center px-2.5 py-1 rounded-lg bg-blue-50 text-blue-700 hover:bg-blue-100 font-bold text-[11px] transition">
                                            View Asset &rarr;
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="p-6 text-center text-slate-400">No product equipment records found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- TAB 3: Department-Wise Summary -->
            <div id="content_tab_department" class="hidden">
                <div class="mb-3">
                    <h3 class="text-sm font-bold text-slate-900">Hospital Department Equipment Distribution</h3>
                    <p class="text-xs text-slate-500">Allocation of medical devices across Biomedical, ICU, OT, Emergency, and Wards.</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @forelse($departmentSummary as $dept)
                        @php
                            $rate = $dept['total_count'] > 0 ? round(($dept['completed'] / $dept['total_count']) * 100) : 0;
                        @endphp
                        <div class="bg-slate-50 rounded-xl p-4 border border-slate-200">
                            <div class="flex items-center justify-between mb-2">
                                <span class="font-bold text-slate-900 text-xs flex items-center">
                                    <i class="fa-solid fa-building text-purple-600 mr-2"></i> {{ $dept['department'] }}
                                </span>
                                <span class="text-xs font-black text-slate-800 bg-white px-2 py-0.5 rounded border border-slate-200">
                                    {{ $dept['total_count'] }} Equipment
                                </span>
                            </div>
                            <div class="flex justify-between text-[11px] text-slate-600 mb-1">
                                <span>Service Compliance: {{ $rate }}%</span>
                                <span>{{ $dept['completed'] }} Done / {{ $dept['pending'] }} Pending</span>
                            </div>
                            <div class="w-full bg-slate-200 h-2 rounded-full overflow-hidden">
                                <div class="bg-purple-600 h-full rounded-full" style="width: {{ $rate }}%"></div>
                            </div>
                            <div class="mt-3 text-right">
                                <a href="{{ route('reports.index', ['department' => $dept['department']]) }}" class="text-[11px] font-bold text-purple-700 hover:text-purple-900">
                                    Filter Department Records &rarr;
                                </a>
                            </div>
                        </div>
                    @empty
                        <div class="col-span-3 text-center py-6 text-slate-400">No department breakdown available.</div>
                    @endforelse
                </div>
            </div>

            <!-- TAB 4: PPM Schedule & Overdue Report -->
            <div id="content_tab_schedule" class="hidden">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
                    
                    <!-- Due Today List -->
                    <div class="border border-amber-200 rounded-xl bg-amber-50/40 p-4">
                        <h4 class="font-bold text-amber-900 text-xs uppercase tracking-wider mb-2 flex items-center justify-between">
                            <span><i class="fa-solid fa-bell text-amber-600 mr-1.5 animate-pulse"></i> PPM Due Today ({{ $statusSummary['due_today']->count() }})</span>
                            <span class="text-[10px] text-amber-700 bg-amber-200 px-2 py-0.5 rounded font-extrabold">{{ now()->format('d-M-Y') }}</span>
                        </h4>
                        <div class="space-y-2 max-h-80 overflow-y-auto pr-1">
                            @forelse($statusSummary['due_today'] as $r)
                                <div class="bg-white p-3 rounded-lg border border-amber-200 shadow-2xs flex items-center justify-between">
                                    <div>
                                        <p class="font-bold text-slate-900 text-xs">{{ $r->equipment_name ?: $r->product_description }}</p>
                                        <p class="text-[11px] text-slate-600"><i class="fa-solid fa-hospital text-slate-400 mr-1"></i>{{ $r->customer_name }}</p>
                                        <p class="text-[10px] text-slate-400">PO: {{ $r->purchase_order_no ?: 'N/A' }} | S/N: {{ $r->serial_no ?: 'N/A' }}</p>
                                    </div>
                                    <a href="{{ route('ppm.show', $r) }}" class="px-2.5 py-1 rounded bg-amber-500 hover:bg-amber-600 text-white font-bold text-[10px] shrink-0 shadow-xs">
                                        View Sheet
                                    </a>
                                </div>
                            @empty
                                <div class="text-center py-6 text-amber-800 text-xs font-semibold">No equipment due for maintenance today.</div>
                            @endforelse
                        </div>
                    </div>

                    <!-- Overdue List -->
                    <div class="border border-rose-200 rounded-xl bg-rose-50/40 p-4">
                        <h4 class="font-bold text-rose-900 text-xs uppercase tracking-wider mb-2 flex items-center justify-between">
                            <span><i class="fa-solid fa-triangle-exclamation text-rose-600 mr-1.5"></i> Overdue PPM Alerts ({{ $statusSummary['overdue']->count() }})</span>
                            <span class="text-[10px] text-rose-700 bg-rose-200 px-2 py-0.5 rounded font-extrabold">Urgent Action</span>
                        </h4>
                        <div class="space-y-2 max-h-80 overflow-y-auto pr-1">
                            @forelse($statusSummary['overdue'] as $r)
                                <div class="bg-white p-3 rounded-lg border border-rose-200 shadow-2xs flex items-center justify-between">
                                    <div>
                                        <div class="flex items-center space-x-2">
                                            <p class="font-bold text-slate-900 text-xs">{{ $r->equipment_name ?: $r->product_description }}</p>
                                            <span class="bg-rose-100 text-rose-800 text-[9px] font-black px-1.5 py-0.5 rounded">
                                                Due: {{ optional($r->next_ppm_date)->format('d-M-Y') }}
                                            </span>
                                        </div>
                                        <p class="text-[11px] text-slate-600"><i class="fa-solid fa-hospital text-slate-400 mr-1"></i>{{ $r->customer_name }}</p>
                                        <p class="text-[10px] text-slate-400">Tech: {{ $r->performed_by ?: 'Unassigned' }}</p>
                                    </div>
                                    <a href="{{ route('ppm.show', $r) }}" class="px-2.5 py-1 rounded bg-rose-600 hover:bg-rose-700 text-white font-bold text-[10px] shrink-0 shadow-xs">
                                        Update Status
                                    </a>
                                </div>
                            @empty
                                <div class="text-center py-6 text-emerald-700 text-xs font-semibold">
                                    <i class="fa-solid fa-circle-check text-base mr-1"></i> Excellent! Zero overdue PPM maintenance records.
                                </div>
                            @endforelse
                        </div>
                    </div>

                </div>
            </div>

            <!-- TAB 5: All Filtered Detailed Records Table -->
            <div id="content_tab_all_records" class="hidden">
                <div class="overflow-x-auto border border-slate-200 rounded-xl">
                    <table class="w-full text-left text-xs text-slate-700">
                        <thead class="bg-slate-100 uppercase text-[11px] font-bold text-slate-600 border-b border-slate-200">
                            <tr>
                                <th class="p-3">Ref ID</th>
                                <th class="p-3">Customer / Hospital</th>
                                <th class="p-3">Equipment / Product</th>
                                <th class="p-3">Serial No</th>
                                <th class="p-3">Service Type</th>
                                <th class="p-3">Next PPM Date</th>
                                <th class="p-3">Status</th>
                                <th class="p-3 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200">
                            @forelse($records as $r)
                                <tr class="hover:bg-slate-50 transition">
                                    <td class="p-3 font-mono text-slate-500 font-bold">#PPM-{{ str_pad($r->id, 5, '0', STR_PAD_LEFT) }}</td>
                                    <td class="p-3 font-bold text-slate-900">{{ $r->customer_name }}</td>
                                    <td class="p-3 text-slate-800 font-semibold">{{ $r->equipment_name ?: $r->product_description }}</td>
                                    <td class="p-3 font-mono text-slate-600">{{ $r->serial_no ?: 'N/A' }}</td>
                                    <td class="p-3 text-slate-600">{{ $r->service_type }}</td>
                                    <td class="p-3 font-semibold">
                                        @if($r->next_ppm_date)
                                            @if($r->next_ppm_date->isToday())
                                                <span class="bg-amber-100 text-amber-900 font-black px-2 py-0.5 rounded text-[11px]">DUE TODAY</span>
                                            @elseif($r->next_ppm_date->isPast() && strtolower($r->status) !== 'completed')
                                                <span class="bg-rose-100 text-rose-900 font-black px-2 py-0.5 rounded text-[11px]">{{ $r->next_ppm_date->format('d-M-Y') }}</span>
                                            @else
                                                <span class="text-slate-700">{{ $r->next_ppm_date->format('d-M-Y') }}</span>
                                            @endif
                                        @else
                                            <span class="text-slate-400">N/A</span>
                                        @endif
                                    </td>
                                    <td class="p-3">
                                        @if(strtolower($r->status) === 'completed')
                                            <span class="px-2 py-0.5 bg-emerald-100 text-emerald-800 font-bold rounded-full text-[10px]">Completed</span>
                                        @else
                                            <span class="px-2 py-0.5 bg-slate-100 text-slate-700 font-bold rounded-full text-[10px]">{{ $r->status ?: 'Pending' }}</span>
                                        @endif
                                    </td>
                                    <td class="p-3 text-right">
                                        <a href="{{ route('ppm.show', $r) }}" class="inline-flex items-center px-2 py-1 bg-slate-800 hover:bg-slate-900 text-white rounded text-[10px] font-bold">
                                            View Sheet &rarr;
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="p-6 text-center text-slate-400">No records match the applied filter criteria.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>

</div>
@endsection

@push('scripts')
<!-- Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Tab Switcher Functionality
    function switchReportTab(tabId) {
        const tabs = ['customer', 'product', 'department', 'schedule', 'all_records'];
        tabs.forEach(t => {
            const content = document.getElementById('content_tab_' + t);
            const btn = document.getElementById('btn_tab_' + t);
            if (content && btn) {
                if (t === tabId) {
                    content.classList.remove('hidden');
                    btn.className = 'py-2 px-4 rounded-xl text-teal-800 bg-white shadow-xs border border-slate-200 transition flex items-center';
                } else {
                    content.classList.add('hidden');
                    btn.className = 'py-2 px-4 rounded-xl text-slate-600 hover:text-slate-900 transition flex items-center';
                }
            }
        });
    }

    // Chart 1: Customer Distribution Bar Chart
    const customerCtx = document.getElementById('customerChart').getContext('2d');
    new Chart(customerCtx, {
        type: 'bar',
        data: {
            labels: {!! json_encode($customerSummary->take(6)->pluck('customer_name')) !!},
            datasets: [{
                label: 'Equipment Count',
                data: {!! json_encode($customerSummary->take(6)->pluck('total_equipment')) !!},
                backgroundColor: 'rgba(13, 148, 136, 0.85)',
                borderRadius: 6,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                y: { beginAtZero: true, ticks: { precision: 0 } },
                x: { ticks: { font: { size: 10 } } }
            }
        }
    });

    // Chart 2: PPM Status Doughnut
    const statusCtx = document.getElementById('statusChart').getContext('2d');
    new Chart(statusCtx, {
        type: 'doughnut',
        data: {
            labels: ['Due Today', 'Overdue', 'Upcoming', 'Completed'],
            datasets: [{
                data: [
                    {{ $summary['due_today'] }},
                    {{ $summary['overdue'] }},
                    {{ $summary['upcoming_7_days'] }},
                    {{ $summary['completed'] }}
                ],
                backgroundColor: ['#f59e0b', '#e11d48', '#0d9488', '#10b981'],
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'right', labels: { font: { size: 10 } } }
            }
        }
    });

    // Chart 3: Department Assets Bar Chart
    const deptCtx = document.getElementById('deptChart').getContext('2d');
    new Chart(deptCtx, {
        type: 'bar',
        data: {
            labels: {!! json_encode($departmentSummary->take(6)->pluck('department')) !!},
            datasets: [{
                label: 'Assets',
                data: {!! json_encode($departmentSummary->take(6)->pluck('total_count')) !!},
                backgroundColor: 'rgba(147, 51, 234, 0.85)',
                borderRadius: 6
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                y: { beginAtZero: true, ticks: { precision: 0 } },
                x: { ticks: { font: { size: 10 } } }
            }
        }
    });
</script>
@endpush

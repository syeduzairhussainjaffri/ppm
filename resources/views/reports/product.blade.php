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
                    <i class="fa-solid fa-boxes-stacked mr-1"></i> Equipment Inventory Audit
                </span>
            </div>
            <h1 class="text-2xl font-black text-white mt-1.5 tracking-tight">Product-Wise Equipment Report</h1>
            <p class="text-xs text-slate-300 mt-1 max-w-xl">
                Installed base deployment per medical device model, customer location map, serial number tracking, and PPM cycle compliance.
            </p>
        </div>

        <div class="flex items-center space-x-2.5">
            <a href="{{ route('reports.index') }}" class="px-3 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-bold transition">
                &larr; Back to Reports Hub
            </a>
            <a href="{{ route('reports.print', ['report_title' => 'Product Equipment Report'] + request()->query()) }}" target="_blank" class="px-4 py-2 rounded-xl bg-blue-600 hover:bg-blue-500 text-white text-xs font-bold transition shadow-md">
                <i class="fa-solid fa-print mr-1.5"></i> Print Equipment Sheet
            </a>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="bg-white rounded-2xl p-4 shadow-sm border border-slate-200">
        <form action="{{ route('reports.product') }}" method="GET" class="flex flex-col sm:flex-row gap-3 items-center justify-between text-xs">
            <div class="flex-1 flex flex-col sm:flex-row gap-3 w-full">
                <div class="w-full sm:w-72">
                    <label class="block font-medium text-slate-700 mb-1">Select Product / Equipment</label>
                    <select name="product_id" onchange="this.form.submit()" class="w-full py-2 px-3 rounded-xl border border-slate-300 focus:ring-2 focus:ring-blue-500 text-xs font-semibold">
                        <option value="">-- All Products ({{ $products->count() }}) --</option>
                        @foreach($products as $p)
                            <option value="{{ $p->id }}" {{ $selectedProductId == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="w-full sm:w-72">
                    <label class="block font-medium text-slate-700 mb-1">Search Model / Name</label>
                    <input type="text" name="search" value="{{ $productSearch }}" placeholder="Search equipment model..." class="w-full py-2 px-3 rounded-xl border border-slate-300 focus:ring-2 focus:ring-blue-500 text-xs">
                </div>
            </div>

            <div class="flex items-center space-x-2 self-end sm:self-auto shrink-0 w-full sm:w-auto">
                <button type="submit" class="w-full sm:w-auto py-2 px-4 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs shadow-xs">
                    Filter Product
                </button>
                @if($selectedProductId || $productSearch)
                    <a href="{{ route('reports.product') }}" class="py-2 px-3 rounded-xl border border-slate-300 text-slate-600 hover:bg-slate-50 font-semibold text-xs">
                        Clear
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Product Detailed Cards -->
    <div class="space-y-6">
        @forelse($productReports as $report)
            @php
                $prod = $report['product'];
                $records = $report['records'];
            @endphp
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                <!-- Product Card Header -->
                <div class="bg-slate-900 text-white p-4 border-b border-slate-800 flex flex-col md:flex-row md:items-center justify-between gap-3">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-blue-600 to-indigo-500 text-white font-black text-lg flex items-center justify-center shadow">
                            <i class="fa-solid fa-microscope"></i>
                        </div>
                        <div>
                            <h2 class="text-base font-bold tracking-tight text-white">{{ $prod->name }}</h2>
                            <p class="text-xs text-slate-400 flex items-center space-x-3 mt-0.5">
                                <span><i class="fa-solid fa-tag text-blue-400 mr-1"></i> Equipment: {{ $prod->equipment_name ?: $prod->name }}</span>
                                <span>&bull;</span>
                                <span><i class="fa-solid fa-industry text-blue-400 mr-1"></i> Make: {{ $prod->model_make ?: 'Standard' }}</span>
                                <span>&bull;</span>
                                <span><i class="fa-solid fa-clock text-blue-400 mr-1"></i> Cycle: {{ $prod->default_ppm_days ?: 30 }} Days</span>
                            </p>
                        </div>
                    </div>

                    <!-- Product Quick Stats Badges -->
                    <div class="flex items-center space-x-2 text-xs">
                        <span class="px-2.5 py-1 rounded-lg bg-blue-900 text-blue-200 border border-blue-700 font-bold">
                            Total Installed: {{ $report['total_installed'] }} Units
                        </span>
                        <span class="px-2.5 py-1 rounded-lg bg-slate-800 text-slate-300 font-medium">
                            Hospitals: {{ $report['unique_hospitals'] }}
                        </span>
                        @if($report['due_today'] > 0)
                            <span class="px-2.5 py-1 rounded-lg bg-amber-500 text-slate-950 font-extrabold animate-pulse">
                                {{ $report['due_today'] }} Due Today
                            </span>
                        @endif
                    </div>
                </div>

                <!-- Hospital Deployments Table for this Product -->
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs text-slate-700">
                        <thead class="bg-slate-100 uppercase text-[11px] font-bold text-slate-600 border-b border-slate-200">
                            <tr>
                                <th class="p-3">Hospital / Customer Name</th>
                                <th class="p-3">Department</th>
                                <th class="p-3">Serial No</th>
                                <th class="p-3">Service Type</th>
                                <th class="p-3">Installation Date</th>
                                <th class="p-3">Next PPM Date</th>
                                <th class="p-3">Status</th>
                                <th class="p-3 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200">
                            @forelse($records as $r)
                                <tr class="hover:bg-slate-50 transition">
                                    <td class="p-3 font-bold text-slate-900 flex items-center space-x-2">
                                        <i class="fa-solid fa-hospital text-slate-400"></i>
                                        <span>{{ $r->customer_name }}</span>
                                    </td>
                                    <td class="p-3 text-slate-600">{{ $r->department ?: 'General' }}</td>
                                    <td class="p-3 font-mono text-slate-800 font-bold">{{ $r->serial_no ?: 'N/A' }}</td>
                                    <td class="p-3 text-slate-600">{{ $r->service_type }}</td>
                                    <td class="p-3 text-slate-600">{{ optional($r->installation_date)->format('d-M-Y') ?: 'N/A' }}</td>
                                    <td class="p-3 font-bold">
                                        @if($r->next_ppm_date)
                                            @if($r->next_ppm_date->isToday())
                                                <span class="px-2 py-0.5 bg-amber-100 text-amber-900 font-black rounded">DUE TODAY</span>
                                            @elseif($r->next_ppm_date->isPast() && strtolower($r->status) !== 'completed')
                                                <span class="px-2 py-0.5 bg-rose-100 text-rose-900 font-black rounded">{{ $r->next_ppm_date->format('d-M-Y') }}</span>
                                            @else
                                                <span class="text-slate-800">{{ $r->next_ppm_date->format('d-M-Y') }}</span>
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
                                        <a href="{{ route('ppm.show', $r) }}" class="inline-flex items-center px-2 py-1 bg-blue-600 hover:bg-blue-700 text-white rounded text-[10px] font-bold shadow-xs">
                                            Voucher &rarr;
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="p-4 text-center text-slate-400 italic">No deployment or installation records found for this equipment.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

            </div>
        @empty
            <div class="bg-white rounded-2xl p-12 text-center text-slate-400 border border-slate-200">
                <i class="fa-solid fa-boxes-stacked text-4xl text-slate-300 mb-2"></i>
                <p class="font-bold text-slate-600 text-sm">No Product Deployment Records Found</p>
                <p class="text-xs text-slate-400 mt-1">Select a product from the filter above to view deployment details.</p>
            </div>
        @endforelse
    </div>

</div>
@endsection

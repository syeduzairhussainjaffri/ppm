@extends('ppm.layout')

@section('title', 'Customer-Wise Equipment & PPM Audit Report')
@section('page_category', 'Customer Reports')

@section('content')
<div class="space-y-6">

    <!-- Header Banner -->
    <div class="bg-gradient-to-r from-slate-900 via-slate-800 to-teal-900 rounded-2xl p-5 text-white shadow-xl flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center space-x-2">
                <span class="bg-teal-500/20 text-teal-300 text-[10px] font-extrabold uppercase px-2.5 py-1 rounded-full border border-teal-500/40 tracking-wider">
                    <i class="fa-solid fa-hospital mr-1"></i> Hospital Asset Audit
                </span>
            </div>
            <h1 class="text-2xl font-black text-white mt-1.5 tracking-tight">Customer-Wise PPM Equipment Report</h1>
            <p class="text-xs text-slate-300 mt-1 max-w-xl">
                Detailed customer asset inventory, field installation records, and preventive maintenance compliance for hospitals and medical centers.
            </p>
        </div>

        <div class="flex items-center space-x-2.5">
            <a href="{{ route('reports.index') }}" class="px-3 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-bold transition">
                &larr; Back to Reports Hub
            </a>
            <a href="{{ route('reports.print', ['report_title' => 'Customer Wise PPM Report'] + request()->query()) }}" target="_blank" class="px-4 py-2 rounded-xl bg-teal-600 hover:bg-teal-500 text-white text-xs font-bold transition shadow-md">
                <i class="fa-solid fa-print mr-1.5"></i> Print Customer Audit Sheet
            </a>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="bg-white rounded-2xl p-4 shadow-sm border border-slate-200">
        <form action="{{ route('reports.customer') }}" method="GET" class="flex flex-col sm:flex-row gap-3 items-center justify-between text-xs">
            <div class="flex-1 flex flex-col sm:flex-row gap-3 w-full">
                <div class="w-full sm:w-72">
                    <label class="block font-medium text-slate-700 mb-1">Select Customer / Hospital</label>
                    <select name="customer_id" onchange="this.form.submit()" class="w-full py-2 px-3 rounded-xl border border-slate-300 focus:ring-2 focus:ring-teal-500 text-xs font-semibold">
                        <option value="">-- All Customers ({{ $customers->count() }}) --</option>
                        @foreach($customers as $c)
                            <option value="{{ $c->id }}" {{ $selectedCustomerId == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="w-full sm:w-72">
                    <label class="block font-medium text-slate-700 mb-1">Search Customer Name / Dept</label>
                    <input type="text" name="search" value="{{ $customerSearch }}" placeholder="Search hospital name..." class="w-full py-2 px-3 rounded-xl border border-slate-300 focus:ring-2 focus:ring-teal-500 text-xs">
                </div>
            </div>

            <div class="flex items-center space-x-2 self-end sm:self-auto shrink-0 w-full sm:w-auto">
                <button type="submit" class="w-full sm:w-auto py-2 px-4 rounded-xl bg-teal-600 hover:bg-teal-700 text-white font-bold text-xs shadow-xs">
                    Filter Customer
                </button>
                @if($selectedCustomerId || $customerSearch)
                    <a href="{{ route('reports.customer') }}" class="py-2 px-3 rounded-xl border border-slate-300 text-slate-600 hover:bg-slate-50 font-semibold text-xs">
                        Clear
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Customer Detailed Cards -->
    <div class="space-y-6">
        @forelse($customerReports as $report)
            @php
                $cust = $report['customer'];
                $records = $report['records'];
            @endphp
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                <!-- Customer Card Header -->
                <div class="bg-slate-900 text-white p-4 border-b border-slate-800 flex flex-col md:flex-row md:items-center justify-between gap-3">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-teal-500 to-emerald-400 text-white font-black text-lg flex items-center justify-center shadow">
                            <i class="fa-solid fa-hospital font-black"></i>
                        </div>
                        <div>
                            <h2 class="text-base font-bold tracking-tight text-white">{{ $cust->name }}</h2>
                            <p class="text-xs text-slate-400 flex items-center space-x-3 mt-0.5">
                                <span><i class="fa-solid fa-user text-teal-400 mr-1"></i> Contact: {{ $cust->contact_person ?: 'N/A' }} ({{ $cust->contact_no ?: 'N/A' }})</span>
                                <span>&bull;</span>
                                <span><i class="fa-solid fa-building text-teal-400 mr-1"></i> Dept: {{ $cust->department ?: 'General' }}</span>
                            </p>
                        </div>
                    </div>

                    <!-- Customer Quick Stats Badges -->
                    <div class="flex items-center space-x-2 text-xs">
                        <span class="px-2.5 py-1 rounded-lg bg-slate-800 text-slate-200 border border-slate-700 font-bold">
                            Total: {{ $report['total_equipment'] }} Equipment
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
                    </div>
                </div>

                <!-- Installed Assets Table for this Customer -->
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs text-slate-700">
                        <thead class="bg-slate-100 uppercase text-[11px] font-bold text-slate-600 border-b border-slate-200">
                            <tr>
                                <th class="p-3">Equipment / Product Description</th>
                                <th class="p-3">Model / Make</th>
                                <th class="p-3">Serial No</th>
                                <th class="p-3">PO Number</th>
                                <th class="p-3">Installation Date</th>
                                <th class="p-3">Next PPM Date</th>
                                <th class="p-3">Status</th>
                                <th class="p-3 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200">
                            @forelse($records as $r)
                                <tr class="hover:bg-slate-50 transition">
                                    <td class="p-3 font-bold text-slate-900">{{ $r->equipment_name ?: $r->product_description }}</td>
                                    <td class="p-3 text-slate-600">{{ $r->model_make ?: 'N/A' }}</td>
                                    <td class="p-3 font-mono text-slate-800 font-semibold">{{ $r->serial_no ?: 'N/A' }}</td>
                                    <td class="p-3 text-slate-600">{{ $r->purchase_order_no ?: 'N/A' }}</td>
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
                                        <a href="{{ route('ppm.show', $r) }}" class="inline-flex items-center px-2 py-1 bg-teal-600 hover:bg-teal-700 text-white rounded text-[10px] font-bold shadow-xs">
                                            Voucher &rarr;
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="p-4 text-center text-slate-400 italic">No installation or maintenance records found for this customer.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

            </div>
        @empty
            <div class="bg-white rounded-2xl p-12 text-center text-slate-400 border border-slate-200">
                <i class="fa-solid fa-hospital text-4xl text-slate-300 mb-2"></i>
                <p class="font-bold text-slate-600 text-sm">No Customer Equipment Records Found</p>
                <p class="text-xs text-slate-400 mt-1">Try selecting a different customer from the dropdown above.</p>
            </div>
        @endforelse
    </div>

</div>
@endsection

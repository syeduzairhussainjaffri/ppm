@extends('ppm.layout')

@section('title', 'CRM & PPM Dashboard')
@section('page_category', 'Records Register')

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Installation &amp; PPM Record Register</h1>
            <p class="text-sm text-slate-500 mt-1">Manage field service sheets, dynamic customer accounts, and scheduled maintenance notifications.</p>
        </div>
        <div class="flex items-center gap-3">
            @can('export_ppm')
            <a href="{{ route('ppm.export') }}" class="inline-flex items-center px-4 py-2 bg-white border border-slate-300 rounded-lg text-sm font-semibold text-slate-700 hover:bg-slate-50 transition shadow-xs">
                <i class="fa-solid fa-file-arrow-down mr-2 text-emerald-600"></i> Export CSV
            </a>
            @endcan
            @can('import_ppm')
            <button type="button" onclick="document.getElementById('ppmImportModal').classList.remove('hidden')" class="inline-flex items-center px-4 py-2 bg-white border border-slate-300 rounded-lg text-sm font-semibold text-slate-700 hover:bg-slate-50 transition shadow-xs">
                <i class="fa-solid fa-file-arrow-up mr-2 text-blue-600"></i> Import CSV
            </button>
            @endcan
            @can('create_ppm')
            <a href="{{ route('ppm.create') }}" class="inline-flex items-center px-4 py-2 bg-teal-600 hover:bg-teal-700 text-white text-sm font-semibold rounded-lg shadow-sm transition">
                <i class="fa-solid fa-plus mr-2"></i> Add Record
            </a>
            @endcan
        </div>
    </div>

    <!-- ================= NOTIFICATION CENTER & CONTROLS ================= -->
    <div class="bg-white rounded-2xl border-2 {{ ($stats['due_today'] ?? 0) > 0 ? 'border-amber-400 shadow-md ring-2 ring-amber-400/20' : 'border-slate-200 shadow-xs' }} p-5 space-y-4">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-3 border-b border-slate-100 pb-3">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 rounded-xl {{ ($stats['due_today'] ?? 0) > 0 ? 'bg-amber-500 text-white animate-bounce' : 'bg-teal-50 text-teal-600' }} flex items-center justify-center text-lg shadow-xs shrink-0">
                    <i class="fa-solid fa-bell"></i>
                </div>
                <div>
                    <h3 class="font-bold text-base text-slate-900 flex items-center gap-2">
                        PPM Date Notification System
                        @if(($stats['due_today'] ?? 0) > 0)
                            <span class="bg-amber-100 text-amber-900 border border-amber-300 text-xs px-2.5 py-0.5 rounded-full font-extrabold uppercase">
                                🔔 {{ $stats['due_today'] }} Record Due Today
                            </span>
                        @else
                            <span class="bg-emerald-50 text-emerald-700 border border-emerald-200 text-xs px-2 py-0.5 rounded-full font-semibold">
                                System Active
                            </span>
                        @endif
                    </h3>
                    <p class="text-xs text-slate-500 mt-0.5">
                        System Date: <strong class="text-slate-700 font-mono">{{ now()->format('d-M-Y') }}</strong> &bull; Alerts trigger automatically on scheduled Next PPM Dates.
                    </p>
                </div>
            </div>

            <!-- Desktop Notification & Test Sound Trigger -->
            <div class="flex items-center gap-2 shrink-0">
                <button type="button" onclick="playPpmChime(); showPpmToast('🔔 Notification Sound Test', 'PPM Alert chime tested successfully!', null, 'amber');" class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-semibold transition flex items-center" title="Test notification sound">
                    <i class="fa-solid fa-volume-high mr-1.5 text-slate-500"></i> Test Chime
                </button>
                <button type="button" onclick="requestDesktopNotifications(true)" id="desktopAlertBtn" class="px-4 py-2 bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-600 hover:to-amber-700 text-white rounded-xl text-xs font-bold shadow-xs transition flex items-center">
                    <i class="fa-solid fa-desktop mr-1.5"></i> <span id="desktopAlertText">Test / Enable Desktop Notification</span>
                </button>
            </div>
        </div>

        <!-- Active Due Today Alert Row if any -->
        @if(($stats['due_today'] ?? 0) > 0)
            <div class="p-4 rounded-xl bg-amber-500/15 border border-amber-300/80 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div class="flex items-start space-x-3">
                    <i class="fa-solid fa-circle-exclamation text-amber-600 text-lg mt-0.5"></i>
                    <div>
                        <h4 class="font-bold text-xs uppercase tracking-wide text-amber-950">
                            Attention: {{ $stats['due_today'] }} Equipment Scheduled for PPM Today ({{ now()->format('d-M-Y') }})
                        </h4>
                        <div class="mt-1 flex flex-wrap gap-2 text-xs">
                            @foreach($navDueToday ?? [] as $dt)
                                <span class="inline-flex items-center bg-white px-2.5 py-1 rounded-md border border-amber-300 text-slate-800 font-medium">
                                    <strong class="text-amber-900 mr-1">{{ $dt->customer_name }}:</strong> {{ $dt->equipment_name ?: $dt->product_description }}
                                    <a href="{{ route('ppm.show', $dt) }}" class="ml-2 text-teal-700 font-bold hover:underline">View Sheet &rarr;</a>
                                </span>
                            @endforeach
                        </div>
                    </div>
                </div>
                <a href="{{ route('ppm.index', ['ppm_status' => 'due_today']) }}" class="px-3.5 py-1.5 bg-amber-600 hover:bg-amber-700 text-white font-bold text-xs rounded-lg shadow-xs transition shrink-0 inline-flex items-center">
                    <i class="fa-solid fa-filter mr-1.5"></i> Filter Today's Due
                </a>
            </div>
        @endif

        <!-- Upcoming Active Scheduled Dates Summary -->
        @if(isset($navUpcomingAll) && $navUpcomingAll->count() > 0)
            <div class="text-xs">
                <p class="font-bold text-slate-700 uppercase tracking-wider text-[11px] mb-2 flex items-center">
                    <i class="fa-solid fa-calendar-days text-teal-600 mr-1.5"></i> Scheduled PPM Date Reminders:
                </p>
                <div class="flex flex-wrap gap-2">
                    @foreach($navUpcomingAll->take(6) as $up)
                        @php
                            $isToday = $up->next_ppm_date && $up->next_ppm_date->isToday();
                            $daysLeft = $up->next_ppm_date ? ceil(now()->diffInDays($up->next_ppm_date, false)) : 0;
                        @endphp
                        <a href="{{ route('ppm.show', $up) }}" class="inline-flex items-center px-3 py-1.5 rounded-lg border {{ $isToday ? 'bg-amber-50 border-amber-300 text-amber-950 font-bold animate-pulse' : 'bg-slate-50 border-slate-200 text-slate-700 hover:bg-slate-100 font-medium' }} transition">
                            <span class="mr-1.5">{{ $isToday ? '🔔 Today:' : '📅' }} <strong>{{ optional($up->next_ppm_date)->format('d-M-Y') }}</strong></span>
                            <span class="text-slate-400 mx-1">|</span>
                            <span class="truncate max-w-[180px]">{{ $up->customer_name }} ({{ $up->equipment_name ?: $up->product_description }})</span>
                            @if(!$isToday)
                                <span class="ml-1.5 text-[10px] bg-slate-200 text-slate-600 px-1.5 py-0.2 rounded font-semibold">{{ $daysLeft }}d left</span>
                            @endif
                        </a>
                    @endforeach
                </div>
            </div>
        @endif
    </div>

    <!-- Stats Cards with Clickable Filters -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Total Records -->
        <a href="{{ route('ppm.index') }}" class="block bg-white p-5 rounded-2xl border {{ empty($ppmStatus) ? 'border-teal-500 ring-2 ring-teal-500/20' : 'border-slate-200' }} hover:shadow-md transition shadow-xs">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Total Records</p>
                    <p class="text-3xl font-extrabold text-slate-800 mt-1">{{ $stats['total'] }}</p>
                </div>
                <div class="w-12 h-12 rounded-xl bg-slate-100 text-slate-700 flex items-center justify-center text-xl">
                    <i class="fa-solid fa-folder-open"></i>
                </div>
            </div>
            <p class="text-[11px] text-slate-500 mt-3 font-medium">
                All service entries
            </p>
        </a>

        <!-- Due Today Card -->
        <a href="{{ route('ppm.index', ['ppm_status' => 'due_today']) }}" class="block bg-white p-5 rounded-2xl border {{ ($ppmStatus ?? '') === 'due_today' ? 'border-amber-500 ring-2 ring-amber-500/30 bg-amber-50/20' : 'border-slate-200' }} hover:shadow-md transition shadow-xs">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-bold uppercase tracking-wider text-amber-700 flex items-center gap-1">
                        <i class="fa-solid fa-bell text-amber-500 animate-pulse"></i> Due Today
                    </p>
                    <p class="text-3xl font-extrabold text-amber-600 mt-1">{{ $stats['due_today'] ?? 0 }}</p>
                </div>
                <div class="w-12 h-12 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center text-xl">
                    <i class="fa-solid fa-calendar-day"></i>
                </div>
            </div>
            <p class="text-[11px] {{ ($stats['due_today'] ?? 0) > 0 ? 'text-amber-700 font-bold' : 'text-slate-400' }} mt-3">
                {{ ($stats['due_today'] ?? 0) > 0 ? '🔔 Requires maintenance today!' : 'No scheduled PPM today' }}
            </p>
        </a>

        <!-- Overdue Card -->
        <a href="{{ route('ppm.index', ['ppm_status' => 'overdue']) }}" class="block bg-white p-5 rounded-2xl border {{ ($ppmStatus ?? '') === 'overdue' ? 'border-rose-500 ring-2 ring-rose-500/30 bg-rose-50/20' : 'border-slate-200' }} hover:shadow-md transition shadow-xs">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-bold uppercase tracking-wider text-rose-700">Overdue PPM</p>
                    <p class="text-3xl font-extrabold text-rose-600 mt-1">{{ $stats['overdue'] ?? 0 }}</p>
                </div>
                <div class="w-12 h-12 rounded-xl bg-rose-100 text-rose-700 flex items-center justify-center text-xl">
                    <i class="fa-solid fa-clock-rotate-left"></i>
                </div>
            </div>
            <p class="text-[11px] {{ ($stats['overdue'] ?? 0) > 0 ? 'text-rose-700 font-semibold' : 'text-slate-400' }} mt-3">
                {{ ($stats['overdue'] ?? 0) > 0 ? 'Past scheduled PPM date' : 'No overdue records' }}
            </p>
        </a>

        <!-- Upcoming PPM Due -->
        <a href="{{ route('ppm.index', ['ppm_status' => 'upcoming_week']) }}" class="block bg-white p-5 rounded-2xl border {{ ($ppmStatus ?? '') === 'upcoming_week' ? 'border-teal-500 ring-2 ring-teal-500/30 bg-teal-50/20' : 'border-slate-200' }} hover:shadow-md transition shadow-xs">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-teal-700">Next 7 Days PPM</p>
                    <p class="text-3xl font-extrabold text-teal-600 mt-1">{{ $stats['upcoming_ppm'] }}</p>
                </div>
                <div class="w-12 h-12 rounded-xl bg-teal-50 text-teal-600 flex items-center justify-center text-xl">
                    <i class="fa-solid fa-calendar-check"></i>
                </div>
            </div>
            <p class="text-[11px] text-teal-700 mt-3 font-medium">
                Upcoming planned schedules
            </p>
        </a>
    </div>

    <!-- Filter & Search Bar with PPM Status Options -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm space-y-3">
        <form action="{{ route('ppm.index') }}" method="GET" class="flex flex-col md:flex-row gap-3">
            <div class="relative flex-grow">
                <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-slate-400">
                    <i class="fa-solid fa-magnifying-glass"></i>
                </span>
                <input type="text" name="search" value="{{ $search }}" placeholder="Search by Customer, PO #, Serial #, Equipment, Engineer..." 
                       class="w-full pl-10 pr-4 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-teal-500">
            </div>

            <div class="flex flex-wrap gap-2">
                <!-- PPM Status Filter -->
                <select name="ppm_status" class="px-3 py-2 border border-slate-300 rounded-lg text-sm text-slate-700 bg-white focus:outline-none focus:ring-2 focus:ring-amber-500 font-medium">
                    <option value="">All PPM Schedules</option>
                    <option value="due_today" {{ ($ppmStatus ?? '') === 'due_today' ? 'selected' : '' }}>🔔 Due Today ({{ $stats['due_today'] ?? 0 }})</option>
                    <option value="overdue" {{ ($ppmStatus ?? '') === 'overdue' ? 'selected' : '' }}>⚠️ Overdue ({{ $stats['overdue'] ?? 0 }})</option>
                    <option value="upcoming_week" {{ ($ppmStatus ?? '') === 'upcoming_week' ? 'selected' : '' }}>📅 Next 7 Days</option>
                </select>

                <!-- Service Type Filter -->
                <select name="service_type" class="px-3 py-2 border border-slate-300 rounded-lg text-sm text-slate-700 bg-white focus:outline-none focus:ring-2 focus:ring-teal-500">
                    <option value="">All Service Types</option>
                    <option value="New Installation" {{ $serviceType == 'New Installation' ? 'selected' : '' }}>New Installation</option>
                    <option value="PPM" {{ $serviceType == 'PPM' ? 'selected' : '' }}>PPM</option>
                    <option value="Equipment CRM Profile" {{ $serviceType == 'Equipment CRM Profile' ? 'selected' : '' }}>Equipment CRM Profile</option>
                </select>

                <button type="submit" class="px-4 py-2 bg-slate-900 text-white rounded-lg text-sm font-medium hover:bg-slate-800 transition">
                    Filter
                </button>

                @if($search || $department || $serviceType || ($ppmStatus ?? ''))
                    <a href="{{ route('ppm.index') }}" class="px-3 py-2 bg-slate-100 text-slate-600 rounded-lg text-sm font-medium hover:bg-slate-200 transition">
                        Reset
                    </a>
                @endif
            </div>
        </form>

        <!-- Active Filter Indicator Pill -->
        @if(!empty($ppmStatus))
            <div class="flex items-center gap-2 pt-1 border-t border-slate-100 text-xs">
                <span class="text-slate-500 font-medium">Active Schedule Filter:</span>
                @if($ppmStatus === 'due_today')
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-100 text-amber-900 border border-amber-300">
                        <i class="fa-solid fa-bell mr-1 text-amber-600"></i> PPM Due Today ({{ $records->total() }} records)
                    </span>
                @elseif($ppmStatus === 'overdue')
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-rose-100 text-rose-800 border border-rose-300">
                        <i class="fa-solid fa-triangle-exclamation mr-1 text-rose-600"></i> Overdue PPM ({{ $records->total() }} records)
                    </span>
                @elseif($ppmStatus === 'upcoming_week')
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-teal-100 text-teal-800 border border-teal-300">
                        <i class="fa-solid fa-calendar-check mr-1 text-teal-600"></i> Scheduled Next 7 Days ({{ $records->total() }} records)
                    </span>
                @endif
                <a href="{{ route('ppm.index') }}" class="text-slate-400 hover:text-slate-700 text-xs underline ml-1">Clear Filter</a>
            </div>
        @endif
    </div>

    <!-- Records Table -->
    <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm text-left">
                <thead class="bg-slate-50 text-slate-600 font-semibold uppercase text-xs tracking-wider">
                    <tr>
                        <th class="py-3.5 px-4">PO / Form #</th>
                        <th class="py-3.5 px-4">Customer &amp; Dept</th>
                        <th class="py-3.5 px-4">Equipment &amp; Model</th>
                        <th class="py-3.5 px-4">Serial No.</th>
                        <th class="py-3.5 px-4">Type &amp; Engineer</th>
                        <th class="py-3.5 px-4">Install Date</th>
                        <th class="py-3.5 px-4">Next PPM Schedule</th>
                        <th class="py-3.5 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 text-slate-700">
                    @forelse($records as $rec)
                        @php
                            $isDueToday = $rec->next_ppm_date && $rec->next_ppm_date->isToday();
                            $isOverdue = $rec->next_ppm_date && $rec->next_ppm_date->isPast() && !$rec->next_ppm_date->isToday() && $rec->status !== 'Completed';
                            $isUpcomingSoon = $rec->next_ppm_date && $rec->next_ppm_date->isFuture() && $rec->next_ppm_date->diffInDays(now()) <= 7;
                        @endphp
                        <tr class="hover:bg-slate-50 transition {{ $isDueToday ? 'bg-amber-50/60 font-medium' : ($isOverdue ? 'bg-rose-50/20' : '') }}">
                            <td class="py-4 px-4 font-medium text-slate-900 whitespace-nowrap">
                                <span class="text-teal-700 font-bold block">{{ $rec->purchase_order_no ?: 'PO Pending' }}</span>
                                <span class="text-xs text-slate-500">{{ $rec->record_type }}</span>
                            </td>
                            <td class="py-4 px-4">
                                <div class="font-semibold text-slate-900">{{ $rec->customer_name }}</div>
                                <div class="text-xs text-slate-500">
                                    <i class="fa-solid fa-building text-slate-400 mr-1"></i>{{ $rec->department ?: 'N/A' }}
                                </div>
                            </td>
                            <td class="py-4 px-4">
                                <div class="font-medium text-slate-800">{{ $rec->equipment_name ?: $rec->product_description }}</div>
                                <div class="text-xs text-slate-500">{{ $rec->model_make ?: 'Model: N/A' }}</div>
                            </td>
                            <td class="py-4 px-4 whitespace-nowrap font-mono text-xs">
                                <span class="bg-slate-100 text-slate-800 px-2 py-1 rounded border border-slate-200">
                                    {{ $rec->serial_no ?: 'N/A' }}
                                </span>
                            </td>
                            <td class="py-4 px-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold
                                    {{ $rec->service_type == 'New Installation' ? 'bg-blue-100 text-blue-800' : 'bg-emerald-100 text-emerald-800' }}">
                                    {{ $rec->service_type }}
                                </span>
                                <div class="text-xs text-slate-500 mt-1">
                                    <i class="fa-solid fa-user-gear text-slate-400 mr-1"></i>{{ $rec->performed_by ?: 'Unassigned' }}
                                </div>
                            </td>
                            <td class="py-4 px-4 whitespace-nowrap text-xs">
                                {{ optional($rec->installation_date)->format('d-M-Y') ?: '-' }}
                            </td>
                            <td class="py-4 px-4 whitespace-nowrap text-xs">
                                @if($rec->next_ppm_date)
                                    @if($isDueToday)
                                        <div class="inline-flex items-center px-2.5 py-1.5 rounded-lg font-bold text-amber-950 bg-amber-100 border border-amber-400 shadow-xs animate-pulse">
                                            <i class="fa-solid fa-bell mr-1.5 text-amber-600"></i>
                                            <span>DUE TODAY ({{ $rec->next_ppm_date->format('d-M-Y') }})</span>
                                        </div>
                                    @elseif($isOverdue)
                                        <div class="inline-flex items-center px-2.5 py-1 rounded-lg font-bold text-rose-800 bg-rose-100 border border-rose-300">
                                            <i class="fa-solid fa-triangle-exclamation mr-1.5 text-rose-600"></i>
                                            <span>OVERDUE ({{ $rec->next_ppm_date->format('d-M-Y') }})</span>
                                        </div>
                                    @elseif($isUpcomingSoon)
                                        <div class="inline-flex items-center px-2.5 py-1 rounded-lg font-semibold text-teal-800 bg-teal-50 border border-teal-300">
                                            <i class="fa-solid fa-clock mr-1 text-teal-600"></i>
                                            <span>Due in {{ ceil(now()->diffInDays($rec->next_ppm_date, false)) }}d ({{ $rec->next_ppm_date->format('d-M-Y') }})</span>
                                        </div>
                                    @else
                                        <span class="inline-flex items-center font-medium text-slate-700 bg-slate-100 px-2.5 py-1 rounded-md border border-slate-200">
                                            <i class="fa-solid fa-calendar-check mr-1.5 text-slate-400"></i>
                                            {{ $rec->next_ppm_date->format('d-M-Y') }}
                                        </span>
                                    @endif
                                @else
                                    <span class="text-slate-400">-</span>
                                @endif
                            </td>
                            <td class="py-4 px-4 whitespace-nowrap text-right space-x-1.5">
                                @can('print_ppm')
                                <a href="{{ route('ppm.show', $rec) }}" title="View Printable Voucher" class="inline-flex items-center px-2.5 py-1.5 rounded-md bg-teal-50 text-teal-700 hover:bg-teal-100 font-medium text-xs transition">
                                    <i class="fa-solid fa-print mr-1"></i> View Form
                                </a>
                                @endcan
                                @can('edit_ppm')
                                <a href="{{ route('ppm.edit', $rec) }}" title="Edit Record" class="inline-flex items-center px-2.5 py-1.5 rounded-md bg-slate-100 text-slate-700 hover:bg-slate-200 text-xs transition">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </a>
                                @endcan
                                @can('delete_ppm')
                                <form action="{{ route('ppm.destroy', $rec) }}" method="POST" class="inline-block" onsubmit="return confirm('Are you sure you want to delete this record?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" title="Delete Record" class="px-2 py-1.5 rounded-md bg-rose-50 text-rose-600 hover:bg-rose-100 text-xs transition">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </form>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-12 text-slate-400">
                                <i class="fa-solid fa-file-circle-question text-4xl mb-3 text-slate-300"></i>
                                <p class="text-base font-semibold text-slate-600">No records found</p>
                                <p class="text-xs text-slate-400 mt-1">No PPM entries match the current filter criteria.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($records->hasPages())
            <div class="p-4 border-t border-slate-200">
                {{ $records->links() }}
            </div>
        @endif
    </div>

    <!-- Import Modal -->
    @can('import_ppm')
    <div id="ppmImportModal" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-xs hidden">
        <div class="bg-white rounded-2xl shadow-2xl border border-slate-200 w-full max-w-lg p-6 mx-4">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-lg bg-teal-50 text-teal-600 flex items-center justify-center text-sm font-bold">
                        <i class="fa-solid fa-file-arrow-up"></i>
                    </div>
                    <h3 class="text-base font-bold text-slate-800">Import PPM Records from CSV</h3>
                </div>
                <button type="button" onclick="document.getElementById('ppmImportModal').classList.add('hidden')" class="text-slate-400 hover:text-slate-600">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <form action="{{ route('ppm.import') }}" method="POST" enctype="multipart/form-data" class="mt-4 space-y-4">
                @csrf
                <div class="p-3 bg-blue-50 border border-blue-200 rounded-xl text-xs text-blue-900">
                    <p class="font-bold flex items-center gap-1.5 mb-1"><i class="fa-solid fa-circle-info"></i> CSV Format Note</p>
                    <p>Expected columns: <code>Customer Name, Equipment, Model, Serial No, Department, Service Type, PO No</code></p>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Select CSV File</label>
                    <input type="file" name="file" accept=".csv,text/csv" required class="block w-full text-xs text-slate-600 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-teal-50 file:text-teal-700 hover:file:bg-teal-100 cursor-pointer border border-slate-200 rounded-xl p-2 bg-slate-50">
                </div>

                <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100">
                    <button type="button" onclick="document.getElementById('ppmImportModal').classList.add('hidden')" class="px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-lg transition">
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

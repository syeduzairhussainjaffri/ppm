@extends('ppm.layout')

@php
    $serviceTypeRaw = strtolower($record->service_type ?? '');
    $recordTypeRaw = strtolower($record->record_type ?? '');

    $isInstallation = str_contains($serviceTypeRaw, 'install');
    $isPpm = str_contains($serviceTypeRaw, 'ppm') || str_contains($serviceTypeRaw, 'preventive');
    $isRepair = str_contains($serviceTypeRaw, 'repair') || str_contains($serviceTypeRaw, 'breakdown');

    if ($isPpm && !$isInstallation) {
        $reportTitle = 'PLANNED PREVENTIVE MAINTENANCE (PPM) REPORT';
        $reportSubTitle = 'Periodic Medical Equipment Inspection, Calibration & Maintenance Field Sheet';
        $headerBadge = 'PPM MAINTENANCE';
        $badgeColorClass = 'bg-emerald-50 text-emerald-700 border-emerald-300';
        $themeGradient = 'from-emerald-600 to-teal-700';
        $themeIcon = 'fa-solid fa-screwdriver-wrench';
        $dateLabel = 'Date of PPM / Maintenance:';
        $performedByLabel = 'Maintenance Performed By:';
        $engineerRoleTitle = 'Biomedical Maintenance Engineer';
    } elseif ($isRepair) {
        $reportTitle = 'BREAKDOWN SERVICE & CORRECTIVE REPAIR REPORT';
        $reportSubTitle = 'Corrective Maintenance, Fault Rectification & Operational Testing Record';
        $headerBadge = 'BREAKDOWN REPAIR';
        $badgeColorClass = 'bg-amber-50 text-amber-700 border-amber-300';
        $themeGradient = 'from-amber-600 to-orange-700';
        $themeIcon = 'fa-solid fa-triangle-exclamation';
        $dateLabel = 'Date of Service / Repair:';
        $performedByLabel = 'Repair Performed By:';
        $engineerRoleTitle = 'Biomedical Service Engineer';
    } elseif ($isInstallation && !$isPpm) {
        $reportTitle = 'EQUIPMENT INSTALLATION REPORT';
        $reportSubTitle = 'Medical Equipment Site Delivery, Installation & Handover Field Sheet';
        $headerBadge = 'NEW INSTALLATION';
        $badgeColorClass = 'bg-blue-50 text-blue-700 border-blue-300';
        $themeGradient = 'from-blue-600 to-indigo-700';
        $themeIcon = 'fa-solid fa-truck-ramp-box';
        $dateLabel = 'Date of Installation:';
        $performedByLabel = 'Installation Performed By:';
        $engineerRoleTitle = 'Installation Engineer';
    } elseif ($isInstallation && $isPpm) {
        $reportTitle = 'INSTALLATION & PREVENTIVE MAINTENANCE REPORT';
        $reportSubTitle = 'Medical Equipment Site Installation & PPM Maintenance Sheet';
        $headerBadge = 'INSTALLATION & PPM';
        $badgeColorClass = 'bg-indigo-50 text-indigo-700 border-indigo-300';
        $themeGradient = 'from-indigo-600 to-teal-700';
        $themeIcon = 'fa-solid fa-heart-pulse';
        $dateLabel = 'Date of Installation / PPM:';
        $performedByLabel = 'Installation / Service By:';
        $engineerRoleTitle = 'Biomedical Field Engineer';
    } else {
        $reportTitle = 'BIOMEDICAL EQUIPMENT SERVICE & MAINTENANCE REPORT';
        $reportSubTitle = 'Hospital Biomedical Equipment Service, Inspection & Technical Verification';
        $headerBadge = strtoupper($record->service_type ?: 'SERVICE REPORT');
        $badgeColorClass = 'bg-sky-50 text-sky-700 border-sky-300';
        $themeGradient = 'from-sky-600 to-blue-700';
        $themeIcon = 'fa-solid fa-clipboard-check';
        $dateLabel = 'Date of Service Performed:';
        $performedByLabel = 'Service Performed By:';
        $engineerRoleTitle = 'Biomedical Field Engineer';
    }

    $statusText = strtoupper($record->status ?: 'COMPLETED');
    $statusBadgeClass = match($statusText) {
        'COMPLETED' => 'bg-emerald-100 text-emerald-800 border-emerald-300',
        'PENDING'   => 'bg-amber-100 text-amber-800 border-amber-300',
        'CANCELLED' => 'bg-rose-100 text-rose-800 border-rose-300',
        default     => 'bg-slate-100 text-slate-800 border-slate-300',
    };

    $daysLeft = null;
    if ($record->next_ppm_date) {
        $daysLeft = (int) round(now()->startOfDay()->diffInDays($record->next_ppm_date->startOfDay(), false));
    }
@endphp

@section('title', $reportTitle . ' #' . str_pad($record->id, 5, '0', STR_PAD_LEFT))
@section('page_category', 'Field Service Voucher')

@section('content')
<style>
    /* ================================================================
       PRINT LAYOUT: Letterhead-Compatible A4 Page
       Header Space: 2.5 inch (top margin for pre-printed letterhead)
       Footer Space: 1.5 inch (bottom margin for pre-printed letterhead)
       Left/Right:   0.5 inch
       ================================================================ */
    @media print {
        @page {
            size: A4 portrait;
            margin: 2.5in 0.5in 1.5in 0.5in !important;
        }

        html, body {
            background: #ffffff !important;
            color: #000000 !important;
            font-size: 10px !important;
            margin: 0 !important;
            padding: 0 !important;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        .no-print, #sidebar, #mobile-header, header, footer, #ppm-toast-container, #ppmNotificationPanel, #sidebar-overlay {
            display: none !important;
        }

        #main-content, main, .voucher-wrapper {
            margin: 0 !important;
            padding: 0 !important;
            width: 100% !important;
            max-width: 100% !important;
        }

        /* Print Card: No border/shadow, fills content area between margins */
        .print-card {
            box-shadow: none !important;
            border: none !important;
            border-radius: 0 !important;
            padding: 0 !important;
            margin: 0 !important;
            width: 100% !important;
            max-width: 100% !important;
            box-sizing: border-box !important;
            display: flex !important;
            flex-direction: column !important;
            justify-content: flex-start !important;
            page-break-inside: avoid !important;
            break-inside: avoid !important;
        }

        /* Hide all screen-only header content — letterhead provides these */
        .print-card .header-section {
            display: none !important;
        }

        /* Show only the print-only reference line */
        .print-ref-line {
            display: flex !important;
            flex-direction: column !important;
            border-bottom: 2px solid #000000 !important;
            padding-bottom: 4px !important;
            margin-bottom: 5px !important;
        }

        /* Table Container stretches to fill page */
        .voucher-table-wrapper {
            flex: 1 1 auto !important;
            display: flex !important;
            flex-direction: column !important;
            margin: 0 !important;
        }

        .voucher-table {
            border: 1.5px solid #000000 !important;
            border-collapse: collapse !important;
            width: 100% !important;
            page-break-inside: avoid !important;
            break-inside: avoid !important;
        }

        .voucher-table tr {
            page-break-inside: avoid !important;
            break-inside: avoid !important;
        }

        .voucher-table th, .voucher-table td {
            padding: 4px 8px !important;
            font-size: 12px !important;
            line-height: 1.35 !important;
            border: 1px solid #000000 !important;
            color: #000000 !important;
            background-color: transparent !important;
        }

        .voucher-table th {
            font-weight: 800 !important;
            font-size: 12px !important;
        }

        .voucher-table td {
            font-size: 12.5px !important;
        }

        .voucher-table td.item-num {
            font-size: 13px !important;
            font-weight: 900 !important;
        }

        .voucher-table thead tr {
            background-color: #f1f5f9 !important;
        }

        .voucher-table thead th {
            background-color: #f1f5f9 !important;
        }

        /* Badges in print */
        .print-card .days-badge {
            background-color: #ffffff !important;
            border: 1.2px solid #000000 !important;
            color: #000000 !important;
            font-weight: 800 !important;
        }

        /* Sign Section */
        .sign-container {
            border-top: 2px solid #000000 !important;
            margin-top: 6px !important;
            padding-top: 5px !important;
            page-break-inside: avoid !important;
            break-inside: avoid !important;
        }

        .sign-box {
            border: 1.2px solid #000000 !important;
            padding: 5px 10px !important;
            background: #ffffff !important;
            color: #000000 !important;
        }

        .sign-box * {
            color: #000000 !important;
        }

        .sign-area {
            height: 80px !important;
        }

        /* Hide footer note entirely — letterhead footer handles it */
        .footer-note {
            display: none !important;
        }
    }
</style>

<div class="max-w-4xl mx-auto space-y-4 voucher-wrapper">

    <!-- Action Toolbar (hidden on print) -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 no-print bg-white p-3 sm:px-5 rounded-2xl border border-slate-200 shadow-sm">
        <div class="flex items-center space-x-2 text-xs font-semibold text-slate-600">
            <a href="{{ route('ppm.index') }}" class="inline-flex items-center text-slate-600 hover:text-indigo-600 font-bold transition">
                <i class="fa-solid fa-arrow-left mr-1.5 text-slate-400"></i> All Records
            </a>
            <span class="text-slate-300">/</span>
            <span class="text-slate-900 font-mono font-bold">Voucher #{{ str_pad($record->id, 5, '0', STR_PAD_LEFT) }}</span>
        </div>

        <div class="flex items-center flex-wrap gap-2">
            @can('print_ppm')
            <button onclick="window.print()" class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white rounded-xl text-xs font-bold shadow-sm transition active:scale-95">
                <i class="fa-solid fa-print mr-2"></i> Print Official A4 Voucher / PDF
            </button>
            @endcan

            @can('edit_ppm')
                <a href="{{ route('ppm.edit', $record) }}" class="inline-flex items-center px-3.5 py-2 bg-white border border-slate-300 text-slate-700 hover:bg-slate-50 hover:text-slate-900 rounded-xl text-xs font-bold shadow-xs transition">
                    <i class="fa-solid fa-pen-to-square mr-1.5 text-indigo-500"></i> Edit
                </a>
            @endcan

            @can('create_ppm')
            <a href="{{ route('ppm.create') }}" class="inline-flex items-center px-3 py-2 bg-emerald-50 border border-emerald-200 text-emerald-700 hover:bg-emerald-100 rounded-xl text-xs font-bold transition">
                <i class="fa-solid fa-plus mr-1"></i> New Entry
            </a>
            @endcan

            @can('delete_ppm')
                <form action="{{ route('ppm.destroy', $record) }}" method="POST" class="inline-block" onsubmit="return confirm('Are you sure you want to permanently delete this service record?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="inline-flex items-center px-3 py-2 bg-rose-50 text-rose-700 hover:bg-rose-100 border border-rose-200 rounded-xl text-xs font-bold transition">
                        <i class="fa-solid fa-trash mr-1"></i> Delete
                    </button>
                </form>
            @endcan
        </div>
    </div>

    <!-- Official Service Report Card -->
    <div class="bg-white border border-slate-200 rounded-2xl shadow-md p-5 sm:p-7 text-slate-900 print-card">
        
        <!-- Print-Only: PPM Report Title + Reference Line (visible ONLY in print) -->
        <div class="print-ref-line hidden flex-col text-slate-900 pb-1.5 mb-2 border-b-2 border-slate-900">
            <div class="text-center mb-1" style="font-size: 20px; font-weight: 900; letter-spacing: 2px; text-transform: uppercase;">PLANNED PREVENTIVE MAINTENANCE (PPM) REPORT</div>
            <div class="flex items-center justify-between text-xs font-bold">
                <span class="font-mono text-sm font-black">#PPM-{{ str_pad($record->id, 5, '0', STR_PAD_LEFT) }}</span>
                <span class="font-semibold">Date: {{ optional($record->entry_date)->format('d-M-Y') ?: now()->format('d-M-Y') }}</span>
            </div>
        </div>

        <!-- Screen-Only Header Section (hidden on print — letterhead provides all branding) -->
        <div class="header-section border-b-2 border-slate-200 pb-3 mb-3 flex flex-row items-start justify-between gap-3">
            
            <div class="space-y-1 flex-1">
                <!-- Branding Line -->
                <div class="flex items-center space-x-2.5">
                    <div class="header-brand-icon w-8 h-8 rounded-lg bg-gradient-to-br {{ $themeGradient }} text-white flex items-center justify-center text-sm font-bold shadow-xs shrink-0">
                        <i class="{{ $themeIcon }}"></i>
                    </div>
                    <div>
                        <div class="text-[10px] font-extrabold uppercase tracking-widest text-slate-500 leading-tight">BioMed CRM &amp; Field Operations</div>
                        <div class="text-xs font-bold text-slate-800 leading-tight">Hospital Equipment Engineering Division</div>
                    </div>
                </div>

                <!-- Main Dynamic Title -->
                <h1 class="text-base sm:text-lg md:text-xl font-black tracking-tight text-slate-900 uppercase mt-1 leading-snug">
                    {{ $reportTitle }}
                </h1>
                <p class="text-[11px] text-slate-600 font-medium">
                    {{ $reportSubTitle }}
                </p>

                <!-- Badges -->
                <div class="flex items-center gap-2 pt-0.5">
                    <span class="badge-box text-[10px] font-extrabold uppercase tracking-wider px-2.5 py-0.5 rounded-md border {{ $badgeColorClass }}">
                        {{ $headerBadge }}
                    </span>
                    <span class="badge-box text-[10px] font-semibold text-slate-700 bg-slate-100 border border-slate-200 px-2 py-0.5 rounded-md">
                        TYPE: {{ strtoupper($record->record_type ?: 'FIELD SERVICE') }}
                    </span>
                </div>
            </div>

            <!-- Right Official Stamp / Meta Box -->
            <div class="shrink-0 text-right">
                <div class="ref-box bg-slate-50 px-3 py-2 rounded-xl border border-slate-200 text-right min-w-[160px] shadow-2xs">
                    <div class="text-[9px] uppercase font-bold text-slate-500 tracking-wider">Document Reference</div>
                    <div class="text-base sm:text-lg font-mono font-black text-slate-900 leading-tight">
                        #PPM-{{ str_pad($record->id, 5, '0', STR_PAD_LEFT) }}
                    </div>

                    <div class="mt-1 flex items-center justify-end space-x-1">
                        <span class="status-badge inline-flex items-center px-2 py-0.5 rounded text-[9px] font-extrabold border {{ $statusBadgeClass }}">
                            STATUS: {{ $statusText }}
                        </span>
                    </div>

                    <div class="text-[10px] text-slate-600 font-medium mt-1 pt-1 border-t border-slate-200">
                        Date: <strong class="text-slate-900">{{ optional($record->entry_date)->format('d-M-Y') ?: now()->format('d-M-Y') }}</strong>
                    </div>
                </div>
            </div>

        </div>

        <!-- Checklist Table Wrapper -->
        <div class="voucher-table-wrapper">
            <table class="w-full border-collapse border border-slate-300 text-sm voucher-table">
                <thead>
                    <tr class="bg-slate-100 text-slate-900 text-xs uppercase tracking-wider font-extrabold border-b border-slate-300">
                        <th class="w-12 text-center py-2 px-2 border-r border-slate-300">#</th>
                        <th class="w-60 sm:w-72 text-left py-2 px-3 border-r border-slate-300">Checklist</th>
                        <th class="text-left py-2 px-3">Description</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    <!-- Row 1: Document / Entry Date -->
                    <tr class="hover:bg-slate-50/70 transition-colors">
                        <td class="item-num text-center font-bold text-slate-700 bg-slate-50/80 border-r border-slate-300 py-2 px-2">1</td>
                        <th class="text-left py-2 px-3 border-r border-slate-300 font-bold text-slate-800">Document / Entry Date:</th>
                        <td class="py-2 px-3 font-semibold text-slate-900">
                            {{ optional($record->entry_date)->format('d-M-Y') ?: 'N/A' }}
                        </td>
                    </tr>

                    <!-- Row 2: Purchase Order # -->
                    <tr class="bg-slate-50/40 hover:bg-slate-50/70 transition-colors">
                        <td class="item-num text-center font-bold text-slate-700 bg-slate-50/80 border-r border-slate-300 py-2 px-2">2</td>
                        <th class="text-left py-2 px-3 border-r border-slate-300 font-bold text-slate-800">Purchase Order #:</th>
                        <td class="py-2 px-3 font-mono font-bold text-slate-900">
                            {{ $record->purchase_order_no ?: 'N/A' }}
                        </td>
                    </tr>

                    <!-- Row 3: Supply Against -->
                    <tr class="hover:bg-slate-50/70 transition-colors">
                        <td class="item-num text-center font-bold text-slate-700 bg-slate-50/80 border-r border-slate-300 py-2 px-2">3</td>
                        <th class="text-left py-2 px-3 border-r border-slate-300 font-bold text-slate-800">Supply Against:</th>
                        <td class="py-2 px-3 font-semibold text-slate-800">
                            {{ $record->supply_against ?: 'N/A' }}
                        </td>
                    </tr>

                    <!-- Row 4: Customer Name -->
                    <tr class="bg-slate-50/40 hover:bg-slate-50/70 transition-colors">
                        <td class="item-num text-center font-bold text-slate-700 bg-slate-50/80 border-r border-slate-300 py-2 px-2">4</td>
                        <th class="text-left py-2 px-3 border-r border-slate-300 font-bold text-slate-800">Customer Name:</th>
                        <td class="py-2 px-3 font-bold text-slate-900">
                            {{ $record->customer_name }}
                        </td>
                    </tr>

                    <!-- Row 5: Product / Equipment Name -->
                    <tr class="hover:bg-slate-50/70 transition-colors">
                        <td class="item-num text-center font-bold text-slate-700 bg-slate-50/80 border-r border-slate-300 py-2 px-2">5</td>
                        <th class="text-left py-2 px-3 border-r border-slate-300 font-bold text-slate-800">Product / Equipment:</th>
                        <td class="py-2 px-3 font-bold text-slate-900">
                            {{ $record->product_description ?: ($record->equipment_name ?: 'N/A') }}
                        </td>
                    </tr>

                    <!-- Row 6: Model / Make -->
                    <tr class="bg-slate-50/40 hover:bg-slate-50/70 transition-colors">
                        <td class="item-num text-center font-bold text-slate-700 bg-slate-50/80 border-r border-slate-300 py-2 px-2">6</td>
                        <th class="text-left py-2 px-3 border-r border-slate-300 font-bold text-slate-800">Model / Make:</th>
                        <td class="py-2 px-3 font-semibold text-slate-800">
                            {{ $record->model_make ?: 'Standard' }}
                        </td>
                    </tr>

                    <!-- Row 7: Serial Number (S/N) -->
                    <tr class="hover:bg-slate-50/70 transition-colors">
                        <td class="item-num text-center font-bold text-slate-700 bg-slate-50/80 border-r border-slate-300 py-2 px-2">7</td>
                        <th class="text-left py-2 px-3 border-r border-slate-300 font-bold text-slate-800">Serial Number (S/N):</th>
                        <td class="py-2 px-3 font-mono font-bold text-indigo-700">
                            {{ $record->serial_no ?: 'N/A' }}
                        </td>
                    </tr>

                    <!-- Row 8: Department / Location / Ward -->
                    <tr class="bg-slate-50/40 hover:bg-slate-50/70 transition-colors">
                        <td class="item-num text-center font-bold text-slate-700 bg-slate-50/80 border-r border-slate-300 py-2 px-2">8</td>
                        <th class="text-left py-2 px-3 border-r border-slate-300 font-bold text-slate-800">Department / Ward:</th>
                        <td class="py-2 px-3 font-semibold text-slate-900">
                            {{ $record->department ?: 'General' }}
                        </td>
                    </tr>

                    <!-- Row 9: Contact Person -->
                    <tr class="hover:bg-slate-50/70 transition-colors">
                        <td class="item-num text-center font-bold text-slate-700 bg-slate-50/80 border-r border-slate-300 py-2 px-2">9</td>
                        <th class="text-left py-2 px-3 border-r border-slate-300 font-bold text-slate-800">Contact Person:</th>
                        <td class="py-2 px-3 font-semibold text-slate-900">
                            {{ $record->contact_person ?: 'N/A' }}
                        </td>
                    </tr>

                    <!-- Row 10: Contact Telephone -->
                    <tr class="bg-slate-50/40 hover:bg-slate-50/70 transition-colors">
                        <td class="item-num text-center font-bold text-slate-700 bg-slate-50/80 border-r border-slate-300 py-2 px-2">10</td>
                        <th class="text-left py-2 px-3 border-r border-slate-300 font-bold text-slate-800">Contact Telephone:</th>
                        <td class="py-2 px-3 font-mono font-bold text-slate-900">
                            {{ $record->contact_no ?: 'N/A' }}
                        </td>
                    </tr>

                    <!-- Row 11: Date of Service / Installation / PPM -->
                    <tr class="hover:bg-slate-50/70 transition-colors">
                        <td class="item-num text-center font-bold text-slate-700 bg-slate-50/80 border-r border-slate-300 py-2 px-2">11</td>
                        <th class="text-left py-2 px-3 border-r border-slate-300 font-bold text-slate-800">{{ $dateLabel }}</th>
                        <td class="py-2 px-3 font-bold text-slate-900">
                            @if($record->installation_date)
                                {{ $record->installation_date->format('d-M-Y') }}
                            @elseif($record->entry_date)
                                {{ $record->entry_date->format('d-M-Y') }}
                            @else
                                <span class="text-slate-400">N/A</span>
                            @endif
                        </td>
                    </tr>

                    <!-- Row 12: Performed By -->
                    <tr class="bg-slate-50/40 hover:bg-slate-50/70 transition-colors">
                        <td class="item-num text-center font-bold text-slate-700 bg-slate-50/80 border-r border-slate-300 py-2 px-2">12</td>
                        <th class="text-left py-2 px-3 border-r border-slate-300 font-bold text-slate-800">{{ $performedByLabel }}</th>
                        <td class="py-2 px-3 font-bold text-slate-900">
                            {{ $record->performed_by ?: 'N/A' }}
                        </td>
                    </tr>

                    <!-- Row 13: Activity / Service Type -->
                    <tr class="hover:bg-slate-50/70 transition-colors">
                        <td class="item-num text-center font-bold text-slate-700 bg-slate-50/80 border-r border-slate-300 py-2 px-2">13</td>
                        <th class="text-left py-2 px-3 border-r border-slate-300 font-bold text-slate-800">Activity / Service Type:</th>
                        <td class="py-2 px-3 font-extrabold text-slate-900">
                            {{ strtoupper($record->service_type ?: 'GENERAL SERVICE') }}
                        </td>
                    </tr>

                    <!-- Row 14: Scope of Work Carried Out -->
                    <tr class="bg-slate-50/40 hover:bg-slate-50/70 transition-colors">
                        <td class="item-num text-center font-bold text-slate-700 bg-slate-50/80 border-r border-slate-300 py-2 px-2 align-top">14</td>
                        <th class="text-left py-2 px-3 border-r border-slate-300 font-bold text-slate-800 align-top">Scope of Work Carried Out:</th>
                        <td class="py-2 px-3 text-slate-900 leading-relaxed font-semibold">
                            {{ $record->scope_of_work ?: 'Standard preventive maintenance inspection and verification completed.' }}
                        </td>
                    </tr>

                    <!-- Row 15: Next PPM Due Date -->
                    <tr class="hover:bg-slate-50/70 transition-colors">
                        <td class="item-num text-center font-bold text-slate-700 bg-slate-50/80 border-r border-slate-300 py-2 px-2">15</td>
                        <th class="text-left py-2 px-3 border-r border-slate-300 font-bold text-slate-800">Next PPM Due Date:</th>
                        <td class="py-2 px-3 font-bold text-slate-900">
                            @if($record->next_ppm_date)
                                <span class="font-bold text-slate-900">{{ $record->next_ppm_date->format('d-M-Y') }}</span>
                                @if($daysLeft !== null)
                                    @if($daysLeft === 0)
                                        <span class="days-badge ml-2 inline-flex items-center px-1.5 py-0.2 rounded text-xs font-bold bg-amber-100 text-amber-800 border border-amber-300">[DUE TODAY]</span>
                                    @elseif($daysLeft < 0)
                                        <span class="days-badge ml-2 inline-flex items-center px-1.5 py-0.2 rounded text-xs font-bold bg-rose-100 text-rose-800 border border-rose-300">[OVERDUE]</span>
                                    @else
                                        <span class="days-badge ml-2 inline-flex items-center px-1.5 py-0.2 rounded text-xs font-semibold bg-emerald-100 text-emerald-800 border border-emerald-200">(In {{ $daysLeft }} days)</span>
                                    @endif
                                @endif
                            @else
                                <span class="text-slate-400">N/A</span>
                            @endif
                        </td>
                    </tr>

                    <!-- Row 16: Technical Remarks (if present) -->
                    @if($record->remarks)
                    <tr class="bg-amber-50/30 hover:bg-amber-50/60 transition-colors">
                        <td class="item-num text-center font-bold text-slate-600 bg-slate-50/80 border-r border-slate-300 py-2 px-2 align-top">&bull;</td>
                        <th class="text-left py-2 px-3 border-r border-slate-300 font-bold text-slate-800 align-top">Technical Remarks:</th>
                        <td class="py-2 px-3 text-slate-800 font-medium italic">
                            {{ $record->remarks }}
                        </td>
                    </tr>
                    @endif
                </tbody>
            </table>
        </div>



        <!-- Sign-off & Verification Section -->
        <div class="sign-container mt-3 pt-2.5 border-t-2 border-slate-200 grid grid-cols-2 gap-4 text-center text-xs text-slate-700">
            
            <!-- Left: Engineer Signature -->
            <div class="sign-box border border-slate-200 rounded-xl p-2.5 bg-slate-50/60 shadow-2xs">
                <div class="sign-area h-24 flex flex-col justify-end items-center pb-1">
                    <p class="font-mono text-xs font-bold text-slate-900">{{ $record->performed_by ?: 'Engineer Signature' }}</p>
                </div>
                <div class="border-t border-slate-300 pt-1">
                    <p class="font-black uppercase tracking-wider text-slate-800 text-[10px]">{{ $engineerRoleTitle }}</p>
                    <p class="text-[9px] text-slate-500">Service Performed By (Sign &amp; Date)</p>
                </div>
            </div>

            <!-- Right: Hospital / Biomedical HOD -->
            <div class="sign-box border border-slate-200 rounded-xl p-2.5 bg-slate-50/60 shadow-2xs">
                <div class="sign-area h-24 flex flex-col justify-end items-center pb-1">
                    <p class="text-slate-400 italic text-[9px]">Official Stamp &amp; Signature</p>
                </div>
                <div class="border-t border-slate-300 pt-1">
                    <p class="font-black uppercase tracking-wider text-slate-800 text-[10px]">Hospital</p>
                    <p class="text-[9px] text-slate-500">Authorized Signature &amp; Official Stamp</p>
                </div>
            </div>

        </div>

        <!-- Footer Note (screen only — hidden in print) -->
        <div class="footer-note mt-2 pt-1 border-t border-slate-200 text-center text-[8.5px] text-slate-500 font-medium flex items-center justify-between">
            <span>BioMed CRM &bull; Planned Preventive Maintenance Operations</span>
            <span>Ref: #PPM-{{ str_pad($record->id, 5, '0', STR_PAD_LEFT) }} &bull; Generated: {{ now()->format('d-M-Y H:i') }}</span>
        </div>

    </div>
</div>
@endsection

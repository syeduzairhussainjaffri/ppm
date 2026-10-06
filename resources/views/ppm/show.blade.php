@extends('ppm.layout')

@section('title', 'Service Voucher - ' . ($record->purchase_order_no ?: 'PO Pending'))

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <!-- Action Toolbar (hidden on print) -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 no-print">
        <a href="{{ route('ppm.index') }}" class="inline-flex items-center text-sm font-semibold text-slate-600 hover:text-slate-900 transition">
            <i class="fa-solid fa-arrow-left mr-2"></i> Back to All Records
        </a>
        <div class="flex items-center gap-3">
            <button onclick="window.print()" class="inline-flex items-center px-4 py-2 bg-slate-900 hover:bg-slate-800 text-white rounded-lg text-sm font-semibold shadow-sm transition">
                <i class="fa-solid fa-print mr-2"></i> Print Voucher / PDF
            </button>
            <a href="{{ route('ppm.edit', $record) }}" class="inline-flex items-center px-4 py-2 bg-white border border-slate-300 text-slate-700 hover:bg-slate-50 rounded-lg text-sm font-semibold shadow-sm transition">
                <i class="fa-solid fa-pen-to-square mr-2"></i> Edit
            </a>
            <form action="{{ route('ppm.destroy', $record) }}" method="POST" class="inline-block" onsubmit="return confirm('Delete this record?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="inline-flex items-center px-3 py-2 bg-rose-50 text-rose-600 hover:bg-rose-100 rounded-lg text-sm font-semibold transition">
                    <i class="fa-solid fa-trash mr-1.5"></i> Delete
                </button>
            </form>
        </div>
    </div>

    <!-- Official Service Report Card -->
    <div class="bg-white border-2 border-slate-800 rounded-xl shadow-lg p-6 sm:p-10 text-slate-900 print:shadow-none print:border-black print:p-6 print-card">
        
        <!-- Header -->
        <div class="border-b-2 border-slate-800 pb-5 mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <span class="text-xs font-bold uppercase tracking-widest text-teal-700 bg-teal-50 px-2.5 py-1 rounded border border-teal-200">
                    {{ $record->record_type }}
                </span>
                <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight mt-2 text-slate-950">
                    INSTALLATION &amp; PPM SERVICE REPORT
                </h1>
                <p class="text-xs sm:text-sm text-slate-500 mt-0.5">Biomedical Engineering &amp; Equipment Maintenance Field Sheet</p>
            </div>
            <div class="sm:text-right border-l sm:border-l-0 sm:pl-0 pl-4 border-slate-300">
                <div class="text-xs uppercase font-semibold text-slate-500">Record ID</div>
                <div class="text-lg font-mono font-bold text-slate-900">#{{ str_pad($record->id, 5, '0', STR_PAD_LEFT) }}</div>
                <div class="text-xs text-slate-500 mt-1">Generated: {{ now()->format('d-M-Y H:i') }}</div>
            </div>
        </div>

        <!-- 15 Numbered Checklist Items (Exactly matching user's paper sheet format) -->
        <div class="border border-slate-300 rounded-lg overflow-hidden divide-y divide-slate-200 text-sm">
            
            <div class="grid grid-cols-1 md:grid-cols-12 bg-slate-50">
                <div class="p-3 font-semibold text-slate-600 md:col-span-1 border-r border-slate-200 text-center">1</div>
                <div class="p-3 font-semibold text-slate-700 md:col-span-4 border-r border-slate-200">Date:</div>
                <div class="p-3 font-bold text-slate-950 md:col-span-7">
                    {{ optional($record->entry_date)->format('d-M-Y') ?: 'N/A' }}
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-12">
                <div class="p-3 font-semibold text-slate-600 md:col-span-1 border-r border-slate-200 text-center">2</div>
                <div class="p-3 font-semibold text-slate-700 md:col-span-4 border-r border-slate-200">Purchase Order #:</div>
                <div class="p-3 font-mono font-bold text-teal-800 md:col-span-7">
                    {{ $record->purchase_order_no ?: 'N/A' }}
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-12 bg-slate-50">
                <div class="p-3 font-semibold text-slate-600 md:col-span-1 border-r border-slate-200 text-center">3</div>
                <div class="p-3 font-semibold text-slate-700 md:col-span-4 border-r border-slate-200">Supply Against:</div>
                <div class="p-3 font-medium text-slate-900 md:col-span-7">
                    {{ $record->supply_against ?: 'N/A' }}
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-12">
                <div class="p-3 font-semibold text-slate-600 md:col-span-1 border-r border-slate-200 text-center">4</div>
                <div class="p-3 font-semibold text-slate-700 md:col-span-4 border-r border-slate-200">Customer Name:</div>
                <div class="p-3 font-bold text-slate-950 md:col-span-7 text-base">
                    {{ $record->customer_name }}
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-12 bg-slate-50">
                <div class="p-3 font-semibold text-slate-600 md:col-span-1 border-r border-slate-200 text-center">5</div>
                <div class="p-3 font-semibold text-slate-700 md:col-span-4 border-r border-slate-200">Description of Product:</div>
                <div class="p-3 font-semibold text-slate-900 md:col-span-7">
                    {{ $record->product_description ?: ($record->equipment_name ?: 'N/A') }}
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-12">
                <div class="p-3 font-semibold text-slate-600 md:col-span-1 border-r border-slate-200 text-center">6</div>
                <div class="p-3 font-semibold text-slate-700 md:col-span-4 border-r border-slate-200">Model / Make:</div>
                <div class="p-3 font-semibold text-slate-900 md:col-span-7">
                    {{ $record->model_make ?: 'N/A' }}
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-12 bg-slate-50">
                <div class="p-3 font-semibold text-slate-600 md:col-span-1 border-r border-slate-200 text-center">7</div>
                <div class="p-3 font-semibold text-slate-700 md:col-span-4 border-r border-slate-200">Serial No:</div>
                <div class="p-3 font-mono font-bold text-slate-950 md:col-span-7">
                    <span class="bg-slate-200 px-2 py-0.5 rounded">{{ $record->serial_no ?: 'N/A' }}</span>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-12">
                <div class="p-3 font-semibold text-slate-600 md:col-span-1 border-r border-slate-200 text-center">8</div>
                <div class="p-3 font-semibold text-slate-700 md:col-span-4 border-r border-slate-200">Department:</div>
                <div class="p-3 font-medium text-slate-900 md:col-span-7">
                    {{ $record->department ?: 'N/A' }}
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-12 bg-slate-50">
                <div class="p-3 font-semibold text-slate-600 md:col-span-1 border-r border-slate-200 text-center">9</div>
                <div class="p-3 font-semibold text-slate-700 md:col-span-4 border-r border-slate-200">Contact Person / Supervisor:</div>
                <div class="p-3 font-medium text-slate-900 md:col-span-7">
                    {{ $record->contact_person ?: 'N/A' }}
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-12">
                <div class="p-3 font-semibold text-slate-600 md:col-span-1 border-r border-slate-200 text-center">10</div>
                <div class="p-3 font-semibold text-slate-700 md:col-span-4 border-r border-slate-200">Contact No:</div>
                <div class="p-3 font-mono font-bold text-slate-900 md:col-span-7">
                    {{ $record->contact_no ?: 'N/A' }}
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-12 bg-slate-50">
                <div class="p-3 font-semibold text-slate-600 md:col-span-1 border-r border-slate-200 text-center">11</div>
                <div class="p-3 font-semibold text-slate-700 md:col-span-4 border-r border-slate-200">Date of Installation:</div>
                <div class="p-3 font-bold text-slate-950 md:col-span-7">
                    {{ optional($record->installation_date)->format('d-M-Y') ?: 'N/A' }}
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-12">
                <div class="p-3 font-semibold text-slate-600 md:col-span-1 border-r border-slate-200 text-center">12</div>
                <div class="p-3 font-semibold text-slate-700 md:col-span-4 border-r border-slate-200">Installation Performed By:</div>
                <div class="p-3 font-bold text-teal-900 md:col-span-7">
                    {{ $record->performed_by ?: 'N/A' }}
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-12 bg-slate-50">
                <div class="p-3 font-semibold text-slate-600 md:col-span-1 border-r border-slate-200 text-center">13</div>
                <div class="p-3 font-semibold text-slate-700 md:col-span-4 border-r border-slate-200">New Install / PPM:</div>
                <div class="p-3 font-semibold text-slate-900 md:col-span-7">
                    <span class="inline-block px-2.5 py-0.5 rounded text-xs font-bold uppercase bg-slate-200 text-slate-800">
                        {{ $record->service_type }}
                    </span>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-12">
                <div class="p-3 font-semibold text-slate-600 md:col-span-1 border-r border-slate-200 text-center">14</div>
                <div class="p-3 font-semibold text-slate-700 md:col-span-4 border-r border-slate-200">Scope of Work:</div>
                <div class="p-3 font-medium text-slate-900 md:col-span-7 leading-relaxed">
                    {{ $record->scope_of_work ?: 'N/A' }}
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-12 bg-amber-50/60">
                <div class="p-3 font-semibold text-amber-800 md:col-span-1 border-r border-slate-200 text-center">15</div>
                <div class="p-3 font-semibold text-amber-900 md:col-span-4 border-r border-slate-200">Next PPM Date:</div>
                <div class="p-3 font-bold text-amber-900 md:col-span-7">
                    @if($record->next_ppm_date)
                        <span class="inline-flex items-center px-3 py-1 rounded bg-amber-100 text-amber-900 font-bold border border-amber-300">
                            <i class="fa-solid fa-clock-rotate-left mr-2"></i> {{ $record->next_ppm_date->format('d-M-Y') }}
                        </span>
                    @else
                        <span>N/A</span>
                    @endif
                </div>
            </div>

        </div>

        @if($record->remarks)
            <div class="mt-4 p-4 rounded-lg bg-slate-50 border border-slate-200 text-sm">
                <span class="font-bold text-slate-700">Remarks / Notes:</span>
                <p class="text-slate-600 mt-1">{{ $record->remarks }}</p>
            </div>
        @endif

        <!-- Sign-off & Verification Section -->
        <div class="mt-12 pt-8 border-t-2 border-dashed border-slate-300 grid grid-cols-1 sm:grid-cols-2 gap-8 text-center text-xs text-slate-600">
            <div>
                <div class="h-16 flex items-end justify-center">
                    <p class="font-mono text-sm font-semibold text-slate-800">{{ $record->performed_by }}</p>
                </div>
                <div class="border-t border-slate-400 pt-2">
                    <p class="font-bold uppercase text-slate-800">Installation Performed By (Engineer)</p>
                    <p class="text-slate-500">Sign &amp; Date</p>
                </div>
            </div>

            <div>
                <div class="h-16 flex items-end justify-center">
                    <p class="text-slate-400 italic">Signature &amp; Official Stamp</p>
                </div>
                <div class="border-t border-slate-400 pt-2">
                    <p class="font-bold uppercase text-slate-800">Customer / Biomedical HOD Verification</p>
                    <p class="text-slate-500">Authorized Signature &amp; Stamp</p>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $reportTitle }} - BioMed CRM Print Report</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Font Awesome CDN -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        @media print {
            .no-print { display: none !important; }
            body { background: white !important; color: black !important; padding: 0 !important; }
            .print-page { border: none !important; box-shadow: none !important; margin: 0 !important; width: 100% !important; }
            table { page-break-inside: auto; }
            tr { page-break-inside: avoid; page-break-after: auto; }
        }
    </style>
</head>
<body class="bg-slate-100 text-slate-900 font-sans p-4 sm:p-8">

    <!-- Top Action Bar for Screen Viewing -->
    <div class="max-w-5xl mx-auto mb-4 flex items-center justify-between no-print">
        <a href="{{ url()->previous() }}" class="inline-flex items-center text-xs font-bold text-slate-600 hover:text-slate-900 bg-white px-3 py-2 rounded-lg border border-slate-300">
            <i class="fa-solid fa-arrow-left mr-1.5"></i> Back
        </a>
        <div class="flex items-center space-x-2">
            <button onclick="window.print()" class="inline-flex items-center px-4 py-2 bg-teal-600 hover:bg-teal-700 text-white rounded-lg text-xs font-bold shadow-md transition">
                <i class="fa-solid fa-print mr-2"></i> Print Report / Save PDF
            </button>
        </div>
    </div>

    <!-- Main Printable Sheet Container -->
    <div class="max-w-5xl mx-auto bg-white p-8 border border-slate-300 shadow-lg rounded-xl print-page">
        
        <!-- Official Hospital / Company Header -->
        <div class="border-b-2 border-slate-900 pb-5 mb-6 flex items-start justify-between">
            <div>
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-slate-900 text-white rounded-lg flex items-center justify-center font-bold text-lg">
                        <i class="fa-solid fa-notes-medical"></i>
                    </div>
                    <div>
                        <h1 class="text-xl font-black uppercase text-slate-900 tracking-wider">BioMed Equipment CRM &amp; PPM Operations</h1>
                        <p class="text-xs text-slate-600 font-semibold">Planned Preventive Maintenance &amp; Field Asset Audit Report</p>
                    </div>
                </div>
                <div class="mt-3 text-xs text-slate-600 space-y-0.5 font-medium">
                    <p><i class="fa-solid fa-location-dot mr-1 text-slate-400"></i> Field Operations Head Office: Karachi, Sindh, Pakistan</p>
                    <p><i class="fa-solid fa-globe mr-1 text-slate-400"></i> System Portal: http://127.0.0.1:8010/ppm</p>
                </div>
            </div>

            <div class="text-right text-xs">
                <div class="bg-slate-100 p-3 rounded-lg border border-slate-300 text-left font-mono">
                    <p class="font-bold text-slate-800 uppercase text-[11px] mb-1 border-b border-slate-300 pb-1">{{ $reportTitle }}</p>
                    <p><span class="text-slate-500">Date Generated:</span> <strong>{{ now()->format('d-M-Y h:i A') }}</strong></p>
                    <p><span class="text-slate-500">Total Records:</span> <strong>{{ $stats['total'] }}</strong></p>
                </div>
            </div>
        </div>

        <!-- Filter Criteria Applied Banner -->
        @if(request()->anyFilled(['search', 'customer_id', 'product_id', 'department', 'service_type', 'status', 'date_from', 'date_to']))
            <div class="mb-5 p-3 bg-slate-50 rounded-lg border border-slate-200 text-xs">
                <span class="font-bold text-slate-700 uppercase tracking-wider text-[10px] mr-2">Applied Report Filters:</span>
                @if(request('search')) <span class="inline-block bg-white px-2 py-0.5 rounded border text-[11px] mr-1">Query: {{ request('search') }}</span> @endif
                @if(request('department')) <span class="inline-block bg-white px-2 py-0.5 rounded border text-[11px] mr-1">Dept: {{ request('department') }}</span> @endif
                @if(request('status')) <span class="inline-block bg-white px-2 py-0.5 rounded border text-[11px] mr-1">Status: {{ ucfirst(request('status')) }}</span> @endif
                @if(request('service_type')) <span class="inline-block bg-white px-2 py-0.5 rounded border text-[11px] mr-1">Type: {{ request('service_type') }}</span> @endif
                @if(request('date_from')) <span class="inline-block bg-white px-2 py-0.5 rounded border text-[11px] mr-1">From: {{ request('date_from') }}</span> @endif
                @if(request('date_to')) <span class="inline-block bg-white px-2 py-0.5 rounded border text-[11px] mr-1">To: {{ request('date_to') }}</span> @endif
            </div>
        @endif

        <!-- Summary KPIs Line -->
        <div class="grid grid-cols-4 gap-3 mb-6 text-center text-xs">
            <div class="p-2.5 bg-slate-50 border border-slate-300 rounded-lg">
                <span class="text-[10px] text-slate-500 font-bold block uppercase">Total Items</span>
                <span class="text-base font-black text-slate-900">{{ $stats['total'] }}</span>
            </div>
            <div class="p-2.5 bg-amber-50 border border-amber-300 rounded-lg">
                <span class="text-[10px] text-amber-800 font-bold block uppercase">PPM Due Today</span>
                <span class="text-base font-black text-amber-900">{{ $stats['due_today'] }}</span>
            </div>
            <div class="p-2.5 bg-rose-50 border border-rose-300 rounded-lg">
                <span class="text-[10px] text-rose-800 font-bold block uppercase">Overdue PPM</span>
                <span class="text-base font-black text-rose-900">{{ $stats['overdue'] }}</span>
            </div>
            <div class="p-2.5 bg-emerald-50 border border-emerald-300 rounded-lg">
                <span class="text-[10px] text-emerald-800 font-bold block uppercase">Completed Services</span>
                <span class="text-base font-black text-emerald-900">{{ $stats['completed'] }}</span>
            </div>
        </div>

        <!-- Printable Data Table -->
        <table class="w-full text-left text-xs border-collapse border border-slate-900 mb-8">
            <thead>
                <tr class="bg-slate-900 text-white font-bold uppercase text-[10px] tracking-wider">
                    <th class="border border-slate-900 p-2 text-center w-10">#</th>
                    <th class="border border-slate-900 p-2">Customer / Hospital</th>
                    <th class="border border-slate-900 p-2">Equipment Description</th>
                    <th class="border border-slate-900 p-2">Model / Make</th>
                    <th class="border border-slate-900 p-2">Serial No</th>
                    <th class="border border-slate-900 p-2">PO #</th>
                    <th class="border border-slate-900 p-2 text-center">Install Date</th>
                    <th class="border border-slate-900 p-2 text-center">Next PPM</th>
                    <th class="border border-slate-900 p-2 text-center">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-300 text-[11px]">
                @forelse($records as $index => $row)
                    <tr class="{{ $index % 2 === 0 ? 'bg-white' : 'bg-slate-50' }}">
                        <td class="border border-slate-400 p-2 text-center font-bold">{{ $index + 1 }}</td>
                        <td class="border border-slate-400 p-2 font-bold">{{ $row->customer_name }}</td>
                        <td class="border border-slate-400 p-2">{{ $row->equipment_name ?: $row->product_description }}</td>
                        <td class="border border-slate-400 p-2">{{ $row->model_make ?: 'N/A' }}</td>
                        <td class="border border-slate-400 p-2 font-mono font-bold">{{ $row->serial_no ?: 'N/A' }}</td>
                        <td class="border border-slate-400 p-2">{{ $row->purchase_order_no ?: 'N/A' }}</td>
                        <td class="border border-slate-400 p-2 text-center">{{ optional($row->installation_date)->format('d-M-Y') ?: '-' }}</td>
                        <td class="border border-slate-400 p-2 text-center font-bold">
                            {{ optional($row->next_ppm_date)->format('d-M-Y') ?: '-' }}
                        </td>
                        <td class="border border-slate-400 p-2 text-center font-bold">
                            {{ $row->status ?: 'Pending' }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="border border-slate-400 p-6 text-center text-slate-500 font-semibold">No equipment records match the criteria.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <!-- Printable Formal Signatures Footer -->
        <div class="mt-12 pt-6 border-t-2 border-slate-400 grid grid-cols-3 gap-6 text-center text-xs">
            <div>
                <div class="h-12 border-b border-slate-400 border-dashed mb-2"></div>
                <p class="font-bold text-slate-800">Prepared By (Field Officer)</p>
                <p class="text-[10px] text-slate-500">Name &amp; Signature</p>
            </div>
            <div>
                <div class="h-12 border-b border-slate-400 border-dashed mb-2"></div>
                <p class="font-bold text-slate-800">Checked By (Biomedical Engr.)</p>
                <p class="text-[10px] text-slate-500">Stamp &amp; Signature</p>
            </div>
            <div>
                <div class="h-12 border-b border-slate-400 border-dashed mb-2"></div>
                <p class="font-bold text-slate-800">Approved By (Hospital Manager)</p>
                <p class="text-[10px] text-slate-500">Official Sign &amp; Date</p>
            </div>
        </div>

    </div>

</body>
</html>

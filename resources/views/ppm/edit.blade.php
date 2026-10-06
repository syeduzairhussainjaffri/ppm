@extends('ppm.layout')

@section('title', 'Edit Record - ' . $record->customer_name)
@section('page_category', 'Edit Service Record')

@section('content')
<div class="max-w-5xl mx-auto space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Edit Service Record #{{ str_pad($record->id, 5, '0', STR_PAD_LEFT) }}</h1>
            <p class="text-sm text-slate-500 mt-0.5">Updating installation and PPM record for: <strong class="text-slate-800">{{ $record->customer_name }}</strong></p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('ppm.show', $record) }}" class="inline-flex items-center px-4 py-2 border border-slate-300 rounded-lg text-sm font-semibold text-slate-700 hover:bg-slate-50 transition shadow-xs">
                <i class="fa-solid fa-eye mr-1.5 text-teal-600"></i> View Voucher
            </a>
            <a href="{{ route('ppm.index') }}" class="inline-flex items-center px-4 py-2 border border-slate-300 rounded-lg text-sm font-semibold text-slate-700 hover:bg-slate-50 transition shadow-xs">
                <i class="fa-solid fa-arrow-left mr-1.5"></i> Back
            </a>
        </div>
    </div>

    @if ($errors->any())
        <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-sm shadow-xs">
            <p class="font-bold mb-1"><i class="fa-solid fa-circle-exclamation mr-1.5"></i> Please correct the following errors:</p>
            <ul class="list-disc list-inside space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('ppm.update', $record) }}" method="POST" id="ppmEditForm" class="bg-white border border-slate-200 rounded-2xl shadow-sm p-6 sm:p-8 space-y-8">
        @csrf
        @method('PUT')

        <!-- Section 1: Customer Details -->
        <div class="bg-teal-50/40 p-5 rounded-xl border border-teal-100/80">
            <h3 class="text-sm font-bold uppercase tracking-wider text-teal-900 border-b border-teal-200/60 pb-3 mb-4 flex items-center">
                <i class="fa-solid fa-hospital-user mr-2 text-teal-600 text-base"></i> 1. Customer / Hospital (Dynamic)
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="md:col-span-2">
                    <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Select Customer</label>
                    <select name="customer_id" id="customer_select" onchange="onCustomerSelected()"
                            class="w-full px-3.5 py-2.5 border border-slate-300 rounded-lg text-sm bg-white font-medium focus:ring-2 focus:ring-teal-500 shadow-xs">
                        <option value="">-- Choose from existing customers --</option>
                        @foreach($customers as $c)
                            <option value="{{ $c->id }}" 
                                    data-name="{{ $c->name }}"
                                    data-dept="{{ $c->department }}"
                                    data-contact="{{ $c->contact_person }}"
                                    data-phone="{{ $c->contact_no }}"
                                    {{ old('customer_id', $record->customer_id) == $c->id ? 'selected' : '' }}>
                                {{ $c->name }} ({{ $c->department ?: 'No Dept' }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Customer / Organization Name</label>
                    <input type="text" name="customer_name" id="customer_name" required value="{{ old('customer_name', $record->customer_name) }}"
                           class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm bg-slate-50 focus:bg-white focus:ring-2 focus:ring-teal-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Department / Unit</label>
                    <input type="text" name="department" id="department" value="{{ old('department', $record->department) }}"
                           class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-teal-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Contact Person / Supervisor</label>
                    <input type="text" name="contact_person" id="contact_person" value="{{ old('contact_person', $record->contact_person) }}"
                           class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-teal-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Contact Number</label>
                    <input type="text" name="contact_no" id="contact_no" value="{{ old('contact_no', $record->contact_no) }}"
                           class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm font-mono focus:ring-2 focus:ring-teal-500">
                </div>
            </div>
        </div>

        <!-- Section 2: Product & Equipment -->
        <div class="bg-blue-50/40 p-5 rounded-xl border border-blue-100/80">
            <h3 class="text-sm font-bold uppercase tracking-wider text-blue-900 border-b border-blue-200/60 pb-3 mb-4 flex items-center">
                <i class="fa-solid fa-boxes-stacked mr-2 text-blue-600 text-base"></i> 2. Equipment &amp; Product Specification (Dynamic)
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="md:col-span-2">
                    <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Select Product / Equipment</label>
                    <select name="product_id" id="product_select" onchange="onProductSelected()"
                            class="w-full px-3.5 py-2.5 border border-slate-300 rounded-lg text-sm bg-white font-medium focus:ring-2 focus:ring-blue-500 shadow-xs">
                        <option value="">-- Choose from catalog or enter below --</option>
                        @foreach($products as $p)
                            <option value="{{ $p->id }}" 
                                    data-name="{{ $p->name }}"
                                    data-equipment="{{ $p->equipment_name ?: $p->name }}"
                                    data-model="{{ $p->model_make }}"
                                    data-interval="{{ $p->default_ppm_days ?: 30 }}"
                                    {{ old('product_id', $record->product_id) == $p->id ? 'selected' : '' }}>
                                {{ $p->name }} | {{ $p->model_make ?: 'No Model' }} (Default PPM: {{ $p->default_ppm_days ?: 30 }} days)
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Description of Product</label>
                    <input type="text" name="product_description" id="product_description" value="{{ old('product_description', $record->product_description) }}"
                           class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Equipment Name</label>
                    <input type="text" name="equipment_name" id="equipment_name" value="{{ old('equipment_name', $record->equipment_name) }}"
                           class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Model / Make</label>
                    <input type="text" name="model_make" id="model_make" value="{{ old('model_make', $record->model_make) }}"
                           class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Serial Number (S/N)</label>
                    <input type="text" name="serial_no" value="{{ old('serial_no', $record->serial_no) }}"
                           class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm font-mono focus:ring-2 focus:ring-blue-500">
                </div>
            </div>
        </div>

        <!-- Section 3: Document & Order Details -->
        <div>
            <h3 class="text-sm font-bold uppercase tracking-wider text-slate-800 border-b border-slate-200 pb-2 mb-4 flex items-center">
                <i class="fa-solid fa-file-invoice mr-2 text-slate-600"></i> 3. Document &amp; Purchase Order Details
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Entry / Form Date</label>
                    <input type="date" name="entry_date" value="{{ old('entry_date', optional($record->entry_date)->format('Y-m-d')) }}" 
                           class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-teal-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Purchase Order # (P.O)</label>
                    <input type="text" name="purchase_order_no" value="{{ old('purchase_order_no', $record->purchase_order_no) }}"
                           class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-teal-500 font-mono">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Supply Against</label>
                    <input type="text" name="supply_against" value="{{ old('supply_against', $record->supply_against) }}"
                           class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-teal-500">
                </div>
            </div>
        </div>

        <!-- Section 4: Execution & Dates -->
        <div>
            <h3 class="text-sm font-bold uppercase tracking-wider text-slate-800 border-b border-slate-200 pb-2 mb-4 flex items-center">
                <i class="fa-solid fa-calendar-check mr-2 text-slate-600"></i> 4. Installation Execution &amp; Maintenance Schedule
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Date of Installation</label>
                    <input type="date" name="installation_date" id="installation_date" value="{{ old('installation_date', optional($record->installation_date)->format('Y-m-d')) }}"
                           class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-teal-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Installation Performed By (Engineer)</label>
                    <input type="text" name="performed_by" value="{{ old('performed_by', $record->performed_by) }}"
                           class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-teal-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">
                        Next PPM Due Date <span class="text-amber-600 font-bold">(Auto-Notification)</span>
                    </label>
                    <input type="date" name="next_ppm_date" id="next_ppm_date" value="{{ old('next_ppm_date', optional($record->next_ppm_date)->format('Y-m-d')) }}"
                           class="w-full px-3 py-2 border border-amber-300 bg-amber-50/50 rounded-lg text-sm font-semibold text-amber-900 focus:ring-2 focus:ring-amber-500">
                    <div class="mt-1 flex flex-wrap gap-1">
                        <button type="button" onclick="setNextPpmOffset(30)" class="text-[10px] px-2 py-0.5 bg-slate-100 hover:bg-amber-100 text-slate-700 hover:text-amber-800 rounded border border-slate-200 transition font-medium">+1 Mo</button>
                        <button type="button" onclick="setNextPpmOffset(90)" class="text-[10px] px-2 py-0.5 bg-slate-100 hover:bg-amber-100 text-slate-700 hover:text-amber-800 rounded border border-slate-200 transition font-medium">+3 Mo</button>
                        <button type="button" onclick="setNextPpmOffset(180)" class="text-[10px] px-2 py-0.5 bg-slate-100 hover:bg-amber-100 text-slate-700 hover:text-amber-800 rounded border border-slate-200 transition font-medium">+6 Mo</button>
                        <button type="button" onclick="setNextPpmOffset(365)" class="text-[10px] px-2 py-0.5 bg-slate-100 hover:bg-amber-100 text-slate-700 hover:text-amber-800 rounded border border-slate-200 transition font-medium">+1 Yr</button>
                    </div>
                    <p class="text-[11px] text-amber-700 font-medium mt-1 flex items-center">
                        <i class="fa-solid fa-bell mr-1 text-amber-500"></i> Notification alert triggers on this date.
                    </p>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Activity / Service Type</label>
                    <select name="service_type" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm bg-white focus:ring-2 focus:ring-teal-500">
                        <option value="New Installation" {{ $record->service_type == 'New Installation' ? 'selected' : '' }}>New Installation</option>
                        <option value="PPM" {{ $record->service_type == 'PPM' ? 'selected' : '' }}>PPM (Preventive Maintenance)</option>
                        <option value="Equipment CRM Profile" {{ $record->service_type == 'Equipment CRM Profile' ? 'selected' : '' }}>Equipment CRM Profile</option>
                        <option value="Breakdown Repair" {{ $record->service_type == 'Breakdown Repair' ? 'selected' : '' }}>Breakdown Repair</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Record Type</label>
                    <select name="record_type" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm bg-white focus:ring-2 focus:ring-teal-500">
                        <option value="Installation / PPM" {{ $record->record_type == 'Installation / PPM' ? 'selected' : '' }}>Installation / PPM Form</option>
                        <option value="CRM Record" {{ $record->record_type == 'CRM Record' ? 'selected' : '' }}>CRM Equipment Profile</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Status</label>
                    <select name="status" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm bg-white focus:ring-2 focus:ring-teal-500">
                        <option value="Completed" {{ $record->status == 'Completed' ? 'selected' : '' }}>Completed</option>
                        <option value="Pending PPM" {{ $record->status == 'Pending PPM' ? 'selected' : '' }}>Pending PPM</option>
                        <option value="Active" {{ $record->status == 'Active' ? 'selected' : '' }}>Active</option>
                    </select>
                </div>

                <div class="md:col-span-3">
                    <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Scope of Work</label>
                    <textarea name="scope_of_work" rows="2" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-teal-500">{{ old('scope_of_work', $record->scope_of_work) }}</textarea>
                </div>

                <div class="md:col-span-3">
                    <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Remarks / Notes</label>
                    <textarea name="remarks" rows="2" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-teal-500">{{ old('remarks', $record->remarks) }}</textarea>
                </div>
            </div>
        </div>

        <div class="pt-4 border-t border-slate-200 flex justify-end gap-3">
            <a href="{{ route('ppm.index') }}" class="px-5 py-2.5 rounded-lg border border-slate-300 text-slate-700 text-sm font-semibold hover:bg-slate-50 transition">
                Cancel
            </a>
            <button type="submit" class="px-6 py-2.5 bg-teal-600 hover:bg-teal-700 text-white rounded-lg text-sm font-semibold shadow-md transition flex items-center">
                <i class="fa-solid fa-save mr-2"></i> Update Record
            </button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
    function onCustomerSelected() {
        const select = document.getElementById('customer_select');
        const selectedOption = select.options[select.selectedIndex];
        if (selectedOption && selectedOption.value) {
            document.getElementById('customer_name').value = selectedOption.getAttribute('data-name') || '';
            document.getElementById('department').value = selectedOption.getAttribute('data-dept') || '';
            document.getElementById('contact_person').value = selectedOption.getAttribute('data-contact') || '';
            document.getElementById('contact_no').value = selectedOption.getAttribute('data-phone') || '';
        }
    }

    function onProductSelected() {
        const select = document.getElementById('product_select');
        const selectedOption = select.options[select.selectedIndex];
        if (selectedOption && selectedOption.value) {
            document.getElementById('product_description').value = selectedOption.getAttribute('data-name') || '';
            document.getElementById('equipment_name').value = selectedOption.getAttribute('data-equipment') || '';
            document.getElementById('model_make').value = selectedOption.getAttribute('data-model') || '';
        }
    }

    function setNextPpmOffset(days) {
        const installDateVal = document.getElementById('installation_date').value;
        const date = installDateVal ? new Date(installDateVal) : new Date();
        date.setDate(date.getDate() + days);
        const yyyy = date.getFullYear();
        const mm = String(date.getMonth() + 1).padStart(2, '0');
        const dd = String(date.getDate()).padStart(2, '0');
        document.getElementById('next_ppm_date').value = `${yyyy}-${mm}-${dd}`;
    }
</script>
@endpush

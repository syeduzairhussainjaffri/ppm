@extends('ppm.layout')

@section('title', 'New Installation / PPM Form')
@section('page_category', 'New Service Entry')

@section('content')
<div class="w-full space-y-4">
    <!-- Header: Compact & Aligned -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2.5 bg-white p-3.5 sm:px-5 rounded-xl border border-slate-200 shadow-xs">
        <div>
            <h1 class="text-xl font-bold text-slate-900 tracking-tight flex items-center">
                <i class="fa-solid fa-notes-medical text-teal-600 mr-2 text-lg"></i> New Installation / PPM Service Entry
            </h1>
            <p class="text-xs text-slate-500 mt-0.5">Biomedical equipment installation sheet, hospital customer records &amp; preventive maintenance schedule.</p>
        </div>
        <a href="{{ route('ppm.index') }}" class="inline-flex items-center px-3 py-1.5 border border-slate-300 rounded-lg text-xs font-semibold text-slate-700 hover:bg-slate-50 transition shadow-xs">
            <i class="fa-solid fa-arrow-left mr-1.5"></i> Back to Register
        </a>
    </div>

    @if ($errors->any())
        <div class="p-3.5 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs shadow-xs">
            <p class="font-bold mb-1"><i class="fa-solid fa-circle-exclamation mr-1.5"></i> Please correct the following errors:</p>
            <ul class="list-disc list-inside space-y-0.5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('ppm.store') }}" method="POST" id="ppmForm" class="bg-white border border-slate-200 rounded-xl shadow-xs p-4 sm:p-5 space-y-4">
        @csrf

        <!-- Section 1: Dynamic Customer & Hospital Selection -->
        <div class="bg-teal-50/40 p-3.5 sm:p-4 rounded-xl border border-teal-100">
            <div class="flex items-center justify-between border-b border-teal-200/60 pb-2 mb-3">
                <h3 class="text-xs font-bold uppercase tracking-wider text-teal-900 flex items-center">
                    <i class="fa-solid fa-hospital-user mr-1.5 text-teal-600"></i> 1. Customer / Hospital (Dynamic)
                </h3>
                <button type="button" onclick="openCustomerModal()" class="inline-flex items-center px-2.5 py-1 text-xs font-semibold rounded-md bg-teal-600 hover:bg-teal-700 text-white shadow-xs transition">
                    <i class="fa-solid fa-plus mr-1"></i> Quick Add Customer
                </button>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-3">
                <div class="md:col-span-2 lg:col-span-4">
                    <label class="block text-[11px] font-semibold text-slate-700 uppercase mb-1">
                        Select Registered Customer / Hospital <span class="text-rose-500">*</span>
                    </label>
                    <select name="customer_id" id="customer_select" onchange="onCustomerSelected()" required
                            class="w-full px-3 py-2 border border-slate-300 rounded-lg text-xs sm:text-sm bg-white font-medium focus:ring-2 focus:ring-teal-500 focus:border-teal-500 shadow-xs">
                        <option value="">-- Choose from existing customer directory --</option>
                        @foreach($customers as $c)
                            <option value="{{ $c->id }}" 
                                    data-name="{{ $c->name }}"
                                    data-dept="{{ $c->department }}"
                                    data-contact="{{ $c->contact_person }}"
                                    data-phone="{{ $c->contact_no }}"
                                    {{ old('customer_id') == $c->id ? 'selected' : '' }}>
                                {{ $c->name }} ({{ $c->department ?: 'No Dept' }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="md:col-span-2">
                    <label class="block text-[11px] font-semibold text-slate-700 uppercase mb-1">Customer / Organization Name</label>
                    <input type="text" name="customer_name" id="customer_name" required value="{{ old('customer_name') }}" placeholder="Auto-fills upon selection or type custom"
                           class="w-full px-3 py-1.5 border border-slate-300 rounded-lg text-xs sm:text-sm bg-slate-50 focus:bg-white focus:ring-2 focus:ring-teal-500">
                </div>

                <div>
                    <label class="block text-[11px] font-semibold text-slate-700 uppercase mb-1">Department / Unit</label>
                    <input type="text" name="department" id="department" value="{{ old('department') }}" placeholder="e.g. Biomedical, Gynae, Orthopedic"
                           class="w-full px-3 py-1.5 border border-slate-300 rounded-lg text-xs sm:text-sm focus:ring-2 focus:ring-teal-500">
                </div>

                <div>
                    <label class="block text-[11px] font-semibold text-slate-700 uppercase mb-1">Contact Person / Supervisor</label>
                    <input type="text" name="contact_person" id="contact_person" value="{{ old('contact_person') }}" placeholder="e.g. Engr. Daniyal / DR / Technician"
                           class="w-full px-3 py-1.5 border border-slate-300 rounded-lg text-xs sm:text-sm focus:ring-2 focus:ring-teal-500">
                </div>

                <div>
                    <label class="block text-[11px] font-semibold text-slate-700 uppercase mb-1">Contact Number</label>
                    <input type="text" name="contact_no" id="contact_no" value="{{ old('contact_no') }}" placeholder="e.g. 0334-2834216"
                           class="w-full px-3 py-1.5 border border-slate-300 rounded-lg text-xs sm:text-sm font-mono focus:ring-2 focus:ring-teal-500">
                </div>
            </div>
        </div>

        <!-- Section 2: Dynamic Product & Equipment Selection -->
        <div class="bg-blue-50/40 p-3.5 sm:p-4 rounded-xl border border-blue-100">
            <div class="flex items-center justify-between border-b border-blue-200/60 pb-2 mb-3">
                <h3 class="text-xs font-bold uppercase tracking-wider text-blue-900 flex items-center">
                    <i class="fa-solid fa-boxes-stacked mr-1.5 text-blue-600"></i> 2. Equipment &amp; Product Specification (Dynamic)
                </h3>
                <button type="button" onclick="openProductModal()" class="inline-flex items-center px-2.5 py-1 text-xs font-semibold rounded-md bg-blue-600 hover:bg-blue-700 text-white shadow-xs transition">
                    <i class="fa-solid fa-plus mr-1"></i> Quick Add Product
                </button>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-3">
                <div class="md:col-span-2 lg:col-span-4">
                    <label class="block text-[11px] font-semibold text-slate-700 uppercase mb-1">
                        Select Registered Product / Equipment
                    </label>
                    <select name="product_id" id="product_select" onchange="onProductSelected()"
                            class="w-full px-3 py-2 border border-slate-300 rounded-lg text-xs sm:text-sm bg-white font-medium focus:ring-2 focus:ring-blue-500 focus:border-blue-500 shadow-xs">
                        <option value="">-- Choose from equipment catalog or enter details below --</option>
                        @foreach($products as $p)
                            <option value="{{ $p->id }}" 
                                    data-name="{{ $p->name }}"
                                    data-equipment="{{ $p->equipment_name ?: $p->name }}"
                                    data-model="{{ $p->model_make }}"
                                    data-category="{{ $p->category }}"
                                    data-interval="{{ $p->default_ppm_days ?: 30 }}"
                                    {{ old('product_id') == $p->id ? 'selected' : '' }}>
                                {{ $p->name }} | {{ $p->model_make ?: 'No Model' }} (Default PPM: {{ $p->default_ppm_days ?: 30 }} days)
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="md:col-span-2">
                    <label class="block text-[11px] font-semibold text-slate-700 uppercase mb-1">Description of Product</label>
                    <input type="text" name="product_description" id="product_description" value="{{ old('product_description') }}" placeholder="e.g. Oxygen Supply Line 400 Beds"
                           class="w-full px-3 py-1.5 border border-slate-300 rounded-lg text-xs sm:text-sm bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500">
                </div>

                <div>
                    <label class="block text-[11px] font-semibold text-slate-700 uppercase mb-1">Equipment Name</label>
                    <input type="text" name="equipment_name" id="equipment_name" value="{{ old('equipment_name') }}" placeholder="e.g. Oxygen Supply Line System / Air Purifier"
                           class="w-full px-3 py-1.5 border border-slate-300 rounded-lg text-xs sm:text-sm focus:ring-2 focus:ring-blue-500">
                </div>

                <div>
                    <label class="block text-[11px] font-semibold text-slate-700 uppercase mb-1">Model / Make</label>
                    <input type="text" name="model_make" id="model_make" value="{{ old('model_make') }}" placeholder="e.g. Noxerior, Italy / Spatel plus"
                           class="w-full px-3 py-1.5 border border-slate-300 rounded-lg text-xs sm:text-sm focus:ring-2 focus:ring-blue-500">
                </div>

                <div class="md:col-span-2">
                    <label class="block text-[11px] font-semibold text-slate-700 uppercase mb-1">Serial Number (S/N)</label>
                    <input type="text" name="serial_no" value="{{ old('serial_no') }}" placeholder="e.g. SN013517-48 / CM01249-12"
                           class="w-full px-3 py-1.5 border border-slate-300 rounded-lg text-xs sm:text-sm font-mono focus:ring-2 focus:ring-blue-500">
                </div>
            </div>
        </div>

        <!-- Section 3: Document & Purchase Order Details -->
        <div class="bg-slate-50/70 p-3.5 sm:p-4 rounded-xl border border-slate-200">
            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-800 border-b border-slate-200 pb-2 mb-3 flex items-center">
                <i class="fa-solid fa-file-invoice mr-1.5 text-slate-600"></i> 3. Document &amp; Purchase Order Details
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                <div>
                    <label class="block text-[11px] font-semibold text-slate-700 uppercase mb-1">Entry / Form Date</label>
                    <input type="date" name="entry_date" id="entry_date" value="{{ old('entry_date', date('Y-m-d')) }}" 
                           class="w-full px-3 py-1.5 border border-slate-300 rounded-lg text-xs sm:text-sm focus:ring-2 focus:ring-teal-500">
                </div>
                <div>
                    <label class="block text-[11px] font-semibold text-slate-700 uppercase mb-1">Purchase Order # (P.O)</label>
                    <input type="text" name="purchase_order_no" value="{{ old('purchase_order_no') }}" placeholder="e.g. DHSK/26-27/318"
                           class="w-full px-3 py-1.5 border border-slate-300 rounded-lg text-xs sm:text-sm focus:ring-2 focus:ring-teal-500 font-mono">
                </div>
                <div>
                    <label class="block text-[11px] font-semibold text-slate-700 uppercase mb-1">Supply Against</label>
                    <input type="text" name="supply_against" value="{{ old('supply_against') }}" placeholder="e.g. RTJ/Iwp/Dutch/08/4A (Imperial)"
                           class="w-full px-3 py-1.5 border border-slate-300 rounded-lg text-xs sm:text-sm focus:ring-2 focus:ring-teal-500">
                </div>
            </div>
        </div>

        <!-- Section 4: Installation Execution & Maintenance Schedule -->
        <div class="bg-slate-50/70 p-3.5 sm:p-4 rounded-xl border border-slate-200">
            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-800 border-b border-slate-200 pb-2 mb-3 flex items-center">
                <i class="fa-solid fa-calendar-check mr-1.5 text-slate-600"></i> 4. Installation Execution &amp; PPM Schedule
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                <div>
                    <label class="block text-[11px] font-semibold text-slate-700 uppercase mb-1">Date of Installation</label>
                    <input type="date" name="installation_date" id="installation_date" onchange="calculateNextPpmDate()" value="{{ old('installation_date', date('Y-m-d')) }}"
                           class="w-full px-3 py-1.5 border border-slate-300 rounded-lg text-xs sm:text-sm focus:ring-2 focus:ring-teal-500">
                </div>
                <div>
                    <label class="block text-[11px] font-semibold text-slate-700 uppercase mb-1">Installation Performed By (Engineer)</label>
                    <input type="text" name="performed_by" value="{{ old('performed_by') }}" placeholder="e.g. Engr. Bilal Ahmed"
                           class="w-full px-3 py-1.5 border border-slate-300 rounded-lg text-xs sm:text-sm focus:ring-2 focus:ring-teal-500">
                </div>
                <div>
                    <label class="block text-[11px] font-semibold text-slate-700 uppercase mb-1">
                        Next PPM Due Date <span class="text-amber-600 font-bold">(Auto-Notification)</span>
                    </label>
                    <input type="date" name="next_ppm_date" id="next_ppm_date" value="{{ old('next_ppm_date') }}"
                           class="w-full px-3 py-1.5 border border-amber-300 bg-amber-50/60 rounded-lg text-xs sm:text-sm font-semibold text-amber-900 focus:ring-2 focus:ring-amber-500">
                    <div class="mt-1 flex flex-wrap gap-1">
                        <button type="button" onclick="setNextPpmOffset(30)" class="text-[10px] px-1.5 py-0.5 bg-slate-100 hover:bg-amber-100 text-slate-700 hover:text-amber-800 rounded border border-slate-200 transition font-medium">+1 Mo</button>
                        <button type="button" onclick="setNextPpmOffset(90)" class="text-[10px] px-1.5 py-0.5 bg-slate-100 hover:bg-amber-100 text-slate-700 hover:text-amber-800 rounded border border-slate-200 transition font-medium">+3 Mo</button>
                        <button type="button" onclick="setNextPpmOffset(180)" class="text-[10px] px-1.5 py-0.5 bg-slate-100 hover:bg-amber-100 text-slate-700 hover:text-amber-800 rounded border border-slate-200 transition font-medium">+6 Mo</button>
                        <button type="button" onclick="setNextPpmOffset(365)" class="text-[10px] px-1.5 py-0.5 bg-slate-100 hover:bg-amber-100 text-slate-700 hover:text-amber-800 rounded border border-slate-200 transition font-medium">+1 Yr</button>
                    </div>
                    <p class="text-[10px] text-amber-700 font-medium mt-0.5 flex items-center">
                        <i class="fa-solid fa-bell mr-1 text-amber-500"></i> Notification will trigger on this scheduled date.
                    </p>
                </div>

                <div>
                    <label class="block text-[11px] font-semibold text-slate-700 uppercase mb-1">Activity / Service Type</label>
                    <select name="service_type" class="w-full px-3 py-1.5 border border-slate-300 rounded-lg text-xs sm:text-sm bg-white focus:ring-2 focus:ring-teal-500">
                        <option value="New Installation" selected>New Installation</option>
                        <option value="PPM">PPM (Preventive Maintenance)</option>
                        <option value="Equipment CRM Profile">Equipment CRM Profile</option>
                        <option value="Breakdown Repair">Breakdown Repair</option>
                    </select>
                </div>

                <div>
                    <label class="block text-[11px] font-semibold text-slate-700 uppercase mb-1">Record Type</label>
                    <select name="record_type" class="w-full px-3 py-1.5 border border-slate-300 rounded-lg text-xs sm:text-sm bg-white focus:ring-2 focus:ring-teal-500">
                        <option value="Installation / PPM">Installation / PPM Form</option>
                        <option value="CRM Record">CRM Equipment Profile</option>
                    </select>
                </div>

                <div>
                    <label class="block text-[11px] font-semibold text-slate-700 uppercase mb-1">Status</label>
                    <select name="status" class="w-full px-3 py-1.5 border border-slate-300 rounded-lg text-xs sm:text-sm bg-white focus:ring-2 focus:ring-teal-500">
                        <option value="Completed" selected>Completed</option>
                        <option value="Pending PPM">Pending PPM</option>
                        <option value="Active">Active</option>
                    </select>
                </div>

                <div class="md:col-span-2 lg:col-span-3">
                    <label class="block text-[11px] font-semibold text-slate-700 uppercase mb-1">Scope of Work</label>
                    <textarea name="scope_of_work" rows="2" placeholder="e.g. New Installed On Site. Pressure test passed and medical line commissioned."
                              class="w-full px-3 py-1.5 border border-slate-300 rounded-lg text-xs sm:text-sm focus:ring-2 focus:ring-teal-500">{{ old('scope_of_work') }}</textarea>
                </div>

                <div class="md:col-span-2 lg:col-span-3">
                    <label class="block text-[11px] font-semibold text-slate-700 uppercase mb-1">Remarks / Special Notes</label>
                    <textarea name="remarks" rows="2" placeholder="Any hospital observations, ward location or technician remarks..."
                              class="w-full px-3 py-1.5 border border-slate-300 rounded-lg text-xs sm:text-sm focus:ring-2 focus:ring-teal-500">{{ old('remarks') }}</textarea>
                </div>
            </div>
        </div>

        <!-- Buttons -->
        <div class="pt-2 border-t border-slate-200 flex justify-end gap-2.5">
            <a href="{{ route('ppm.index') }}" class="px-4 py-2 rounded-lg border border-slate-300 text-slate-700 text-xs font-semibold hover:bg-slate-50 transition">
                Cancel
            </a>
            <button type="submit" class="px-5 py-2 bg-teal-600 hover:bg-teal-700 text-white rounded-lg text-xs font-semibold shadow-sm transition flex items-center">
                <i class="fa-solid fa-floppy-disk mr-1.5"></i> Save Installation / PPM Record
            </button>
        </div>
    </form>
</div>

<!-- ================= QUICK ADD CUSTOMER MODAL ================= -->
<div id="customerModal" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-xs hidden p-4">
    <div class="bg-white rounded-2xl shadow-2xl max-w-lg w-full overflow-hidden border border-slate-200">
        <div class="px-5 py-3.5 bg-teal-600 text-white flex items-center justify-between">
            <div class="flex items-center space-x-2">
                <i class="fa-solid fa-hospital-user text-base"></i>
                <h3 class="font-bold text-sm">Quick Add New Customer</h3>
            </div>
            <button onclick="closeCustomerModal()" class="text-white/80 hover:text-white text-xl leading-none">&times;</button>
        </div>
        <form id="quickCustomerForm" onsubmit="submitQuickCustomer(event)" class="p-5 space-y-3.5">
            <div>
                <label class="block text-[11px] font-semibold text-slate-700 uppercase mb-1">Hospital / Customer Name <span class="text-rose-500">*</span></label>
                <input type="text" id="qc_name" required placeholder="e.g. Liaquat National Hospital" 
                       class="w-full px-3 py-1.5 border border-slate-300 rounded-lg text-xs sm:text-sm focus:ring-2 focus:ring-teal-500">
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-[11px] font-semibold text-slate-700 uppercase mb-1">Department</label>
                    <input type="text" id="qc_dept" placeholder="e.g. Biomedical / ICU" 
                           class="w-full px-3 py-1.5 border border-slate-300 rounded-lg text-xs sm:text-sm focus:ring-2 focus:ring-teal-500">
                </div>
                <div>
                    <label class="block text-[11px] font-semibold text-slate-700 uppercase mb-1">Contact Person</label>
                    <input type="text" id="qc_contact" placeholder="e.g. Dr. Asif / In-charge" 
                           class="w-full px-3 py-1.5 border border-slate-300 rounded-lg text-xs sm:text-sm focus:ring-2 focus:ring-teal-500">
                </div>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-[11px] font-semibold text-slate-700 uppercase mb-1">Contact Number</label>
                    <input type="text" id="qc_phone" placeholder="e.g. 0321-9876543" 
                           class="w-full px-3 py-1.5 border border-slate-300 rounded-lg text-xs sm:text-sm focus:ring-2 focus:ring-teal-500">
                </div>
                <div>
                    <label class="block text-[11px] font-semibold text-slate-700 uppercase mb-1">City / Address</label>
                    <input type="text" id="qc_address" placeholder="e.g. Karachi" 
                           class="w-full px-3 py-1.5 border border-slate-300 rounded-lg text-xs sm:text-sm focus:ring-2 focus:ring-teal-500">
                </div>
            </div>
            <div class="pt-2 border-t border-slate-200 flex justify-end gap-2">
                <button type="button" onclick="closeCustomerModal()" class="px-3.5 py-1.5 border border-slate-300 rounded-lg text-xs font-semibold text-slate-700 hover:bg-slate-50">Cancel</button>
                <button type="submit" id="qc_submit_btn" class="px-4 py-1.5 bg-teal-600 hover:bg-teal-700 text-white rounded-lg text-xs font-semibold shadow-xs">
                    Save &amp; Select
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ================= QUICK ADD PRODUCT MODAL ================= -->
<div id="productModal" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-xs hidden p-4">
    <div class="bg-white rounded-2xl shadow-2xl max-w-lg w-full overflow-hidden border border-slate-200">
        <div class="px-5 py-3.5 bg-blue-600 text-white flex items-center justify-between">
            <div class="flex items-center space-x-2">
                <i class="fa-solid fa-boxes-stacked text-base"></i>
                <h3 class="font-bold text-sm">Quick Add New Product</h3>
            </div>
            <button onclick="closeProductModal()" class="text-white/80 hover:text-white text-xl leading-none">&times;</button>
        </div>
        <form id="quickProductForm" onsubmit="submitQuickProduct(event)" class="p-5 space-y-3.5">
            <div>
                <label class="block text-[11px] font-semibold text-slate-700 uppercase mb-1">Product Description / Name <span class="text-rose-500">*</span></label>
                <input type="text" id="qp_name" required placeholder="e.g. Medical Ventilator Model V500" 
                       class="w-full px-3 py-1.5 border border-slate-300 rounded-lg text-xs sm:text-sm focus:ring-2 focus:ring-blue-500">
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-[11px] font-semibold text-slate-700 uppercase mb-1">Equipment Name</label>
                    <input type="text" id="qp_equipment" placeholder="e.g. ICU Ventilator" 
                       class="w-full px-3 py-1.5 border border-slate-300 rounded-lg text-xs sm:text-sm focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-[11px] font-semibold text-slate-700 uppercase mb-1">Model / Make</label>
                    <input type="text" id="qp_model" placeholder="e.g. Dräger, Germany" 
                           class="w-full px-3 py-1.5 border border-slate-300 rounded-lg text-xs sm:text-sm focus:ring-2 focus:ring-blue-500">
                </div>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-[11px] font-semibold text-slate-700 uppercase mb-1">Category</label>
                    <input type="text" id="qp_category" placeholder="e.g. Life Support" 
                           class="w-full px-3 py-1.5 border border-slate-300 rounded-lg text-xs sm:text-sm focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-[11px] font-semibold text-slate-700 uppercase mb-1">Default PPM Interval (Days)</label>
                    <input type="number" id="qp_days" value="30" min="1" 
                           class="w-full px-3 py-1.5 border border-slate-300 rounded-lg text-xs sm:text-sm focus:ring-2 focus:ring-blue-500">
                </div>
            </div>
            <div class="pt-2 border-t border-slate-200 flex justify-end gap-2">
                <button type="button" onclick="closeProductModal()" class="px-3.5 py-1.5 border border-slate-300 rounded-lg text-xs font-semibold text-slate-700 hover:bg-slate-50">Cancel</button>
                <button type="submit" id="qp_submit_btn" class="px-4 py-1.5 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-xs font-semibold shadow-xs">
                    Save &amp; Select
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Auto-fill Customer Details
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

    // Auto-fill Product Details & Calculate Next PPM Date
    let currentPpmIntervalDays = 30;

    function onProductSelected() {
        const select = document.getElementById('product_select');
        const selectedOption = select.options[select.selectedIndex];
        
        if (selectedOption && selectedOption.value) {
            document.getElementById('product_description').value = selectedOption.getAttribute('data-name') || '';
            document.getElementById('equipment_name').value = selectedOption.getAttribute('data-equipment') || '';
            document.getElementById('model_make').value = selectedOption.getAttribute('data-model') || '';
            
            const interval = parseInt(selectedOption.getAttribute('data-interval')) || 30;
            currentPpmIntervalDays = interval;
            calculateNextPpmDate();
        }
    }

    // Auto calculate Next PPM Date based on installation date + product interval
    function calculateNextPpmDate() {
        const installDateVal = document.getElementById('installation_date').value;
        if (installDateVal) {
            const date = new Date(installDateVal);
            date.setDate(date.getDate() + currentPpmIntervalDays);
            const yyyy = date.getFullYear();
            const mm = String(date.getMonth() + 1).padStart(2, '0');
            const dd = String(date.getDate()).padStart(2, '0');
            document.getElementById('next_ppm_date').value = `${yyyy}-${mm}-${dd}`;
        }
    }

    // Set Next PPM date with custom day offset from installation date
    function setNextPpmOffset(days) {
        const installDateVal = document.getElementById('installation_date').value;
        const date = installDateVal ? new Date(installDateVal) : new Date();
        date.setDate(date.getDate() + days);
        const yyyy = date.getFullYear();
        const mm = String(date.getMonth() + 1).padStart(2, '0');
        const dd = String(date.getDate()).padStart(2, '0');
        document.getElementById('next_ppm_date').value = `${yyyy}-${mm}-${dd}`;
    }

    // Initial calculation if empty
    if (!document.getElementById('next_ppm_date').value) {
        calculateNextPpmDate();
    }

    // Customer Modal Handlers
    function openCustomerModal() {
        document.getElementById('customerModal').classList.remove('hidden');
        document.getElementById('qc_name').focus();
    }
    function closeCustomerModal() {
        document.getElementById('customerModal').classList.add('hidden');
        document.getElementById('quickCustomerForm').reset();
    }

    // Product Modal Handlers
    function openProductModal() {
        document.getElementById('productModal').classList.remove('hidden');
        document.getElementById('qp_name').focus();
    }
    function closeProductModal() {
        document.getElementById('productModal').classList.add('hidden');
        document.getElementById('quickProductForm').reset();
    }

    // Quick Customer AJAX Submission
    async function submitQuickCustomer(e) {
        e.preventDefault();
        const submitBtn = document.getElementById('qc_submit_btn');
        submitBtn.disabled = true;
        submitBtn.innerText = 'Saving...';

        const data = {
            name: document.getElementById('qc_name').value,
            department: document.getElementById('qc_dept').value,
            contact_person: document.getElementById('qc_contact').value,
            contact_no: document.getElementById('qc_phone').value,
            address: document.getElementById('qc_address').value,
        };

        try {
            const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            const res = await fetch("{{ route('customers.quick') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrf
                },
                body: JSON.stringify(data)
            });

            const result = await res.json();
            if (res.ok && result.customer) {
                const c = result.customer;
                const select = document.getElementById('customer_select');
                const opt = document.createElement('option');
                opt.value = c.id;
                opt.textContent = `${c.name} (${c.department || 'No Dept'})`;
                opt.setAttribute('data-name', c.name);
                opt.setAttribute('data-dept', c.department || '');
                opt.setAttribute('data-contact', c.contact_person || '');
                opt.setAttribute('data-phone', c.contact_no || '');
                opt.selected = true;
                select.appendChild(opt);

                onCustomerSelected();
                closeCustomerModal();
            } else {
                alert(result.message || 'Error creating customer. Please ensure name is unique.');
            }
        } catch (err) {
            alert('Failed to connect to server: ' + err.message);
        } finally {
            submitBtn.disabled = false;
            submitBtn.innerText = 'Save & Select';
        }
    }

    // Quick Product AJAX Submission
    async function submitQuickProduct(e) {
        e.preventDefault();
        const submitBtn = document.getElementById('qp_submit_btn');
        submitBtn.disabled = true;
        submitBtn.innerText = 'Saving...';

        const data = {
            name: document.getElementById('qp_name').value,
            equipment_name: document.getElementById('qp_equipment').value,
            model_make: document.getElementById('qp_model').value,
            category: document.getElementById('qp_category').value,
            default_ppm_days: document.getElementById('qp_days').value || 30,
        };

        try {
            const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            const res = await fetch("{{ route('products.quick') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrf
                },
                body: JSON.stringify(data)
            });

            const result = await res.json();
            if (res.ok && result.product) {
                const p = result.product;
                const select = document.getElementById('product_select');
                const opt = document.createElement('option');
                opt.value = p.id;
                opt.textContent = `${p.name} | ${p.model_make || 'No Model'} (Default PPM: ${p.default_ppm_days || 30} days)`;
                opt.setAttribute('data-name', p.name);
                opt.setAttribute('data-equipment', p.equipment_name || p.name);
                opt.setAttribute('data-model', p.model_make || '');
                opt.setAttribute('data-interval', p.default_ppm_days || 30);
                opt.selected = true;
                select.appendChild(opt);

                onProductSelected();
                closeProductModal();
            } else {
                alert(result.message || 'Error creating product.');
            }
        } catch (err) {
            alert('Failed to connect to server: ' + err.message);
        } finally {
            submitBtn.disabled = false;
            submitBtn.innerText = 'Save & Select';
        }
    }
</script>
@endpush

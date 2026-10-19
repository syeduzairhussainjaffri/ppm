<?php

namespace App\Http\Controllers;

use App\Models\PpmRecord;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Branch;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use Carbon\Carbon;

class PpmRecordController extends Controller
{
    /**
     * Display a listing of the resource with branch scoping & dynamic permissions.
     */
    public function index(Request $request)
    {
        $this->authorize('view_ppm');

        $search = $request->input('search');
        $department = $request->input('department');
        $serviceType = $request->input('service_type');
        $ppmStatus = $request->input('ppm_status');
        $today = now()->toDateString();

        $query = PpmRecord::forUserBranch()->with(['customer', 'product', 'branch'])->latest();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('customer_name', 'like', "%{$search}%")
                  ->orWhere('purchase_order_no', 'like', "%{$search}%")
                  ->orWhere('serial_no', 'like', "%{$search}%")
                  ->orWhere('equipment_name', 'like', "%{$search}%")
                  ->orWhere('product_description', 'like', "%{$search}%")
                  ->orWhere('performed_by', 'like', "%{$search}%");
            });
        }

        if ($department) {
            $query->where('department', $department);
        }

        if ($serviceType) {
            $query->where('service_type', $serviceType);
        }

        // PPM Status Filtering (Due Today, Overdue, Upcoming 7 Days)
        if ($ppmStatus === 'due_today') {
            $query->whereDate('next_ppm_date', $today);
        } elseif ($ppmStatus === 'overdue') {
            $query->whereNotNull('next_ppm_date')
                  ->whereDate('next_ppm_date', '<', $today)
                  ->where(function ($q) {
                      $q->where('status', '!=', 'Completed')
                        ->orWhereNull('status');
                  });
        } elseif ($ppmStatus === 'upcoming_week') {
            $query->whereDate('next_ppm_date', '>', $today)
                  ->whereDate('next_ppm_date', '<=', now()->addDays(7)->toDateString());
        }

        $records = $query->paginate(10)->withQueryString();

        // Branch-scoped summary counts
        $scopedBase = PpmRecord::forUserBranch();

        $stats = [
            'total' => (clone $scopedBase)->count(),
            'new_installations' => (clone $scopedBase)->where('service_type', 'New Installation')->count(),
            'biomedical' => (clone $scopedBase)->where('department', 'like', '%Biomedical%')->count(),
            'due_today' => (clone $scopedBase)->whereDate('next_ppm_date', $today)->count(),
            'overdue' => (clone $scopedBase)->whereNotNull('next_ppm_date')
                ->whereDate('next_ppm_date', '<', $today)
                ->where(function ($q) {
                    $q->where('status', '!=', 'Completed')
                      ->orWhereNull('status');
                })->count(),
            'upcoming_ppm' => (clone $scopedBase)->whereNotNull('next_ppm_date')
                ->whereDate('next_ppm_date', '>=', $today)
                ->count(),
            'customers_count' => Customer::forUserBranch()->count(),
            'products_count' => Product::count(),
        ];

        return view('ppm.index', compact('records', 'stats', 'search', 'department', 'serviceType', 'ppmStatus'));
    }

    /**
     * Show the form for creating a new resource with dynamic customers and products.
     */
    public function create()
    {
        $this->authorize('create_ppm');

        $customers = Customer::forUserBranch()->orderBy('name')->get();
        $products = Product::orderBy('name')->get();
        $branches = Branch::active()->orderBy('name')->get();

        return view('ppm.create', compact('customers', 'products', 'branches'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorize('create_ppm');

        $validated = $request->validate([
            'branch_id' => 'nullable|exists:branches,id',
            'customer_id' => 'nullable|exists:customers,id',
            'product_id' => 'nullable|exists:products,id',
            'record_type' => 'nullable|string|max:255',
            'entry_date' => 'nullable|date',
            'purchase_order_no' => 'nullable|string|max:255',
            'supply_against' => 'nullable|string|max:255',
            'customer_name' => 'nullable|string|max:255',
            'product_description' => 'nullable|string|max:255',
            'equipment_name' => 'nullable|string|max:255',
            'model_make' => 'nullable|string|max:255',
            'serial_no' => 'nullable|string|max:255',
            'department' => 'nullable|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'contact_no' => 'nullable|string|max:255',
            'installation_date' => 'nullable|date',
            'performed_by' => 'nullable|string|max:255',
            'service_type' => 'nullable|string|max:255',
            'scope_of_work' => 'nullable|string',
            'next_ppm_date' => 'nullable|date',
            'status' => 'nullable|string|max:255',
            'remarks' => 'nullable|string',
        ]);

        $user = auth()->user();

        // Enforce user's branch if user does not have multi-branch global access
        if ($user && $user->branch_id && !$user->hasAllBranchesAccess()) {
            $validated['branch_id'] = $user->branch_id;
        } elseif (empty($validated['branch_id']) && $user && $user->branch_id) {
            $validated['branch_id'] = $user->branch_id;
        }

        // Auto-fill customer name if customer_id selected and customer_name not typed
        if (!empty($validated['customer_id'])) {
            $customer = Customer::find($validated['customer_id']);
            if ($customer && empty($validated['customer_name'])) {
                $validated['customer_name'] = $customer->name;
            }
        }

        // Auto-fill product fields if product_id selected
        if (!empty($validated['product_id'])) {
            $product = Product::find($validated['product_id']);
            if ($product) {
                if (empty($validated['product_description'])) {
                    $validated['product_description'] = $product->name;
                }
                if (empty($validated['equipment_name'])) {
                    $validated['equipment_name'] = $product->equipment_name ?: $product->name;
                }
                if (empty($validated['model_make'])) {
                    $validated['model_make'] = $product->model_make;
                }
            }
        }

        // Ensure customer_name has fallback
        if (empty($validated['customer_name'])) {
            $validated['customer_name'] = 'General Customer';
        }

        $record = PpmRecord::create($validated);

        $msg = 'Installation / PPM record created successfully.';
        if ($record->next_ppm_date) {
            if ($record->next_ppm_date->isToday()) {
                $msg .= ' 🔔 PPM IS DUE TODAY (' . $record->next_ppm_date->format('d-M-Y') . ')! Maintenance notification active.';
            } else {
                $msg .= ' 🔔 Next PPM notification scheduled for ' . $record->next_ppm_date->format('d-M-Y') . '.';
            }
        }

        return redirect()->route('ppm.show', $record)->with('success', $msg);
    }

    /**
     * Display the specified resource in a printable voucher / report view.
     */
    public function show(PpmRecord $ppm)
    {
        $this->authorize('view_ppm');

        // Check branch access if user is scoped to a specific branch
        $user = auth()->user();
        if ($user && !$user->hasAllBranchesAccess() && $ppm->branch_id && $ppm->branch_id !== $user->branch_id) {
            abort(403, 'Unauthorized access: This service record belongs to another branch.');
        }

        $ppm->load(['customer', 'product', 'branch']);
        return view('ppm.show', ['record' => $ppm]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(PpmRecord $ppm)
    {
        $this->authorize('edit_ppm');

        $user = auth()->user();
        if ($user && !$user->hasAllBranchesAccess() && $ppm->branch_id && $ppm->branch_id !== $user->branch_id) {
            abort(403, 'Unauthorized action: You cannot edit records from another branch.');
        }

        $customers = Customer::forUserBranch()->orderBy('name')->get();
        $products = Product::orderBy('name')->get();
        $branches = Branch::active()->orderBy('name')->get();

        return view('ppm.edit', [
            'record' => $ppm,
            'customers' => $customers,
            'products' => $products,
            'branches' => $branches,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, PpmRecord $ppm)
    {
        $this->authorize('edit_ppm');

        $user = auth()->user();
        if ($user && !$user->hasAllBranchesAccess() && $ppm->branch_id && $ppm->branch_id !== $user->branch_id) {
            abort(403, 'Unauthorized action: You cannot update records from another branch.');
        }

        $validated = $request->validate([
            'branch_id' => 'nullable|exists:branches,id',
            'customer_id' => 'nullable|exists:customers,id',
            'product_id' => 'nullable|exists:products,id',
            'record_type' => 'nullable|string|max:255',
            'entry_date' => 'nullable|date',
            'purchase_order_no' => 'nullable|string|max:255',
            'supply_against' => 'nullable|string|max:255',
            'customer_name' => 'nullable|string|max:255',
            'product_description' => 'nullable|string|max:255',
            'equipment_name' => 'nullable|string|max:255',
            'model_make' => 'nullable|string|max:255',
            'serial_no' => 'nullable|string|max:255',
            'department' => 'nullable|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'contact_no' => 'nullable|string|max:255',
            'installation_date' => 'nullable|date',
            'performed_by' => 'nullable|string|max:255',
            'service_type' => 'nullable|string|max:255',
            'scope_of_work' => 'nullable|string',
            'next_ppm_date' => 'nullable|date',
            'status' => 'nullable|string|max:255',
            'remarks' => 'nullable|string',
        ]);

        if (!empty($validated['customer_id'])) {
            $customer = Customer::find($validated['customer_id']);
            if ($customer && empty($validated['customer_name'])) {
                $validated['customer_name'] = $customer->name;
            }
        }

        if (!empty($validated['product_id'])) {
            $product = Product::find($validated['product_id']);
            if ($product) {
                if (empty($validated['product_description'])) {
                    $validated['product_description'] = $product->name;
                }
                if (empty($validated['equipment_name'])) {
                    $validated['equipment_name'] = $product->equipment_name ?: $product->name;
                }
                if (empty($validated['model_make'])) {
                    $validated['model_make'] = $product->model_make;
                }
            }
        }

        if (empty($validated['customer_name'])) {
            $validated['customer_name'] = 'General Customer';
        }

        // If user is locked to a branch, preserve or set user's branch
        if ($user && $user->branch_id && !$user->hasAllBranchesAccess()) {
            $validated['branch_id'] = $user->branch_id;
        }

        $ppm->update($validated);

        $msg = 'Installation / PPM record updated successfully.';
        if ($ppm->next_ppm_date) {
            if ($ppm->next_ppm_date->isToday()) {
                $msg .= ' 🔔 PPM IS DUE TODAY (' . $ppm->next_ppm_date->format('d-M-Y') . ')! Maintenance notification active.';
            } else {
                $msg .= ' 🔔 Next PPM notification scheduled for ' . $ppm->next_ppm_date->format('d-M-Y') . '.';
            }
        }

        return redirect()->route('ppm.show', $ppm)->with('success', $msg);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PpmRecord $ppm)
    {
        $this->authorize('delete_ppm');

        $user = auth()->user();
        if ($user && !$user->hasAllBranchesAccess() && $ppm->branch_id && $ppm->branch_id !== $user->branch_id) {
            abort(403, 'Unauthorized action: You cannot delete records from another branch.');
        }

        $ppm->delete();

        return redirect()->route('ppm.index')
            ->with('success', 'Record deleted successfully.');
    }

    /**
     * Export all records to CSV format with branch awareness.
     */
    public function exportCsv(): StreamedResponse
    {
        $this->authorize('export_ppm');

        $records = PpmRecord::forUserBranch()->with(['customer', 'product', 'branch'])->get();
        $filename = "installation_ppm_records_" . date('Ymd_His') . ".csv";

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        return response()->stream(function () use ($records) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, [
                'ID',
                'Branch',
                'Type',
                'Date',
                'Purchase Order #',
                'Supply Against',
                'Customer ID',
                'Customer Name',
                'Product ID',
                'Product Description',
                'Equipment Name',
                'Model/Make',
                'Serial No',
                'Department',
                'Contact Person',
                'Contact No',
                'Installation Date',
                'Installation Performed By',
                'Service Type',
                'Scope of Work',
                'Next PPM Date',
                'Status',
                'Remarks'
            ]);

            foreach ($records as $row) {
                fputcsv($handle, [
                    $row->id,
                    $row->branch ? $row->branch->name : 'HQ / Unassigned',
                    $row->record_type,
                    optional($row->entry_date)->format('Y-m-d'),
                    $row->purchase_order_no,
                    $row->supply_against,
                    $row->customer_id,
                    $row->customer_name,
                    $row->product_id,
                    $row->product_description,
                    $row->equipment_name,
                    $row->model_make,
                    $row->serial_no,
                    $row->department,
                    $row->contact_person,
                    $row->contact_no,
                    optional($row->installation_date)->format('Y-m-d'),
                    $row->performed_by,
                    $row->service_type,
                    $row->scope_of_work,
                    optional($row->next_ppm_date)->format('Y-m-d'),
                    $row->status,
                    $row->remarks,
                ]);
            }
            fclose($handle);
        }, 200, $headers);
    }

    /**
     * Download Excel/CSV Import Template with sample data and column instructions.
     */
    public function downloadTemplate(Request $request)
    {
        $this->authorize('import_ppm');

        $format = $request->query('format', 'xlsx');

        $headers = [
            'Branch Name / Code',
            'Customer / Hospital Name',
            'Department',
            'Contact Person',
            'Contact Number',
            'Product / Item Description',
            'Equipment Name',
            'Model / Make',
            'Serial Number (S/N)',
            'Purchase Order # (P.O)',
            'Supply Against',
            'Entry Date (YYYY-MM-DD)',
            'Installation Date (YYYY-MM-DD)',
            'Service Performed By',
            'Service Type',
            'Record Type',
            'Next PPM Due Date (YYYY-MM-DD)',
            'Status',
            'Scope of Work',
            'Remarks / Notes',
        ];

        $sampleRow = [
            'Main Branch',
            'Liaquat National Hospital',
            'Biomedical / ICU',
            'Engr. Tariq Mehmood',
            '0300-1234567',
            'Oxygen Supply Line 400 Beds',
            'Medical Oxygen Plant',
            'Noxerior Italy',
            'SN-987654-2026',
            'PO-2026-0091',
            'Supply Order REF-01',
            date('Y-m-d'),
            date('Y-m-d'),
            'Engr. Daniyal',
            'New Installation',
            'Installation / PPM',
            date('Y-m-d', strtotime('+90 days')),
            'Completed',
            'System installed, calibrated and commissioned successfully.',
            'Periodic maintenance scheduled after 90 days.',
        ];

        if ($format === 'csv') {
            $filename = "ppm_import_template_" . date('Ymd') . ".csv";
            return response()->stream(function () use ($headers, $sampleRow) {
                $handle = fopen('php://output', 'w');
                fputcsv($handle, $headers);
                fputcsv($handle, $sampleRow);
                fclose($handle);
            }, 200, [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            ]);
        }

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('PPM Import Template');

        // Header Styling
        $sheet->fromArray([$headers], null, 'A1');
        $sheet->fromArray([$sampleRow], null, 'A2');

        // Style Header Row
        $headerRange = 'A1:T1';
        $sheet->getStyle($headerRange)->getFont()->setBold(true)->getColor()->setRGB('FFFFFF');
        $sheet->getStyle($headerRange)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setRGB('0D9488'); // Teal 600

        foreach (range('A', 'T') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $filename = "ppm_import_template_" . date('Ymd') . ".xlsx";
        $writer = new Xlsx($spreadsheet);

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    /**
     * Import PPM Records from Excel (.xlsx, .xls) or CSV file.
     */
    public function importExcel(Request $request)
    {
        $this->authorize('import_ppm');

        $request->validate([
            'excel_file' => 'required|file|mimes:xlsx,xls,csv,txt|max:20480',
            'default_branch_id' => 'nullable|exists:branches,id',
        ]);

        $file = $request->file('excel_file');
        $extension = strtolower($file->getClientOriginalExtension());
        $user = auth()->user();

        // Branch scoping
        $fallbackBranchId = null;
        if ($user && $user->branch_id && !$user->hasAllBranchesAccess()) {
            $fallbackBranchId = $user->branch_id;
        } elseif ($request->filled('default_branch_id')) {
            $fallbackBranchId = $request->input('default_branch_id');
        } elseif (session('selected_branch_id') && session('selected_branch_id') !== 'all') {
            $fallbackBranchId = session('selected_branch_id');
        }

        // Parse rows depending on format
        $rows = [];
        if ($extension === 'csv' || $extension === 'txt') {
            if (($handle = fopen($file->getRealPath(), 'r')) !== false) {
                while (($data = fgetcsv($handle)) !== false) {
                    $rows[] = $data;
                }
                fclose($handle);
            }
        } else {
            try {
                $spreadsheet = IOFactory::load($file->getRealPath());
                $sheet = $spreadsheet->getActiveSheet();
                $rows = $sheet->toArray(null, true, true, false);
            } catch (\Exception $e) {
                return back()->withErrors(['excel_file' => 'Unable to read the Excel file: ' . $e->getMessage()]);
            }
        }

        if (empty($rows) || count($rows) < 2) {
            return back()->withErrors(['excel_file' => 'The uploaded file appears to be empty or does not contain data rows.']);
        }

        $headerRow = array_map(function ($item) {
            return strtolower(trim(preg_replace('/[^a-zA-Z0-9]/', '', (string)$item)));
        }, $rows[0]);

        // Helper to find column index by aliases
        $findCol = function (array $aliases) use ($headerRow) {
            foreach ($aliases as $alias) {
                $clean = strtolower(trim(preg_replace('/[^a-zA-Z0-9]/', '', $alias)));
                $idx = array_search($clean, $headerRow, true);
                if ($idx !== false) {
                    return $idx;
                }
            }
            return false;
        };

        $colBranch = $findCol(['branch', 'branchname', 'branchcode']);
        $colCustomer = $findCol(['customer', 'customername', 'hospital', 'hospitalname', 'organization']);
        $colDept = $findCol(['department', 'dept', 'unit']);
        $colContactPerson = $findCol(['contactperson', 'supervisor', 'contact']);
        $colContactNo = $findCol(['contactno', 'phone', 'contactnumber', 'mobile']);
        $colProduct = $findCol(['product', 'productdescription', 'descriptionofproduct', 'item']);
        $colEquipment = $findCol(['equipment', 'equipmentname', 'device']);
        $colModel = $findCol(['model', 'make', 'modelmake']);
        $colSerial = $findCol(['serial', 'serialno', 'sn', 'serialnumber']);
        $colPO = $findCol(['po', 'purchaseorderno', 'purchaseorder', 'pono']);
        $colSupply = $findCol(['supplyagainst', 'supply']);
        $colEntryDate = $findCol(['entrydate', 'date', 'formdate']);
        $colInstallDate = $findCol(['installationdate', 'dateofinstallation', 'installdate']);
        $colPerformedBy = $findCol(['performedby', 'serviceperformedby', 'engineer', 'technician']);
        $colServiceType = $findCol(['servicetype', 'activity']);
        $colRecordType = $findCol(['recordtype', 'type']);
        $colNextPpmDate = $findCol(['nextppmdate', 'nextduedate', 'nextppm']);
        $colStatus = $findCol(['status', 'state']);
        $colScope = $findCol(['scopeofwork', 'scope']);
        $colRemarks = $findCol(['remarks', 'notes', 'specialnotes']);

        // Cache existing branches, customers, and products for fast ID lookup
        $branchesCache = Branch::all()->keyBy(function ($b) {
            return strtolower(trim($b->name));
        });
        $customersCache = Customer::all()->keyBy(function ($c) {
            return strtolower(trim($c->name));
        });
        $productsCache = Product::all()->keyBy(function ($p) {
            return strtolower(trim($p->name));
        });

        $parseDate = function ($raw) {
            if (empty($raw)) {
                return null;
            }
            if (is_numeric($raw)) {
                try {
                    return Carbon::instance(ExcelDate::excelToDateTimeObject($raw))->toDateString();
                } catch (\Exception $e) {
                    // ignore
                }
            }
            try {
                return Carbon::parse(trim($raw))->toDateString();
            } catch (\Exception $e) {
                return null;
            }
        };

        $imported = 0;
        $skipped = 0;

        // Process data rows
        for ($i = 1; $i < count($rows); $i++) {
            $row = $rows[$i];

            // Resolve Customer Name
            $customerName = '';
            if ($colCustomer !== false && !empty($row[$colCustomer])) {
                $customerName = trim((string)$row[$colCustomer]);
            } elseif (!empty($row[1])) {
                $customerName = trim((string)$row[1]);
            } elseif (!empty($row[0])) {
                $customerName = trim((string)$row[0]);
            }

            if (empty($customerName)) {
                $skipped++;
                continue;
            }

            // Resolve Branch
            $branchId = $fallbackBranchId;
            if ($colBranch !== false && !empty($row[$colBranch])) {
                $bNameKey = strtolower(trim((string)$row[$colBranch]));
                if (isset($branchesCache[$bNameKey])) {
                    $branchId = $branchesCache[$bNameKey]->id;
                }
            }

            // Department & Contacts
            $department = $colDept !== false && isset($row[$colDept]) ? trim((string)$row[$colDept]) : null;
            $contactPerson = $colContactPerson !== false && isset($row[$colContactPerson]) ? trim((string)$row[$colContactPerson]) : null;
            $contactNo = $colContactNo !== false && isset($row[$colContactNo]) ? trim((string)$row[$colContactNo]) : null;

            // Link or auto-register Customer
            $customerId = null;
            $custKey = strtolower($customerName);
            if (isset($customersCache[$custKey])) {
                $customerId = $customersCache[$custKey]->id;
            } else {
                try {
                    $newCust = Customer::create([
                        'branch_id' => $branchId,
                        'name' => $customerName,
                        'department' => $department,
                        'contact_person' => $contactPerson,
                        'contact_no' => $contactNo,
                    ]);
                    $customerId = $newCust->id;
                    $customersCache[$custKey] = $newCust;
                } catch (\Exception $e) {
                    // Ignore duplicate race conditions
                }
            }

            // Product & Equipment
            $productDesc = $colProduct !== false && isset($row[$colProduct]) ? trim((string)$row[$colProduct]) : null;
            $equipmentName = $colEquipment !== false && isset($row[$colEquipment]) ? trim((string)$row[$colEquipment]) : null;
            $modelMake = $colModel !== false && isset($row[$colModel]) ? trim((string)$row[$colModel]) : null;
            $serialNo = $colSerial !== false && isset($row[$colSerial]) ? trim((string)$row[$colSerial]) : null;

            if (empty($equipmentName) && !empty($productDesc)) {
                $equipmentName = $productDesc;
            }
            if (empty($productDesc) && !empty($equipmentName)) {
                $productDesc = $equipmentName;
            }

            // Link or auto-register Product
            $productId = null;
            if (!empty($productDesc)) {
                $prodKey = strtolower($productDesc);
                if (isset($productsCache[$prodKey])) {
                    $productId = $productsCache[$prodKey]->id;
                } else {
                    try {
                        $newProd = Product::create([
                            'name' => $productDesc,
                            'equipment_name' => $equipmentName ?: $productDesc,
                            'model_make' => $modelMake,
                            'default_ppm_days' => 90,
                        ]);
                        $productId = $newProd->id;
                        $productsCache[$prodKey] = $newProd;
                    } catch (\Exception $e) {
                        // Ignore duplicate
                    }
                }
            }

            // Dates & execution fields
            $entryDate = $colEntryDate !== false && isset($row[$colEntryDate]) ? $parseDate($row[$colEntryDate]) : date('Y-m-d');
            $installDate = $colInstallDate !== false && isset($row[$colInstallDate]) ? $parseDate($row[$colInstallDate]) : date('Y-m-d');
            $nextPpmDate = $colNextPpmDate !== false && isset($row[$colNextPpmDate]) ? $parseDate($row[$colNextPpmDate]) : null;

            // If next PPM date not specified, compute from installation date + 90 days
            if (!$nextPpmDate && $installDate) {
                $nextPpmDate = Carbon::parse($installDate)->addDays(90)->toDateString();
            }

            PpmRecord::create([
                'branch_id' => $branchId,
                'customer_id' => $customerId,
                'product_id' => $productId,
                'customer_name' => $customerName,
                'product_description' => $productDesc,
                'equipment_name' => $equipmentName ?: 'Medical Equipment',
                'model_make' => $modelMake,
                'serial_no' => $serialNo,
                'department' => $department ?: 'Biomedical',
                'contact_person' => $contactPerson,
                'contact_no' => $contactNo,
                'purchase_order_no' => $colPO !== false && isset($row[$colPO]) ? trim((string)$row[$colPO]) : null,
                'supply_against' => $colSupply !== false && isset($row[$colSupply]) ? trim((string)$row[$colSupply]) : null,
                'entry_date' => $entryDate ?: date('Y-m-d'),
                'installation_date' => $installDate ?: date('Y-m-d'),
                'performed_by' => $colPerformedBy !== false && isset($row[$colPerformedBy]) ? trim((string)$row[$colPerformedBy]) : 'Engineer',
                'service_type' => $colServiceType !== false && isset($row[$colServiceType]) ? trim((string)$row[$colServiceType]) : 'New Installation',
                'record_type' => $colRecordType !== false && isset($row[$colRecordType]) ? trim((string)$row[$colRecordType]) : 'Installation / PPM',
                'next_ppm_date' => $nextPpmDate,
                'status' => $colStatus !== false && isset($row[$colStatus]) ? trim((string)$row[$colStatus]) : 'Completed',
                'scope_of_work' => $colScope !== false && isset($row[$colScope]) ? trim((string)$row[$colScope]) : null,
                'remarks' => $colRemarks !== false && isset($row[$colRemarks]) ? trim((string)$row[$colRemarks]) : 'Imported via Excel Batch Import',
            ]);

            $imported++;
        }

        $message = "Successfully imported {$imported} PPM record(s) from Excel file.";
        if ($skipped > 0) {
            $message .= " ({$skipped} invalid or empty rows skipped).";
        }

        return redirect()->route('ppm.index')->with('success', $message);
    }

    /**
     * API to check PPM notifications (due today, overdue, upcoming).
     */
    public function checkNotifications()
    {
        $today = now()->toDateString();
        $scopedBase = PpmRecord::forUserBranch();

        $dueToday = (clone $scopedBase)->whereDate('next_ppm_date', $today)
            ->select('id', 'customer_name', 'equipment_name', 'product_description', 'purchase_order_no', 'next_ppm_date', 'department', 'performed_by', 'status')
            ->orderBy('id', 'desc')
            ->get();

        $overdue = (clone $scopedBase)->whereNotNull('next_ppm_date')
            ->whereDate('next_ppm_date', '<', $today)
            ->where(function ($q) {
                $q->where('status', '!=', 'Completed')
                  ->orWhereNull('status');
            })
            ->select('id', 'customer_name', 'equipment_name', 'product_description', 'purchase_order_no', 'next_ppm_date', 'department', 'performed_by', 'status')
            ->orderBy('next_ppm_date', 'desc')
            ->take(15)
            ->get();

        $upcoming = (clone $scopedBase)->whereDate('next_ppm_date', '>', $today)
            ->whereDate('next_ppm_date', '<=', now()->addDays(7)->toDateString())
            ->select('id', 'customer_name', 'equipment_name', 'product_description', 'purchase_order_no', 'next_ppm_date', 'department', 'performed_by', 'status')
            ->orderBy('next_ppm_date', 'asc')
            ->take(15)
            ->get();

        return response()->json([
            'due_today' => $dueToday,
            'due_today_count' => $dueToday->count(),
            'overdue' => $overdue,
            'overdue_count' => $overdue->count(),
            'upcoming' => $upcoming,
            'upcoming_count' => $upcoming->count(),
            'total_alerts' => $dueToday->count() + $overdue->count(),
            'today_date' => $today,
        ]);
    }
}

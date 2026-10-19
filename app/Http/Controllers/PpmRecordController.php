<?php

namespace App\Http\Controllers;

use App\Models\PpmRecord;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Branch;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

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
     * Import PPM Records from CSV file.
     */
    public function importCsv(Request $request)
    {
        $this->authorize('import_ppm');

        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:10240',
        ]);

        $file = $request->file('csv_file');
        $handle = fopen($file->getRealPath(), 'r');
        $header = fgetcsv($handle); // skip header row

        $imported = 0;
        $user = auth()->user();
        $defaultBranchId = ($user && $user->branch_id && !$user->hasAllBranchesAccess()) ? $user->branch_id : null;

        while (($row = fgetcsv($handle)) !== false) {
            if (empty($row[0]) && empty($row[1])) {
                continue;
            }

            // Map columns: Customer Name, Equipment, Model, Serial No, Department, Service Type, PO No, Next PPM Date
            $custName = trim($row[1] ?? $row[0] ?? '');
            if (empty($custName)) {
                continue;
            }

            PpmRecord::create([
                'branch_id' => $defaultBranchId,
                'customer_name' => $custName,
                'equipment_name' => $row[2] ?? $row[1] ?? 'Medical Equipment',
                'model_make' => $row[3] ?? null,
                'serial_no' => $row[4] ?? null,
                'department' => $row[5] ?? 'General',
                'service_type' => $row[6] ?? 'Routine PPM',
                'purchase_order_no' => $row[7] ?? null,
                'entry_date' => now(),
                'status' => 'Pending',
            ]);
            $imported++;
        }

        fclose($handle);

        return redirect()->route('ppm.index')
            ->with('success', "Successfully imported {$imported} PPM record(s) from CSV.");
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

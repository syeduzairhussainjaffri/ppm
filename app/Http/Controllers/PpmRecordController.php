<?php

namespace App\Http\Controllers;

use App\Models\PpmRecord;
use App\Models\Customer;
use App\Models\Product;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PpmRecordController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->input('search');
        $department = $request->input('department');
        $serviceType = $request->input('service_type');
        $ppmStatus = $request->input('ppm_status');
        $today = now()->toDateString();

        $query = PpmRecord::with(['customer', 'product'])->latest();

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

        // Summary counts
        $stats = [
            'total' => PpmRecord::count(),
            'new_installations' => PpmRecord::where('service_type', 'New Installation')->count(),
            'biomedical' => PpmRecord::where('department', 'like', '%Biomedical%')->count(),
            'due_today' => PpmRecord::whereDate('next_ppm_date', $today)->count(),
            'overdue' => PpmRecord::whereNotNull('next_ppm_date')
                ->whereDate('next_ppm_date', '<', $today)
                ->where(function ($q) {
                    $q->where('status', '!=', 'Completed')
                      ->orWhereNull('status');
                })->count(),
            'upcoming_ppm' => PpmRecord::whereNotNull('next_ppm_date')
                ->whereDate('next_ppm_date', '>=', $today)
                ->count(),
            'customers_count' => Customer::count(),
            'products_count' => Product::count(),
        ];

        return view('ppm.index', compact('records', 'stats', 'search', 'department', 'serviceType', 'ppmStatus'));
    }

    /**
     * Show the form for creating a new resource with dynamic customers and products.
     */
    public function create()
    {
        $customers = Customer::orderBy('name')->get();
        $products = Product::orderBy('name')->get();

        return view('ppm.create', compact('customers', 'products'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
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
        $ppm->load(['customer', 'product']);
        return view('ppm.show', ['record' => $ppm]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(PpmRecord $ppm)
    {
        $customers = Customer::orderBy('name')->get();
        $products = Product::orderBy('name')->get();

        return view('ppm.edit', [
            'record' => $ppm,
            'customers' => $customers,
            'products' => $products,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, PpmRecord $ppm)
    {
        $validated = $request->validate([
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
        $ppm->delete();

        return redirect()->route('ppm.index')
            ->with('success', 'Record deleted successfully.');
    }

    /**
     * Export all records to CSV format.
     */
    public function exportCsv(): StreamedResponse
    {
        $records = PpmRecord::with(['customer', 'product'])->get();
        $filename = "installation_ppm_records_" . date('Ymd_His') . ".csv";

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        return response()->stream(function () use ($records) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, [
                'ID',
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
     * API to check PPM notifications (due today, overdue, upcoming).
     */
    public function checkNotifications()
    {
        $today = now()->toDateString();

        $dueToday = PpmRecord::whereDate('next_ppm_date', $today)
            ->select('id', 'customer_name', 'equipment_name', 'product_description', 'purchase_order_no', 'next_ppm_date', 'department', 'performed_by', 'status')
            ->orderBy('id', 'desc')
            ->get();

        $overdue = PpmRecord::whereNotNull('next_ppm_date')
            ->whereDate('next_ppm_date', '<', $today)
            ->where(function ($q) {
                $q->where('status', '!=', 'Completed')
                  ->orWhereNull('status');
            })
            ->select('id', 'customer_name', 'equipment_name', 'product_description', 'purchase_order_no', 'next_ppm_date', 'department', 'performed_by', 'status')
            ->orderBy('next_ppm_date', 'desc')
            ->take(15)
            ->get();

        $upcoming = PpmRecord::whereDate('next_ppm_date', '>', $today)
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

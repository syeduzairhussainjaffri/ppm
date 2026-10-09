<?php

namespace App\Http\Controllers;

use App\Models\PpmRecord;
use App\Models\Customer;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    /**
     * Reports Hub / Dashboard index view with advanced multi-filters and analytics.
     */
    public function index(Request $request)
    {
        $customers = Customer::orderBy('name')->get();
        $products = Product::orderBy('name')->get();

        // Get unique departments for filter dropdown
        $departments = PpmRecord::whereNotNull('department')
            ->where('department', '!=', '')
            ->distinct()
            ->pluck('department')
            ->sort()
            ->values();

        // Get unique service types for filter dropdown
        $serviceTypes = PpmRecord::whereNotNull('service_type')
            ->where('service_type', '!=', '')
            ->distinct()
            ->pluck('service_type')
            ->sort()
            ->values();

        // Build base query with filters
        $query = PpmRecord::with(['customer', 'product'])->latest();

        // Apply filters
        $this->applyReportFilters($query, $request);

        $records = $query->get();

        $today = now()->toDateString();

        // General KPI Metrics
        $summary = [
            'total_records' => $records->count(),
            'new_installations' => $records->where('service_type', 'New Installation')->count(),
            'routine_ppm' => $records->where('service_type', 'Routine PPM')->count(),
            'due_today' => $records->filter(fn($r) => optional($r->next_ppm_date)->toDateString() === $today)->count(),
            'overdue' => $records->filter(function ($r) use ($today) {
                return $r->next_ppm_date && $r->next_ppm_date->toDateString() < $today && strtolower($r->status) !== 'completed';
            })->count(),
            'upcoming_7_days' => $records->filter(function ($r) use ($today) {
                $next = optional($r->next_ppm_date)->toDateString();
                return $next && $next > $today && $next <= now()->addDays(7)->toDateString();
            })->count(),
            'completed' => $records->filter(fn($r) => strtolower($r->status) === 'completed')->count(),
            'pending' => $records->filter(fn($r) => strtolower($r->status) !== 'completed')->count(),
        ];

        // 1. Customer-Wise Summary Aggregates
        $customerSummary = $records->groupBy(function ($r) {
            return $r->customer_name ?: ($r->customer ? $r->customer->name : 'Unassigned Customer');
        })->map(function ($group, $custName) use ($today) {
            return [
                'customer_name' => $custName,
                'total_equipment' => $group->count(),
                'new_installations' => $group->where('service_type', 'New Installation')->count(),
                'due_today' => $group->filter(fn($r) => optional($r->next_ppm_date)->toDateString() === $today)->count(),
                'overdue' => $group->filter(fn($r) => $r->next_ppm_date && $r->next_ppm_date->toDateString() < $today && strtolower($r->status) !== 'completed')->count(),
                'upcoming' => $group->filter(fn($r) => optional($r->next_ppm_date)->toDateString() > $today)->count(),
                'completed' => $group->filter(fn($r) => strtolower($r->status) === 'completed')->count(),
                'last_entry_date' => $group->max('entry_date'),
            ];
        })->sortByDesc('total_equipment')->values();

        // 2. Product/Equipment-Wise Summary Aggregates
        $productSummary = $records->groupBy(function ($r) {
            return $r->equipment_name ?: ($r->product_description ?: ($r->product ? $r->product->name : 'Unspecified Equipment'));
        })->map(function ($group, $prodName) use ($today) {
            return [
                'product_name' => $prodName,
                'total_units' => $group->count(),
                'customers_count' => $group->pluck('customer_name')->unique()->count(),
                'due_today' => $group->filter(fn($r) => optional($r->next_ppm_date)->toDateString() === $today)->count(),
                'overdue' => $group->filter(fn($r) => $r->next_ppm_date && $r->next_ppm_date->toDateString() < $today && strtolower($r->status) !== 'completed')->count(),
                'upcoming' => $group->filter(fn($r) => optional($r->next_ppm_date)->toDateString() > $today)->count(),
                'models' => $group->pluck('model_make')->filter()->unique()->implode(', '),
            ];
        })->sortByDesc('total_units')->values();

        // 3. Department-Wise Summary Aggregates
        $departmentSummary = $records->groupBy(function ($r) {
            return $r->department ?: 'General / Unspecified';
        })->map(function ($group, $deptName) {
            return [
                'department' => $deptName,
                'total_count' => $group->count(),
                'completed' => $group->filter(fn($r) => strtolower($r->status) === 'completed')->count(),
                'pending' => $group->filter(fn($r) => strtolower($r->status) !== 'completed')->count(),
            ];
        })->sortByDesc('total_count')->values();

        // 4. Status Schedule Summary Aggregates
        $statusSummary = [
            'due_today' => $records->filter(fn($r) => optional($r->next_ppm_date)->toDateString() === $today),
            'overdue' => $records->filter(fn($r) => $r->next_ppm_date && $r->next_ppm_date->toDateString() < $today && strtolower($r->status) !== 'completed'),
            'upcoming' => $records->filter(fn($r) => optional($r->next_ppm_date)->toDateString() > $today),
            'completed' => $records->filter(fn($r) => strtolower($r->status) === 'completed'),
        ];

        return view('reports.index', compact(
            'records',
            'summary',
            'customerSummary',
            'productSummary',
            'departmentSummary',
            'statusSummary',
            'customers',
            'products',
            'departments',
            'serviceTypes'
        ));
    }

    /**
     * Dedicated Customer-Wise detailed equipment & PPM report.
     */
    public function customerReport(Request $request)
    {
        $customers = Customer::orderBy('name')->get();
        $selectedCustomerId = $request->input('customer_id');
        $customerSearch = $request->input('search');

        $query = Customer::with(['ppmRecords' => function ($q) {
            $q->latest();
        }]);

        if ($selectedCustomerId) {
            $query->where('id', $selectedCustomerId);
        } elseif ($customerSearch) {
            $query->where('name', 'like', "%{$customerSearch}%")
                  ->orWhere('department', 'like', "%{$customerSearch}%");
        }

        $customerReports = $query->get()->map(function ($cust) {
            $records = $cust->ppmRecords;
            $today = now()->toDateString();

            return [
                'customer' => $cust,
                'records' => $records,
                'total_equipment' => $records->count(),
                'due_today' => $records->filter(fn($r) => optional($r->next_ppm_date)->toDateString() === $today)->count(),
                'overdue' => $records->filter(fn($r) => $r->next_ppm_date && $r->next_ppm_date->toDateString() < $today && strtolower($r->status) !== 'completed')->count(),
                'upcoming' => $records->filter(fn($r) => optional($r->next_ppm_date)->toDateString() > $today)->count(),
                'completed' => $records->filter(fn($r) => strtolower($r->status) === 'completed')->count(),
            ];
        });

        return view('reports.customer', compact('customers', 'customerReports', 'selectedCustomerId', 'customerSearch'));
    }

    /**
     * Dedicated Product-Wise detailed equipment deployment report.
     */
    public function productReport(Request $request)
    {
        $products = Product::orderBy('name')->get();
        $selectedProductId = $request->input('product_id');
        $productSearch = $request->input('search');

        $query = Product::with(['ppmRecords' => function ($q) {
            $q->latest();
        }]);

        if ($selectedProductId) {
            $query->where('id', $selectedProductId);
        } elseif ($productSearch) {
            $query->where('name', 'like', "%{$productSearch}%")
                  ->orWhere('equipment_name', 'like', "%{$productSearch}%")
                  ->orWhere('model_make', 'like', "%{$productSearch}%");
        }

        $productReports = $query->get()->map(function ($prod) {
            $records = $prod->ppmRecords;
            $today = now()->toDateString();

            return [
                'product' => $prod,
                'records' => $records,
                'total_installed' => $records->count(),
                'unique_hospitals' => $records->pluck('customer_name')->unique()->filter()->count(),
                'due_today' => $records->filter(fn($r) => optional($r->next_ppm_date)->toDateString() === $today)->count(),
                'overdue' => $records->filter(fn($r) => $r->next_ppm_date && $r->next_ppm_date->toDateString() < $today && strtolower($r->status) !== 'completed')->count(),
                'upcoming' => $records->filter(fn($r) => optional($r->next_ppm_date)->toDateString() > $today)->count(),
            ];
        });

        return view('reports.product', compact('products', 'productReports', 'selectedProductId', 'productSearch'));
    }

    /**
     * Export filtered report records to CSV sheet.
     */
    public function exportCsv(Request $request): StreamedResponse
    {
        $query = PpmRecord::with(['customer', 'product'])->latest();
        $this->applyReportFilters($query, $request);
        $records = $query->get();

        $filename = "ppm_filtered_report_" . date('Ymd_His') . ".csv";

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        return response()->stream(function () use ($records) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, [
                'Report Ref ID',
                'Customer / Hospital',
                'Department',
                'Contact Person',
                'Contact No',
                'Equipment Name',
                'Model / Make',
                'Serial No',
                'Service Type',
                'PO Number',
                'Installation Date',
                'Next PPM Date',
                'PPM Status',
                'Performed By',
                'Remarks'
            ]);

            foreach ($records as $row) {
                fputcsv($handle, [
                    '#PPM-' . str_pad($row->id, 5, '0', STR_PAD_LEFT),
                    $row->customer_name ?: optional($row->customer)->name,
                    $row->department,
                    $row->contact_person,
                    $row->contact_no,
                    $row->equipment_name ?: $row->product_description,
                    $row->model_make,
                    $row->serial_no,
                    $row->service_type,
                    $row->purchase_order_no,
                    optional($row->installation_date)->format('Y-m-d'),
                    optional($row->next_ppm_date)->format('Y-m-d'),
                    $row->status ?: 'Pending',
                    $row->performed_by,
                    $row->remarks,
                ]);
            }
            fclose($handle);
        }, 200, $headers);
    }

    /**
     * Printable view optimized for print / PDF output.
     */
    public function printReport(Request $request)
    {
        $query = PpmRecord::with(['customer', 'product'])->latest();
        $this->applyReportFilters($query, $request);
        $records = $query->get();

        $reportTitle = $request->input('report_title', 'PPM Field Maintenance & Asset Report');
        $today = now()->toDateString();

        $stats = [
            'total' => $records->count(),
            'due_today' => $records->filter(fn($r) => optional($r->next_ppm_date)->toDateString() === $today)->count(),
            'overdue' => $records->filter(fn($r) => $r->next_ppm_date && $r->next_ppm_date->toDateString() < $today && strtolower($r->status) !== 'completed')->count(),
            'completed' => $records->filter(fn($r) => strtolower($r->status) === 'completed')->count(),
        ];

        return view('reports.print', compact('records', 'reportTitle', 'stats', 'request'));
    }

    /**
     * Helper to apply common report filters to Eloquent query.
     */
    private function applyReportFilters($query, Request $request)
    {
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('customer_name', 'like', "%{$search}%")
                  ->orWhere('equipment_name', 'like', "%{$search}%")
                  ->orWhere('product_description', 'like', "%{$search}%")
                  ->orWhere('purchase_order_no', 'like', "%{$search}%")
                  ->orWhere('serial_no', 'like', "%{$search}%")
                  ->orWhere('performed_by', 'like', "%{$search}%");
            });
        }

        if ($customerId = $request->input('customer_id')) {
            $query->where(function ($q) use ($customerId) {
                $customer = Customer::find($customerId);
                $q->where('customer_id', $customerId);
                if ($customer) {
                    $q->orWhere('customer_name', $customer->name);
                }
            });
        }

        if ($productId = $request->input('product_id')) {
            $query->where(function ($q) use ($productId) {
                $product = Product::find($productId);
                $q->where('product_id', $productId);
                if ($product) {
                    $q->orWhere('product_description', 'like', "%{$product->name}%")
                      ->orWhere('equipment_name', 'like', "%{$product->equipment_name}%");
                }
            });
        }

        if ($department = $request->input('department')) {
            $query->where('department', $department);
        }

        if ($serviceType = $request->input('service_type')) {
            $query->where('service_type', $serviceType);
        }

        // Status Filter
        $status = $request->input('status');
        $today = now()->toDateString();

        if ($status === 'due_today') {
            $query->whereDate('next_ppm_date', $today);
        } elseif ($status === 'overdue') {
            $query->whereNotNull('next_ppm_date')
                  ->whereDate('next_ppm_date', '<', $today)
                  ->where(function ($q) {
                      $q->where('status', '!=', 'Completed')
                        ->orWhereNull('status');
                  });
        } elseif ($status === 'upcoming') {
            $query->whereDate('next_ppm_date', '>', $today);
        } elseif ($status === 'completed') {
            $query->where('status', 'Completed');
        } elseif ($status === 'pending') {
            $query->where(function ($q) {
                $q->where('status', '!=', 'Completed')
                  ->orWhereNull('status');
            });
        }

        // Date Range Filter
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');
        $dateField = $request->input('date_field', 'next_ppm_date');

        if (!in_array($dateField, ['next_ppm_date', 'entry_date', 'installation_date'])) {
            $dateField = 'next_ppm_date';
        }

        if ($dateFrom) {
            $query->whereDate($dateField, '>=', $dateFrom);
        }

        if ($dateTo) {
            $query->whereDate($dateField, '<=', $dateTo);
        }
    }
}

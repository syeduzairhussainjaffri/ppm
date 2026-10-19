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
        $departments = PpmRecord::whereNotNull('department')
            ->where('department', '!=', '')
            ->distinct()
            ->pluck('department')
            ->merge(Customer::whereNotNull('department')->where('department', '!=', '')->distinct()->pluck('department'))
            ->unique()
            ->sort()
            ->values();

        $serviceTypes = PpmRecord::whereNotNull('service_type')
            ->where('service_type', '!=', '')
            ->distinct()
            ->pluck('service_type')
            ->sort()
            ->values();

        $selectedCustomerId = $request->input('customer_id');
        $customerSearch = $request->input('search');

        $query = PpmRecord::with(['customer', 'product'])->latest();
        $this->applyReportFilters($query, $request);
        $records = $query->get();

        $today = now()->toDateString();

        // Calculate Overall Totals across all filtered records
        $totals = [
            'total_customers' => 0,
            'total_equipment' => $records->count(),
            'due_today' => $records->filter(fn($r) => optional($r->next_ppm_date)->toDateString() === $today)->count(),
            'overdue' => $records->filter(fn($r) => $r->next_ppm_date && $r->next_ppm_date->toDateString() < $today && strtolower($r->status) !== 'completed')->count(),
            'upcoming' => $records->filter(fn($r) => optional($r->next_ppm_date)->toDateString() > $today)->count(),
            'completed' => $records->filter(fn($r) => strtolower($r->status) === 'completed')->count(),
            'pending' => $records->filter(fn($r) => strtolower($r->status) !== 'completed')->count(),
        ];

        // Group records by Customer
        $grouped = $records->groupBy(function ($r) {
            return $r->customer_id ? 'id_' . $r->customer_id : 'name_' . trim(strtolower($r->customer_name ?: 'General Customer'));
        });

        $customerReports = $grouped->map(function ($group) use ($today, $customers) {
            $first = $group->first();
            $customer = $first->customer;

            if (!$customer && $first->customer_name) {
                $customer = $customers->first(function ($c) use ($first) {
                    return strcasecmp($c->name, $first->customer_name) === 0;
                });
            }

            $customerName = $customer ? $customer->name : ($first->customer_name ?: 'General Customer');
            $department = $customer ? ($customer->department ?: $first->department) : $first->department;
            $contactPerson = $customer ? ($customer->contact_person ?: $first->contact_person) : $first->contact_person;
            $contactNo = $customer ? ($customer->contact_no ?: $first->contact_no) : $first->contact_no;
            $address = $customer ? $customer->address : null;

            return [
                'customer' => $customer,
                'customer_name' => $customerName,
                'department' => $department,
                'contact_person' => $contactPerson,
                'contact_no' => $contactNo,
                'address' => $address,
                'records' => $group,
                'total_equipment' => $group->count(),
                'due_today' => $group->filter(fn($r) => optional($r->next_ppm_date)->toDateString() === $today)->count(),
                'overdue' => $group->filter(fn($r) => $r->next_ppm_date && $r->next_ppm_date->toDateString() < $today && strtolower($r->status) !== 'completed')->count(),
                'upcoming' => $group->filter(fn($r) => optional($r->next_ppm_date)->toDateString() > $today)->count(),
                'completed' => $group->filter(fn($r) => strtolower($r->status) === 'completed')->count(),
                'pending' => $group->filter(fn($r) => strtolower($r->status) !== 'completed')->count(),
            ];
        })->sortByDesc('total_equipment')->values();

        // If a customer was specifically filtered by ID and had 0 matching records, show them with empty record set
        if ($selectedCustomerId && $customerReports->isEmpty()) {
            $cust = Customer::find($selectedCustomerId);
            if ($cust) {
                $customerReports = collect([[
                    'customer' => $cust,
                    'customer_name' => $cust->name,
                    'department' => $cust->department,
                    'contact_person' => $cust->contact_person,
                    'contact_no' => $cust->contact_no,
                    'address' => $cust->address,
                    'records' => collect(),
                    'total_equipment' => 0,
                    'due_today' => 0,
                    'overdue' => 0,
                    'upcoming' => 0,
                    'completed' => 0,
                    'pending' => 0,
                ]]);
            }
        }

        $totals['total_customers'] = $customerReports->count();

        return view('reports.customer', compact(
            'customers',
            'departments',
            'serviceTypes',
            'customerReports',
            'totals',
            'selectedCustomerId',
            'customerSearch',
            'request'
        ));
    }

    /**
     * Dedicated Product-Wise detailed equipment deployment report.
     */
    public function productReport(Request $request)
    {
        $products = Product::orderBy('name')->get();
        $departments = PpmRecord::whereNotNull('department')
            ->where('department', '!=', '')
            ->distinct()
            ->pluck('department')
            ->merge(Customer::whereNotNull('department')->where('department', '!=', '')->distinct()->pluck('department'))
            ->unique()
            ->sort()
            ->values();

        $serviceTypes = PpmRecord::whereNotNull('service_type')
            ->where('service_type', '!=', '')
            ->distinct()
            ->pluck('service_type')
            ->sort()
            ->values();

        $selectedProductId = $request->input('product_id');
        $productSearch = $request->input('search');

        $query = PpmRecord::with(['customer', 'product'])->latest();
        $this->applyReportFilters($query, $request);
        $records = $query->get();

        $today = now()->toDateString();

        // Calculate Overall Totals across all filtered records
        $totals = [
            'total_products' => 0,
            'total_installed' => $records->count(),
            'unique_hospitals' => $records->pluck('customer_name')->unique()->filter()->count(),
            'due_today' => $records->filter(fn($r) => optional($r->next_ppm_date)->toDateString() === $today)->count(),
            'overdue' => $records->filter(fn($r) => $r->next_ppm_date && $r->next_ppm_date->toDateString() < $today && strtolower($r->status) !== 'completed')->count(),
            'upcoming' => $records->filter(fn($r) => optional($r->next_ppm_date)->toDateString() > $today)->count(),
            'completed' => $records->filter(fn($r) => strtolower($r->status) === 'completed')->count(),
            'pending' => $records->filter(fn($r) => strtolower($r->status) !== 'completed')->count(),
        ];

        // Group records by Product
        $grouped = $records->groupBy(function ($r) {
            return $r->product_id ? 'id_' . $r->product_id : 'name_' . trim(strtolower($r->equipment_name ?: ($r->product_description ?: 'General Equipment')));
        });

        $productReports = $grouped->map(function ($group) use ($today, $products) {
            $first = $group->first();
            $product = $first->product;

            if (!$product && ($first->equipment_name || $first->product_description)) {
                $product = $products->first(function ($p) use ($first) {
                    return strcasecmp($p->name, $first->product_description) === 0
                        || strcasecmp($p->equipment_name, $first->equipment_name) === 0;
                });
            }

            $productName = $product ? $product->name : ($first->equipment_name ?: ($first->product_description ?: 'General Equipment'));
            $equipmentName = $product ? ($product->equipment_name ?: $product->name) : ($first->equipment_name ?: $first->product_description);
            $modelMake = $product ? ($product->model_make ?: $first->model_make) : $first->model_make;
            $defaultPpmDays = $product ? $product->default_ppm_days : null;
            $category = $product ? $product->category : null;

            return [
                'product' => $product,
                'product_name' => $productName,
                'equipment_name' => $equipmentName,
                'model_make' => $modelMake,
                'default_ppm_days' => $defaultPpmDays,
                'category' => $category,
                'records' => $group,
                'total_installed' => $group->count(),
                'unique_hospitals' => $group->pluck('customer_name')->unique()->filter()->count(),
                'due_today' => $group->filter(fn($r) => optional($r->next_ppm_date)->toDateString() === $today)->count(),
                'overdue' => $group->filter(fn($r) => $r->next_ppm_date && $r->next_ppm_date->toDateString() < $today && strtolower($r->status) !== 'completed')->count(),
                'upcoming' => $group->filter(fn($r) => optional($r->next_ppm_date)->toDateString() > $today)->count(),
                'completed' => $group->filter(fn($r) => strtolower($r->status) === 'completed')->count(),
                'pending' => $group->filter(fn($r) => strtolower($r->status) !== 'completed')->count(),
            ];
        })->sortByDesc('total_installed')->values();

        // If a product was specifically filtered by ID and had 0 matching records, show it
        if ($selectedProductId && $productReports->isEmpty()) {
            $prod = Product::find($selectedProductId);
            if ($prod) {
                $productReports = collect([[
                    'product' => $prod,
                    'product_name' => $prod->name,
                    'equipment_name' => $prod->equipment_name ?: $prod->name,
                    'model_make' => $prod->model_make,
                    'default_ppm_days' => $prod->default_ppm_days,
                    'category' => $prod->category,
                    'records' => collect(),
                    'total_installed' => 0,
                    'unique_hospitals' => 0,
                    'due_today' => 0,
                    'overdue' => 0,
                    'upcoming' => 0,
                    'completed' => 0,
                    'pending' => 0,
                ]]);
            }
        }

        $totals['total_products'] = $productReports->count();

        return view('reports.product', compact(
            'products',
            'departments',
            'serviceTypes',
            'productReports',
            'totals',
            'selectedProductId',
            'productSearch',
            'request'
        ));
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
                  ->orWhere('model_make', 'like', "%{$search}%")
                  ->orWhere('purchase_order_no', 'like', "%{$search}%")
                  ->orWhere('serial_no', 'like', "%{$search}%")
                  ->orWhere('department', 'like', "%{$search}%")
                  ->orWhere('contact_person', 'like', "%{$search}%")
                  ->orWhere('performed_by', 'like', "%{$search}%");
            });
        }

        if ($customerId = $request->input('customer_id')) {
            $customer = Customer::find($customerId);
            $query->where(function ($q) use ($customerId, $customer) {
                $q->where('customer_id', $customerId);
                if ($customer) {
                    $q->orWhere('customer_name', $customer->name);
                }
            });
        }

        if ($productId = $request->input('product_id')) {
            $product = Product::find($productId);
            $query->where(function ($q) use ($productId, $product) {
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

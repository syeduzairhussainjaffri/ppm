<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CustomerController extends Controller
{
    /**
     * Display a listing of customers.
     */
    public function index(Request $request)
    {
        $this->authorize('view_customers');

        $search = $request->input('search');
        $query = Customer::forUserBranch()->withCount('ppmRecords')->latest();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('department', 'like', "%{$search}%")
                  ->orWhere('contact_person', 'like', "%{$search}%")
                  ->orWhere('contact_no', 'like', "%{$search}%");
            });
        }

        $customers = $query->paginate(10)->withQueryString();

        return view('customers.index', compact('customers', 'search'));
    }

    /**
     * Show form to create customer.
     */
    public function create()
    {
        $this->authorize('create_customers');
        return view('customers.create');
    }

    /**
     * Store new customer.
     */
    public function store(Request $request)
    {
        $this->authorize('create_customers');

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:customers,name',
            'department' => 'nullable|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'contact_no' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        $user = auth()->user();
        if ($user && $user->branch_id && !$user->hasAllBranchesAccess()) {
            $validated['branch_id'] = $user->branch_id;
        }

        $customer = Customer::create($validated);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'customer' => $customer,
                'message' => 'Customer created successfully'
            ]);
        }

        return redirect()->route('customers.index')
            ->with('success', 'Customer created successfully.');
    }

    /**
     * Display specific customer.
     */
    public function show(Customer $customer)
    {
        $this->authorize('view_customers');
        $customer->load(['ppmRecords' => fn($q) => $q->latest()]);
        return view('customers.show', compact('customer'));
    }

    /**
     * Show edit form.
     */
    public function edit(Customer $customer)
    {
        $this->authorize('edit_customers');
        return view('customers.edit', compact('customer'));
    }

    /**
     * Update customer.
     */
    public function update(Request $request, Customer $customer)
    {
        $this->authorize('edit_customers');

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:customers,name,' . $customer->id,
            'department' => 'nullable|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'contact_no' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        $customer->update($validated);

        return redirect()->route('customers.index')
            ->with('success', 'Customer updated successfully.');
    }

    /**
     * Delete customer.
     */
    public function destroy(Customer $customer)
    {
        $this->authorize('delete_customers');

        if ($customer->ppmRecords()->count() > 0) {
            return redirect()->route('customers.index')
                ->with('error', "Cannot delete customer '{$customer->name}'. There are active PPM service records linked to this customer.");
        }

        $customer->delete();

        return redirect()->route('customers.index')
            ->with('success', 'Customer deleted successfully.');
    }

    /**
     * Export Customers to CSV.
     */
    public function exportCsv(): StreamedResponse
    {
        $this->authorize('export_customers');

        $filename = 'Customers_Directory_' . now()->format('Y-m-d_H-i') . '.csv';

        return response()->streamDownload(function () {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['ID', 'Hospital / Customer Name', 'Department', 'Contact Person', 'Phone', 'Email', 'Address', 'PPM Records Count', 'Created At']);

            Customer::forUserBranch()->withCount('ppmRecords')->chunk(200, function ($customers) use ($handle) {
                foreach ($customers as $c) {
                    fputcsv($handle, [
                        $c->id,
                        $c->name,
                        $c->department ?: 'N/A',
                        $c->contact_person ?: 'N/A',
                        $c->contact_no ?: 'N/A',
                        $c->email ?: 'N/A',
                        $c->address ?: 'N/A',
                        $c->ppm_records_count,
                        $c->created_at?->format('d-M-Y'),
                    ]);
                }
            });

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    /**
     * Import Customers from CSV.
     */
    public function importCsv(Request $request)
    {
        $this->authorize('import_customers');

        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:5120',
        ]);

        $file = $request->file('csv_file');
        $handle = fopen($file->getRealPath(), 'r');
        $header = fgetcsv($handle); // Skip header row

        $imported = 0;
        $user = auth()->user();
        $defaultBranchId = ($user && $user->branch_id && !$user->hasAllBranchesAccess()) ? $user->branch_id : null;

        while (($row = fgetcsv($handle)) !== false) {
            if (empty($row[0]) && empty($row[1])) {
                continue;
            }

            // Expected format: Name, Department, Contact Person, Phone, Email, Address
            $name = trim($row[0] ?? $row[1] ?? '');
            if (empty($name)) {
                continue;
            }

            Customer::firstOrCreate(
                ['name' => $name],
                [
                    'department' => $row[1] ?? null,
                    'contact_person' => $row[2] ?? null,
                    'contact_no' => $row[3] ?? null,
                    'email' => $row[4] ?? null,
                    'address' => $row[5] ?? null,
                    'branch_id' => $defaultBranchId,
                ]
            );
            $imported++;
        }

        fclose($handle);

        return redirect()->route('customers.index')
            ->with('success', "Successfully imported {$imported} customer(s) from CSV.");
    }
}

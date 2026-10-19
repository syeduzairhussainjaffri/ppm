<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ProductController extends Controller
{
    /**
     * Display a listing of products.
     */
    public function index(Request $request)
    {
        $this->authorize('view_products');

        $search = $request->input('search');
        $query = Product::withCount('ppmRecords')->latest();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('equipment_name', 'like', "%{$search}%")
                  ->orWhere('model_make', 'like', "%{$search}%")
                  ->orWhere('category', 'like', "%{$search}%");
            });
        }

        $products = $query->paginate(10)->withQueryString();

        return view('products.index', compact('products', 'search'));
    }

    /**
     * Show create form.
     */
    public function create()
    {
        $this->authorize('create_products');
        return view('products.create');
    }

    /**
     * Store new product.
     */
    public function store(Request $request)
    {
        $this->authorize('create_products');

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'equipment_name' => 'nullable|string|max:255',
            'model_make' => 'nullable|string|max:255',
            'category' => 'nullable|string|max:255',
            'default_ppm_days' => 'nullable|integer|min:1',
            'description' => 'nullable|string',
        ]);

        $product = Product::create($validated);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'product' => $product,
                'message' => 'Product created successfully'
            ]);
        }

        return redirect()->route('products.index')
            ->with('success', 'Product created successfully.');
    }

    /**
     * Display specific product.
     */
    public function show(Product $product)
    {
        $this->authorize('view_products');
        $product->load(['ppmRecords' => fn($q) => $q->latest()]);
        return view('products.show', compact('product'));
    }

    /**
     * Show edit form.
     */
    public function edit(Product $product)
    {
        $this->authorize('edit_products');
        return view('products.edit', compact('product'));
    }

    /**
     * Update product.
     */
    public function update(Request $request, Product $product)
    {
        $this->authorize('edit_products');

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'equipment_name' => 'nullable|string|max:255',
            'model_make' => 'nullable|string|max:255',
            'category' => 'nullable|string|max:255',
            'default_ppm_days' => 'nullable|integer|min:1',
            'description' => 'nullable|string',
        ]);

        $product->update($validated);

        return redirect()->route('products.index')
            ->with('success', 'Product updated successfully.');
    }

    /**
     * Delete product.
     */
    public function destroy(Product $product)
    {
        $this->authorize('delete_products');

        if ($product->ppmRecords()->count() > 0) {
            return redirect()->route('products.index')
                ->with('error', "Cannot delete product '{$product->name}'. It is currently referenced in active PPM service vouchers.");
        }

        $product->delete();

        return redirect()->route('products.index')
            ->with('success', 'Product deleted successfully.');
    }

    /**
     * Export Products to CSV.
     */
    public function exportCsv(): StreamedResponse
    {
        $this->authorize('export_products');

        $filename = 'Products_Catalog_' . now()->format('Y-m-d_H-i') . '.csv';

        return response()->streamDownload(function () {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['ID', 'Product Name', 'Equipment Name', 'Model / Make', 'Category', 'Default PPM Days', 'Active PPM Records', 'Description']);

            Product::withCount('ppmRecords')->chunk(200, function ($products) use ($handle) {
                foreach ($products as $p) {
                    fputcsv($handle, [
                        $p->id,
                        $p->name,
                        $p->equipment_name ?: 'N/A',
                        $p->model_make ?: 'N/A',
                        $p->category ?: 'General',
                        $p->default_ppm_days ?: 180,
                        $p->ppm_records_count,
                        $p->description ?: '',
                    ]);
                }
            });

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    /**
     * Import Products from CSV.
     */
    public function importCsv(Request $request)
    {
        $this->authorize('import_products');

        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:5120',
        ]);

        $file = $request->file('csv_file');
        $handle = fopen($file->getRealPath(), 'r');
        $header = fgetcsv($handle); // Skip header

        $imported = 0;
        while (($row = fgetcsv($handle)) !== false) {
            $name = trim($row[0] ?? $row[1] ?? '');
            if (empty($name)) {
                continue;
            }

            Product::firstOrCreate(
                ['name' => $name],
                [
                    'equipment_name' => $row[1] ?? $name,
                    'model_make' => $row[2] ?? null,
                    'category' => $row[3] ?? 'General',
                    'default_ppm_days' => !empty($row[4]) ? (int) $row[4] : 180,
                    'description' => $row[5] ?? null,
                ]
            );
            $imported++;
        }

        fclose($handle);

        return redirect()->route('products.index')
            ->with('success', "Successfully imported {$imported} product model(s) from CSV.");
    }
}

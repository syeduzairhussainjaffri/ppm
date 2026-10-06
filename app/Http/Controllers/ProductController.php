<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Display a listing of products.
     */
    public function index(Request $request)
    {
        $search = $request->input('search');
        $query = Product::withCount('ppmRecords')->latest();

        if ($search) {
            $query->where('name', 'like', "%{$search}%")
                  ->orWhere('equipment_name', 'like', "%{$search}%")
                  ->orWhere('model_make', 'like', "%{$search}%")
                  ->orWhere('category', 'like', "%{$search}%");
        }

        $products = $query->paginate(10)->withQueryString();

        return view('products.index', compact('products', 'search'));
    }

    /**
     * Show create form.
     */
    public function create()
    {
        return view('products.create');
    }

    /**
     * Store new product.
     */
    public function store(Request $request)
    {
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
        $product->load('ppmRecords');
        return view('products.show', compact('product'));
    }

    /**
     * Show edit form.
     */
    public function edit(Product $product)
    {
        return view('products.edit', compact('product'));
    }

    /**
     * Update product.
     */
    public function update(Request $request, Product $product)
    {
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
        $product->delete();

        return redirect()->route('products.index')
            ->with('success', 'Product deleted successfully.');
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    /**
     * Display a listing of customers.
     */
    public function index(Request $request)
    {
        $search = $request->input('search');
        $query = Customer::withCount('ppmRecords')->latest();

        if ($search) {
            $query->where('name', 'like', "%{$search}%")
                  ->orWhere('department', 'like', "%{$search}%")
                  ->orWhere('contact_person', 'like', "%{$search}%")
                  ->orWhere('contact_no', 'like', "%{$search}%");
        }

        $customers = $query->paginate(10)->withQueryString();

        return view('customers.index', compact('customers', 'search'));
    }

    /**
     * Show form to create customer.
     */
    public function create()
    {
        return view('customers.create');
    }

    /**
     * Store new customer.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:customers,name',
            'department' => 'nullable|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'contact_no' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

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
        $customer->load('ppmRecords');
        return view('customers.show', compact('customer'));
    }

    /**
     * Show edit form.
     */
    public function edit(Customer $customer)
    {
        return view('customers.edit', compact('customer'));
    }

    /**
     * Update customer.
     */
    public function update(Request $request, Customer $customer)
    {
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
        $customer->delete();

        return redirect()->route('customers.index')
            ->with('success', 'Customer deleted successfully.');
    }
}

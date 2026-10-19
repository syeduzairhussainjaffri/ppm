<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class BranchController extends Controller
{
    /**
     * Store a newly created branch.
     */
    public function store(Request $request)
    {
        $this->authorize('create_branches');

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:50', 'unique:branches,code'],
            'city' => ['nullable', 'string', 'max:100'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:100'],
            'address' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validated['code'] = strtoupper(trim($validated['code']));
        $validated['is_active'] = $request->has('is_active') ? (bool) $validated['is_active'] : true;

        Branch::create($validated);

        return redirect()->route('users.index', ['tab' => 'branches'])
            ->with('success', "Branch '{$validated['name']}' ({$validated['code']}) created successfully.");
    }

    /**
     * Update specified branch.
     */
    public function update(Request $request, Branch $branch)
    {
        $this->authorize('edit_branches');

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:50', Rule::unique('branches')->ignore($branch->id)],
            'city' => ['nullable', 'string', 'max:100'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:100'],
            'address' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validated['code'] = strtoupper(trim($validated['code']));
        $validated['is_active'] = $request->has('is_active') ? (bool) $validated['is_active'] : true;

        $branch->update($validated);

        return redirect()->route('users.index', ['tab' => 'branches'])
            ->with('success', "Branch '{$branch->name}' updated successfully.");
    }

    /**
     * Toggle branch active status.
     */
    public function toggleStatus(Branch $branch)
    {
        $this->authorize('edit_branches');

        $branch->is_active = !$branch->is_active;
        $branch->save();

        $statusStr = $branch->is_active ? 'activated' : 'deactivated';
        return redirect()->route('users.index', ['tab' => 'branches'])
            ->with('success', "Branch '{$branch->name}' {$statusStr} successfully.");
    }

    /**
     * Delete branch if safe.
     */
    public function destroy(Branch $branch)
    {
        $this->authorize('delete_branches');

        $usersCount = $branch->users()->count();
        $recordsCount = $branch->ppmRecords()->count();

        if ($usersCount > 0 || $recordsCount > 0) {
            return redirect()->route('users.index', ['tab' => 'branches'])
                ->with('error', "Cannot delete branch '{$branch->name}'. It is currently linked with {$usersCount} user(s) and {$recordsCount} PPM record(s). Please reassign or deactivate the branch instead.");
        }

        $name = $branch->name;
        $branch->delete();

        return redirect()->route('users.index', ['tab' => 'branches'])
            ->with('success', "Branch '{$name}' deleted successfully.");
    }

    /**
     * Switch active branch filter in session for multi-branch users.
     */
    public function switchBranch(Request $request)
    {
        $user = auth()->user();

        if (!$user->hasAllBranchesAccess()) {
            return back()->with('error', 'You are scoped to your assigned branch and cannot switch branches.');
        }

        $branchId = $request->input('branch_id');

        if ($branchId === 'all' || empty($branchId)) {
            session()->forget('selected_branch_id');
            return back()->with('success', 'Viewing data across All Branches (HQ).');
        }

        $branch = Branch::findOrFail($branchId);
        session(['selected_branch_id' => $branch->id]);

        return back()->with('success', "Switched branch view to: {$branch->name} ({$branch->code})");
    }
}

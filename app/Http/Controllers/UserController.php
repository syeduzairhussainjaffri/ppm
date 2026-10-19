<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use App\Models\Permission;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules;

class UserController extends Controller
{
    /**
     * Display Unified User, Role, Permission & Branch Management Center.
     */
    public function index(Request $request)
    {
        $this->authorize('view_users');

        $search = $request->input('search');
        $roleFilter = $request->input('role');
        $branchFilter = $request->input('branch');
        $activeTab = $request->input('tab', 'users');

        // 1. Users Query with Branch & Role
        $usersQuery = User::with(['branch'])->latest();

        if ($search) {
            $usersQuery->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($roleFilter) {
            $usersQuery->where('role', $roleFilter);
        }

        if ($branchFilter) {
            if ($branchFilter === 'none') {
                $usersQuery->whereNull('branch_id');
            } else {
                $usersQuery->where('branch_id', $branchFilter);
            }
        }

        $users = $usersQuery->paginate(15)->withQueryString();

        // 2. Roles Query (with associated permissions)
        $roles = Role::with('permissions')->orderBy('name')->get();

        // 3. Permissions Query (grouped by module category)
        $permissions = Permission::orderBy('group_name')->orderBy('name')->get();
        $groupedPermissions = $permissions->groupBy('group_name');

        // 4. Branches Query with user and record counts
        $branches = Branch::withCount(['users', 'ppmRecords'])->orderBy('name')->get();

        $stats = [
            'total_users' => User::count(),
            'total_roles' => Role::count(),
            'total_permissions' => Permission::count(),
            'total_branches' => Branch::count(),
            'active_users' => User::where('is_active', true)->count(),
            'active_branches' => Branch::where('is_active', true)->count(),
        ];

        return view('users.index', compact(
            'users',
            'roles',
            'permissions',
            'groupedPermissions',
            'branches',
            'stats',
            'search',
            'roleFilter',
            'branchFilter',
            'activeTab'
        ));
    }

    /**
     * Store a newly created user account.
     */
    public function store(Request $request)
    {
        $this->authorize('create_users');

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', Rules\Password::defaults()],
            'role' => ['required', 'string'],
            'branch_id' => ['nullable', 'exists:branches,id'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        User::create([
            'name' => $validated['name'],
            'email' => strtolower($validated['email']),
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
            'branch_id' => $validated['branch_id'] ?? null,
            'is_active' => $request->has('is_active') ? (bool) $validated['is_active'] : true,
        ]);

        return redirect()->route('users.index', ['tab' => 'users'])->with('success', "User account for {$validated['name']} created successfully.");
    }

    /**
     * Update specified user account.
     */
    public function update(Request $request, User $user)
    {
        $this->authorize('edit_users');

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'role' => ['required', 'string'],
            'branch_id' => ['nullable', 'exists:branches,id'],
            'password' => ['nullable', Rules\Password::defaults()],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $data = [
            'name' => $validated['name'],
            'email' => strtolower($validated['email']),
            'role' => $validated['role'],
            'branch_id' => $validated['branch_id'] ?? null,
            'is_active' => $request->has('is_active') ? (bool) $validated['is_active'] : $user->is_active,
        ];

        if (!empty($validated['password'])) {
            $data['password'] = Hash::make($validated['password']);
        }

        $user->update($data);

        return redirect()->route('users.index', ['tab' => 'users'])->with('success', "User {$user->name} updated successfully.");
    }

    /**
     * Toggle active/deactive status of user.
     */
    public function toggleStatus(User $user)
    {
        $this->authorize('edit_users');

        if ($user->id === auth()->id()) {
            return redirect()->route('users.index', ['tab' => 'users'])->with('error', 'You cannot deactivate your own account.');
        }

        $user->update(['is_active' => !$user->is_active]);

        $status = $user->is_active ? 'activated' : 'deactivated';
        return redirect()->route('users.index', ['tab' => 'users'])->with('success', "User {$user->name} has been {$status}.");
    }

    /**
     * Delete user account.
     */
    public function destroy(User $user)
    {
        $this->authorize('delete_users');

        if ($user->id === auth()->id()) {
            return redirect()->route('users.index', ['tab' => 'users'])->with('error', 'You cannot delete your own account.');
        }

        $name = $user->name;
        $user->delete();

        return redirect()->route('users.index', ['tab' => 'users'])->with('success', "User {$name} deleted successfully.");
    }

    /**
     * Create a new custom Role and assign permissions matrix.
     */
    public function storeRole(Request $request)
    {
        $this->authorize('manage_roles');

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:500'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['exists:permissions,id'],
        ]);

        $slug = Str::slug($validated['name'], '_');

        if (Role::where('slug', $slug)->exists()) {
            return redirect()->route('users.index', ['tab' => 'roles'])->with('error', 'A role with this name already exists.');
        }

        $role = Role::create([
            'name' => $validated['name'],
            'slug' => $slug,
            'description' => $validated['description'] ?? null,
            'is_system' => false,
        ]);

        if (!empty($validated['permissions'])) {
            $role->permissions()->sync($validated['permissions']);
        }

        return redirect()->route('users.index', ['tab' => 'roles'])->with('success', "Custom role '{$role->name}' created successfully with assigned permissions.");
    }

    /**
     * Update an existing Role and its permissions matrix.
     */
    public function updateRole(Request $request, Role $role)
    {
        $this->authorize('manage_roles');

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:500'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['exists:permissions,id'],
        ]);

        $data = [
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
        ];

        // If not system role, allow updating slug
        if (!$role->is_system) {
            $data['slug'] = Str::slug($validated['name'], '_');
        }

        $role->update($data);

        // Sync permissions checkboxes
        $permissions = $validated['permissions'] ?? [];
        $role->permissions()->sync($permissions);

        return redirect()->route('users.index', ['tab' => 'roles'])->with('success', "Role '{$role->name}' and permission matrix updated successfully.");
    }

    /**
     * Delete a custom Role.
     */
    public function destroyRole(Role $role)
    {
        $this->authorize('manage_roles');

        if ($role->is_system) {
            return redirect()->route('users.index', ['tab' => 'roles'])->with('error', 'System protected core roles cannot be deleted.');
        }

        $name = $role->name;
        $role->delete();

        return redirect()->route('users.index', ['tab' => 'roles'])->with('success', "Role '{$name}' deleted successfully.");
    }

    /**
     * Create a new custom Permission.
     */
    public function storePermission(Request $request)
    {
        $this->authorize('manage_permissions');

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'group_name' => ['required', 'string', 'max:255'],
        ]);

        $slug = Str::slug($validated['name'], '_');

        if (Permission::where('slug', $slug)->exists()) {
            return redirect()->route('users.index', ['tab' => 'permissions'])->with('error', 'A permission with this slug already exists.');
        }

        Permission::create([
            'name' => $validated['name'],
            'slug' => $slug,
            'group_name' => $validated['group_name'],
        ]);

        return redirect()->route('users.index', ['tab' => 'permissions'])->with('success', "Custom permission '{$validated['name']}' created successfully.");
    }

    /**
     * Delete a Permission.
     */
    public function destroyPermission(Permission $permission)
    {
        $this->authorize('manage_permissions');

        $name = $permission->name;
        $permission->delete();

        return redirect()->route('users.index', ['tab' => 'permissions'])->with('success', "Permission '{$name}' deleted successfully.");
    }
}

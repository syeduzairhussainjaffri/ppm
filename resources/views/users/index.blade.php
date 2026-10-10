@extends('ppm.layout')

@section('title', 'User & Access Management Center - BioMed CRM')
@section('page_category', 'User & Access Control')

@section('content')
<div class="space-y-6">

    <!-- Top Header Banner -->
    <div class="bg-gradient-to-r from-slate-900 via-slate-800 to-indigo-950 rounded-2xl p-5 text-white shadow-xl flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center space-x-2">
                <span class="bg-indigo-500/20 text-indigo-300 text-[10px] font-extrabold uppercase px-2.5 py-1 rounded-full border border-indigo-500/40 tracking-wider">
                    <i class="fa-solid fa-user-shield mr-1"></i> Unified Access Control Hub
                </span>
            </div>
            <h1 class="text-2xl font-black text-white mt-1.5 tracking-tight">User &amp; Access Management Center</h1>
            <p class="text-xs text-slate-300 mt-1 max-w-xl">
                Unified control panel to manage User Accounts, custom Roles, and fine-grained Permission Matrix assignments.
            </p>
        </div>

        <div class="flex items-center space-x-2">
            <button onclick="document.getElementById('newUserModal').classList.remove('hidden')" class="px-3.5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs shadow-md transition flex items-center">
                <i class="fa-solid fa-user-plus mr-1.5"></i> Add User
            </button>
            <button onclick="document.getElementById('newRoleModal').classList.remove('hidden')" class="px-3.5 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 font-bold text-xs shadow-md transition flex items-center">
                <i class="fa-solid fa-shield-halved mr-1.5 text-teal-400"></i> Add Role
            </button>
        </div>
    </div>

    <!-- Summary KPI Cards -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
        <div class="bg-white rounded-xl p-3.5 border border-slate-200 shadow-xs">
            <span class="text-slate-500 text-xs font-semibold block">Total User Accounts</span>
            <p class="text-xl font-black text-slate-900 mt-0.5">{{ number_format($stats['total_users']) }}</p>
        </div>

        <div class="bg-white rounded-xl p-3.5 border border-slate-200 shadow-xs">
            <span class="text-indigo-700 text-xs font-bold block">Active Roles</span>
            <p class="text-xl font-black text-indigo-800 mt-0.5">{{ number_format($stats['total_roles']) }}</p>
        </div>

        <div class="bg-white rounded-xl p-3.5 border border-slate-200 shadow-xs">
            <span class="text-teal-700 text-xs font-bold block">System Permissions</span>
            <p class="text-xl font-black text-teal-800 mt-0.5">{{ number_format($stats['total_permissions']) }}</p>
        </div>

        <div class="bg-white rounded-xl p-3.5 border border-slate-200 shadow-xs">
            <span class="text-emerald-700 text-xs font-bold block">Active Users</span>
            <p class="text-xl font-black text-emerald-800 mt-0.5">{{ number_format($stats['active_users']) }}</p>
        </div>
    </div>

    <!-- Main Unified Module Tab Navigation -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        
        <div class="border-b border-slate-200 bg-slate-50 p-2 flex flex-wrap gap-2 text-xs font-bold">
            <button onclick="switchAccessTab('users')" id="btn_access_users" class="py-2.5 px-4 rounded-xl transition flex items-center">
                <i class="fa-solid fa-users mr-2 text-indigo-600"></i> 👥 User Accounts ({{ $users->total() }})
            </button>
            <button onclick="switchAccessTab('roles')" id="btn_access_roles" class="py-2.5 px-4 rounded-xl transition flex items-center">
                <i class="fa-solid fa-shield-halved mr-2 text-amber-600"></i> 🛡️ Roles &amp; Permissions Matrix ({{ $roles->count() }})
            </button>
            <button onclick="switchAccessTab('permissions')" id="btn_access_permissions" class="py-2.5 px-4 rounded-xl transition flex items-center">
                <i class="fa-solid fa-key mr-2 text-teal-600"></i> 🔑 Permissions Catalog ({{ $permissions->count() }})
            </button>
        </div>

        <div class="p-4 sm:p-5">
            
            <!-- TAB 1: USER ACCOUNTS MANAGEMENT -->
            <div id="tab_content_users" class="space-y-4">
                
                <!-- Search & Role Filter Bar -->
                <form action="{{ route('users.index') }}" method="GET" class="flex flex-col sm:flex-row gap-3 items-center justify-between text-xs">
                    <input type="hidden" name="tab" value="users">
                    <div class="flex-1 flex flex-col sm:flex-row gap-3 w-full">
                        <div class="w-full sm:w-72">
                            <input type="text" name="search" value="{{ $search }}" placeholder="Search user name or email..." class="w-full py-2 px-3 rounded-xl border border-slate-300 text-xs">
                        </div>
                        <div class="w-full sm:w-48">
                            <select name="role" onchange="this.form.submit()" class="w-full py-2 px-3 rounded-xl border border-slate-300 text-xs font-semibold">
                                <option value="">-- All Roles --</option>
                                @foreach($roles as $r)
                                    <option value="{{ $r->slug }}" {{ $roleFilter == $r->slug ? 'selected' : '' }}>{{ $r->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="flex items-center space-x-2 shrink-0">
                        <button type="submit" class="py-2 px-4 rounded-xl bg-slate-900 text-white font-bold text-xs">Filter Users</button>
                        @if($search || $roleFilter)
                            <a href="{{ route('users.index', ['tab' => 'users']) }}" class="py-2 px-3 rounded-xl border border-slate-300 text-slate-600 hover:bg-slate-50 text-xs">Clear</a>
                        @endif
                    </div>
                </form>

                <!-- Users Data Table -->
                <div class="overflow-x-auto border border-slate-200 rounded-xl">
                    <table class="w-full text-left text-xs text-slate-700">
                        <thead class="bg-slate-100 uppercase text-[11px] font-bold text-slate-600 border-b border-slate-200">
                            <tr>
                                <th class="p-3.5">User Details</th>
                                <th class="p-3.5">Assigned Role</th>
                                <th class="p-3.5">Account Status</th>
                                <th class="p-3.5">Registered Date</th>
                                <th class="p-3.5 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200">
                            @forelse($users as $u)
                                <tr class="hover:bg-slate-50 transition">
                                    <td class="p-3.5 font-bold text-slate-900 flex items-center space-x-3">
                                        <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-slate-800 to-slate-700 text-white font-bold flex items-center justify-center text-sm shrink-0 uppercase">
                                            {{ substr($u->name, 0, 2) }}
                                        </div>
                                        <div>
                                            <div class="font-bold text-slate-900 text-sm leading-tight">{{ $u->name }}</div>
                                            <div class="text-[11px] text-slate-500 font-mono">{{ $u->email }}</div>
                                        </div>
                                    </td>
                                    <td class="p-3.5">
                                        @php
                                            $userRoleObj = $roles->firstWhere('slug', $u->role);
                                            $roleDisplayName = $userRoleObj ? $userRoleObj->name : ucfirst($u->role);
                                        @endphp
                                        @if($u->role === 'admin')
                                            <span class="px-2.5 py-1 bg-rose-100 text-rose-900 border border-rose-200 rounded-lg font-extrabold text-[10px] uppercase">
                                                <i class="fa-solid fa-shield-halved mr-1 text-rose-600"></i> {{ $roleDisplayName }}
                                            </span>
                                        @elseif($u->role === 'manager')
                                            <span class="px-2.5 py-1 bg-amber-100 text-amber-900 border border-amber-200 rounded-lg font-extrabold text-[10px] uppercase">
                                                <i class="fa-solid fa-briefcase mr-1 text-amber-600"></i> {{ $roleDisplayName }}
                                            </span>
                                        @elseif($u->role === 'technician')
                                            <span class="px-2.5 py-1 bg-teal-100 text-teal-900 border border-teal-200 rounded-lg font-extrabold text-[10px] uppercase">
                                                <i class="fa-solid fa-wrench mr-1 text-teal-600"></i> {{ $roleDisplayName }}
                                            </span>
                                        @else
                                            <span class="px-2.5 py-1 bg-indigo-100 text-indigo-900 border border-indigo-200 rounded-lg font-extrabold text-[10px] uppercase">
                                                <i class="fa-solid fa-user-tag mr-1 text-indigo-600"></i> {{ $roleDisplayName }}
                                            </span>
                                        @endif
                                    </td>
                                    <td class="p-3.5">
                                        @if($u->is_active)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mr-1.5"></span> Active
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800">
                                                <span class="w-1.5 h-1.5 rounded-full bg-rose-500 mr-1.5"></span> Deactivated
                                            </span>
                                        @endif
                                    </td>
                                    <td class="p-3.5 text-slate-500 font-mono text-[11px]">
                                        {{ $u->created_at->format('d-M-Y') }}
                                    </td>
                                    <td class="p-3.5 text-right space-x-1.5">
                                        <button onclick="openEditUserModal({{ json_encode($u) }})" class="px-2.5 py-1 rounded bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-[11px] transition">
                                            <i class="fa-solid fa-pen-to-square mr-1"></i> Edit
                                        </button>

                                        @if($u->id !== auth()->id())
                                            <form action="{{ route('users.toggle', $u) }}" method="POST" class="inline-block">
                                                @csrf
                                                <button type="submit" class="px-2.5 py-1 rounded {{ $u->is_active ? 'bg-amber-50 text-amber-700 hover:bg-amber-100' : 'bg-emerald-50 text-emerald-700 hover:bg-emerald-100' }} font-bold text-[11px] transition">
                                                    {{ $u->is_active ? 'Deactivate' : 'Activate' }}
                                                </button>
                                            </form>

                                            <form action="{{ route('users.destroy', $u) }}" method="POST" class="inline-block" onsubmit="return confirm('Delete user {{ $u->name }}?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="px-2 py-1 rounded bg-rose-50 text-rose-600 hover:bg-rose-100 font-bold text-[11px] transition">
                                                    <i class="fa-solid fa-trash"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="p-6 text-center text-slate-400">No user accounts found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="p-3 bg-slate-50 border border-slate-200 rounded-xl">
                    {{ $users->links() }}
                </div>
            </div>

            <!-- TAB 2: ROLES & PERMISSION MATRIX MANAGEMENT -->
            <div id="tab_content_roles" class="hidden space-y-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-bold text-slate-900">Roles &amp; Permission Checkbox Matrix</h3>
                        <p class="text-xs text-slate-500">Create custom user roles and assign fine-grained module permission checkboxes.</p>
                    </div>
                    <button onclick="document.getElementById('newRoleModal').classList.remove('hidden')" class="px-3.5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs shadow-xs">
                        <i class="fa-solid fa-plus mr-1"></i> Add Custom Role
                    </button>
                </div>

                <!-- Roles Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @foreach($roles as $roleObj)
                        <div class="bg-slate-50 rounded-2xl p-4 border border-slate-200 flex flex-col justify-between space-y-3">
                            <div>
                                <div class="flex items-center justify-between mb-1.5">
                                    <div class="flex items-center space-x-2">
                                        <h4 class="font-extrabold text-slate-900 text-sm">{{ $roleObj->name }}</h4>
                                        <span class="text-[10px] font-mono text-slate-400 bg-white px-2 py-0.5 rounded border border-slate-200">{{ $roleObj->slug }}</span>
                                    </div>
                                    @if($roleObj->is_system)
                                        <span class="bg-rose-100 text-rose-800 text-[10px] font-extrabold px-2 py-0.5 rounded">System Protected</span>
                                    @else
                                        <span class="bg-teal-100 text-teal-800 text-[10px] font-extrabold px-2 py-0.5 rounded">Custom Role</span>
                                    @endif
                                </div>

                                <p class="text-xs text-slate-600 leading-relaxed mb-3">{{ $roleObj->description ?: 'No description provided.' }}</p>

                                <!-- Assigned Permissions Badges -->
                                <div>
                                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block mb-1.5">
                                        Assigned Permissions ({{ $roleObj->permissions->count() }}):
                                    </span>
                                    <div class="flex flex-wrap gap-1 max-h-24 overflow-y-auto pr-1">
                                        @forelse($roleObj->permissions as $p)
                                            <span class="bg-white text-slate-800 border border-slate-300 text-[10px] font-semibold px-2 py-0.5 rounded">
                                                {{ $p->name }}
                                            </span>
                                        @empty
                                            <span class="text-[11px] text-slate-400 italic">No permissions assigned.</span>
                                        @endforelse
                                    </div>
                                </div>
                            </div>

                            <div class="pt-3 border-t border-slate-200 flex items-center justify-between text-xs">
                                <span class="text-slate-500 font-medium">Users Count: <strong>{{ $roleObj->users->count() }}</strong></span>
                                <div class="flex items-center space-x-2">
                                    <button onclick="openEditRoleModal({{ json_encode($roleObj->load('permissions')) }})" class="px-3 py-1 bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs rounded-lg transition">
                                        <i class="fa-solid fa-sliders mr-1"></i> Edit Matrix
                                    </button>

                                    @if(!$roleObj->is_system)
                                        <form action="{{ route('roles.destroy', $roleObj) }}" method="POST" onsubmit="return confirm('Delete custom role {{ $roleObj->name }}?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="px-2 py-1 bg-rose-50 hover:bg-rose-100 text-rose-600 font-bold text-xs rounded-lg transition">
                                                <i class="fa-solid fa-trash"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- TAB 3: PERMISSIONS CATALOG -->
            <div id="tab_content_permissions" class="hidden space-y-4">
                <div class="flex items-center justify-between mb-3">
                    <div>
                        <h3 class="text-sm font-bold text-slate-900">System Permissions Catalog</h3>
                        <p class="text-xs text-slate-500">List of all system capability slugs grouped by module category.</p>
                    </div>
                    <button onclick="document.getElementById('newPermissionModal').classList.remove('hidden')" class="px-3.5 py-2 rounded-xl bg-teal-600 hover:bg-teal-700 text-white font-bold text-xs shadow-xs">
                        <i class="fa-solid fa-plus mr-1"></i> Add Permission
                    </button>
                </div>

                <div class="space-y-4">
                    @foreach($groupedPermissions as $groupName => $perms)
                        <div class="bg-slate-50 rounded-xl p-4 border border-slate-200">
                            <h4 class="font-bold text-xs uppercase tracking-wider text-teal-800 mb-2 flex items-center">
                                <i class="fa-solid fa-layer-group mr-1.5"></i> Module: {{ $groupName }} ({{ $perms->count() }})
                            </h4>
                            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-2">
                                @foreach($perms as $perm)
                                    <div class="bg-white p-2.5 rounded-lg border border-slate-200 flex items-center justify-between">
                                        <div>
                                            <p class="font-bold text-slate-900 text-xs">{{ $perm->name }}</p>
                                            <p class="font-mono text-[10px] text-slate-400">{{ $perm->slug }}</p>
                                        </div>
                                        <form action="{{ route('permissions.destroy', $perm) }}" method="POST" onsubmit="return confirm('Delete permission {{ $perm->name }}?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-slate-300 hover:text-rose-600 text-xs">
                                                <i class="fa-solid fa-xmark"></i>
                                            </button>
                                        </form>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

        </div>
    </div>

</div>

<!-- Modal 1: Add New User Account -->
<div id="newUserModal" class="hidden fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl border border-slate-200">
        <div class="flex items-center justify-between border-b pb-3 mb-4">
            <h3 class="font-bold text-sm text-slate-900">Add New User Account</h3>
            <button onclick="document.getElementById('newUserModal').classList.add('hidden')" class="text-slate-400 hover:text-slate-700 text-lg">&times;</button>
        </div>

        <form action="{{ route('users.store') }}" method="POST" class="space-y-3 text-xs">
            @csrf
            <div>
                <label class="block font-bold text-slate-700 mb-1">Full Name</label>
                <input type="text" name="name" required class="w-full py-2 px-3 border rounded-lg text-xs" placeholder="Engr. Hassan">
            </div>

            <div>
                <label class="block font-bold text-slate-700 mb-1">Email Address</label>
                <input type="email" name="email" required class="w-full py-2 px-3 border rounded-lg text-xs" placeholder="hassan@biomed.com">
            </div>

            <div>
                <label class="block font-bold text-slate-700 mb-1">Role Privilege</label>
                <select name="role" required class="w-full py-2 px-3 border rounded-lg text-xs font-semibold">
                    @foreach($roles as $r)
                        <option value="{{ $r->slug }}">{{ $r->name }} ({{ $r->slug }})</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block font-bold text-slate-700 mb-1">Password</label>
                <div class="relative">
                    <input type="password" name="password" id="new_user_password" required class="w-full py-2 pl-3 pr-9 border rounded-lg text-xs" placeholder="••••••••">
                    <button type="button" onclick="togglePasswordVisibility('new_user_password', this)" class="absolute right-2.5 top-2 text-slate-400 hover:text-slate-700 text-xs focus:outline-none" title="Toggle password visibility">
                        <i class="fa-solid fa-eye"></i>
                    </button>
                </div>
            </div>

            <div class="pt-3 flex justify-end space-x-2">
                <button type="button" onclick="document.getElementById('newUserModal').classList.add('hidden')" class="px-3 py-1.5 border rounded-lg text-slate-600 font-bold">Cancel</button>
                <button type="submit" class="px-4 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg font-bold shadow-xs">Create User</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal 2: Edit User Account -->
<div id="editUserModal" class="hidden fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl border border-slate-200">
        <div class="flex items-center justify-between border-b pb-3 mb-4">
            <h3 class="font-bold text-sm text-slate-900">Edit User Account &amp; Role</h3>
            <button onclick="document.getElementById('editUserModal').classList.add('hidden')" class="text-slate-400 hover:text-slate-700 text-lg">&times;</button>
        </div>

        <form id="editUserForm" action="" method="POST" class="space-y-3 text-xs">
            @csrf
            @method('PUT')

            <div>
                <label class="block font-bold text-slate-700 mb-1">Full Name</label>
                <input type="text" name="name" id="edit_name" required class="w-full py-2 px-3 border rounded-lg text-xs">
            </div>

            <div>
                <label class="block font-bold text-slate-700 mb-1">Email Address</label>
                <input type="email" name="email" id="edit_email" required class="w-full py-2 px-3 border rounded-lg text-xs">
            </div>

            <div>
                <label class="block font-bold text-slate-700 mb-1">Role Privilege</label>
                <select name="role" id="edit_role" required class="w-full py-2 px-3 border rounded-lg text-xs font-semibold">
                    @foreach($roles as $r)
                        <option value="{{ $r->slug }}">{{ $r->name }} ({{ $r->slug }})</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block font-bold text-slate-700 mb-1">New Password (leave empty to keep current)</label>
                <div class="relative">
                    <input type="password" name="password" id="edit_user_password" class="w-full py-2 pl-3 pr-9 border rounded-lg text-xs" placeholder="••••••••">
                    <button type="button" onclick="togglePasswordVisibility('edit_user_password', this)" class="absolute right-2.5 top-2 text-slate-400 hover:text-slate-700 text-xs focus:outline-none" title="Toggle password visibility">
                        <i class="fa-solid fa-eye"></i>
                    </button>
                </div>
            </div>

            <div class="pt-3 flex justify-end space-x-2">
                <button type="button" onclick="document.getElementById('editUserModal').classList.add('hidden')" class="px-3 py-1.5 border rounded-lg text-slate-600 font-bold">Cancel</button>
                <button type="submit" class="px-4 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg font-bold shadow-xs">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal 3: Add Custom Role -->
<div id="newRoleModal" class="hidden fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-xl w-full p-6 shadow-2xl border border-slate-200 max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between border-b pb-3 mb-4">
            <h3 class="font-bold text-sm text-slate-900">Create New Custom Role</h3>
            <button onclick="document.getElementById('newRoleModal').classList.add('hidden')" class="text-slate-400 hover:text-slate-700 text-lg">&times;</button>
        </div>

        <form action="{{ route('roles.store') }}" method="POST" class="space-y-4 text-xs">
            @csrf
            <div>
                <label class="block font-bold text-slate-700 mb-1">Role Title / Name</label>
                <input type="text" name="name" required class="w-full py-2 px-3 border rounded-lg text-xs" placeholder="e.g. Store Incharge / Quality Auditor">
            </div>

            <div>
                <label class="block font-bold text-slate-700 mb-1">Role Description</label>
                <textarea name="description" rows="2" class="w-full py-2 px-3 border rounded-lg text-xs" placeholder="Responsibilities of this role..."></textarea>
            </div>

            <!-- Permission Checkbox Matrix -->
            <div>
                <label class="block font-extrabold text-slate-900 text-xs mb-2">Assign Permissions Matrix:</label>
                <div class="space-y-3 max-h-60 overflow-y-auto border border-slate-200 rounded-xl p-3 bg-slate-50">
                    @foreach($groupedPermissions as $groupName => $perms)
                        <div>
                            <h5 class="font-bold text-[11px] text-teal-800 uppercase tracking-wider mb-1">{{ $groupName }}</h5>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-1.5">
                                @foreach($perms as $p)
                                    <label class="flex items-center space-x-2 bg-white p-1.5 rounded border border-slate-200 text-slate-700 cursor-pointer">
                                        <input type="checkbox" name="permissions[]" value="{{ $p->id }}" class="rounded text-indigo-600 focus:ring-indigo-500">
                                        <span class="font-medium text-[11px]">{{ $p->name }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="pt-3 flex justify-end space-x-2 border-t">
                <button type="button" onclick="document.getElementById('newRoleModal').classList.add('hidden')" class="px-3 py-1.5 border rounded-lg text-slate-600 font-bold">Cancel</button>
                <button type="submit" class="px-4 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg font-bold shadow-xs">Create Role</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal 4: Edit Role Permission Matrix -->
<div id="editRoleModal" class="hidden fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-xl w-full p-6 shadow-2xl border border-slate-200 max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between border-b pb-3 mb-4">
            <h3 class="font-bold text-sm text-slate-900">Edit Role &amp; Permission Checkbox Matrix</h3>
            <button onclick="document.getElementById('editRoleModal').classList.add('hidden')" class="text-slate-400 hover:text-slate-700 text-lg">&times;</button>
        </div>

        <form id="editRoleForm" action="" method="POST" class="space-y-4 text-xs">
            @csrf
            @method('PUT')

            <div>
                <label class="block font-bold text-slate-700 mb-1">Role Title / Name</label>
                <input type="text" name="name" id="edit_role_name" required class="w-full py-2 px-3 border rounded-lg text-xs">
            </div>

            <div>
                <label class="block font-bold text-slate-700 mb-1">Role Description</label>
                <textarea name="description" id="edit_role_description" rows="2" class="w-full py-2 px-3 border rounded-lg text-xs"></textarea>
            </div>

            <!-- Permission Checkbox Matrix -->
            <div>
                <label class="block font-extrabold text-slate-900 text-xs mb-2">Configure Permission Checkboxes:</label>
                <div class="space-y-3 max-h-60 overflow-y-auto border border-slate-200 rounded-xl p-3 bg-slate-50">
                    @foreach($groupedPermissions as $groupName => $perms)
                        <div>
                            <h5 class="font-bold text-[11px] text-teal-800 uppercase tracking-wider mb-1">{{ $groupName }}</h5>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-1.5">
                                @foreach($perms as $p)
                                    <label class="flex items-center space-x-2 bg-white p-1.5 rounded border border-slate-200 text-slate-700 cursor-pointer">
                                        <input type="checkbox" name="permissions[]" value="{{ $p->id }}" id="perm_check_{{ $p->id }}" class="role-perm-checkbox rounded text-indigo-600 focus:ring-indigo-500">
                                        <span class="font-medium text-[11px]">{{ $p->name }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="pt-3 flex justify-end space-x-2 border-t">
                <button type="button" onclick="document.getElementById('editRoleModal').classList.add('hidden')" class="px-3 py-1.5 border rounded-lg text-slate-600 font-bold">Cancel</button>
                <button type="submit" class="px-4 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg font-bold shadow-xs">Save Matrix Changes</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal 5: Add Custom Permission -->
<div id="newPermissionModal" class="hidden fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl border border-slate-200">
        <div class="flex items-center justify-between border-b pb-3 mb-4">
            <h3 class="font-bold text-sm text-slate-900">Add New System Permission</h3>
            <button onclick="document.getElementById('newPermissionModal').classList.add('hidden')" class="text-slate-400 hover:text-slate-700 text-lg">&times;</button>
        </div>

        <form action="{{ route('permissions.store') }}" method="POST" class="space-y-3 text-xs">
            @csrf
            <div>
                <label class="block font-bold text-slate-700 mb-1">Permission Title / Name</label>
                <input type="text" name="name" required class="w-full py-2 px-3 border rounded-lg text-xs" placeholder="e.g. Audit Logs Access">
            </div>

            <div>
                <label class="block font-bold text-slate-700 mb-1">Module Category Group</label>
                <input type="text" name="group_name" required class="w-full py-2 px-3 border rounded-lg text-xs" placeholder="e.g. PPM Operations / System Administration">
            </div>

            <div class="pt-3 flex justify-end space-x-2">
                <button type="button" onclick="document.getElementById('newPermissionModal').classList.add('hidden')" class="px-3 py-1.5 border rounded-lg text-slate-600 font-bold">Cancel</button>
                <button type="submit" class="px-4 py-1.5 bg-teal-600 hover:bg-teal-700 text-white rounded-lg font-bold shadow-xs">Save Permission</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    // Tab Switching Logic
    function switchAccessTab(tabId) {
        const tabs = ['users', 'roles', 'permissions'];
        tabs.forEach(t => {
            const content = document.getElementById('tab_content_' + t);
            const btn = document.getElementById('btn_access_' + t);
            if (content && btn) {
                if (t === tabId) {
                    content.classList.remove('hidden');
                    btn.className = 'py-2.5 px-4 rounded-xl bg-white shadow-xs border border-slate-200 text-slate-900 font-extrabold transition flex items-center';
                } else {
                    content.classList.add('hidden');
                    btn.className = 'py-2.5 px-4 rounded-xl text-slate-600 hover:text-slate-900 transition flex items-center';
                }
            }
        });
    }

    // Initialize Active Tab on Page Load
    document.addEventListener('DOMContentLoaded', function() {
        const activeTab = "{{ $activeTab ?? 'users' }}";
        switchAccessTab(activeTab);
    });

    // Edit User Modal
    function openEditUserModal(user) {
        document.getElementById('editUserForm').action = '/users/' + user.id;
        document.getElementById('edit_name').value = user.name;
        document.getElementById('edit_email').value = user.email;
        document.getElementById('edit_role').value = user.role;
        document.getElementById('editUserModal').classList.remove('hidden');
    }

    // Edit Role Permission Matrix Modal
    function openEditRoleModal(role) {
        document.getElementById('editRoleForm').action = '/roles/' + role.id;
        document.getElementById('edit_role_name').value = role.name;
        document.getElementById('edit_role_description').value = role.description || '';

        // Reset all checkboxes
        document.querySelectorAll('.role-perm-checkbox').forEach(cb => cb.checked = false);

        // Check assigned permissions
        if (role.permissions) {
            role.permissions.forEach(p => {
                const cb = document.getElementById('perm_check_' + p.id);
                if (cb) cb.checked = true;
            });
        }

        document.getElementById('editRoleModal').classList.remove('hidden');
    }

    // Toggle Password Visibility
    function togglePasswordVisibility(inputId, btn) {
        const input = document.getElementById(inputId);
        const icon = btn.querySelector('i');
        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        } else {
            input.type = 'password';
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    }
</script>
@endpush
@endsection

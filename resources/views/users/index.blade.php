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
            <h1 class="text-2xl font-black text-white mt-1.5 tracking-tight">Access Control &amp; Branch Operations</h1>
            <p class="text-xs text-slate-300 mt-1 max-w-xl">
                Unified control panel for User Accounts, dynamic Roles, fine-grained Module Permissions (View, Create, Edit, Delete, Import, Export, Print), and Multi-Branch access control.
            </p>
        </div>

        <div class="flex items-center flex-wrap gap-2">
            @can('create_users')
                <button onclick="document.getElementById('newUserModal').classList.remove('hidden')" class="px-3.5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs shadow-md transition flex items-center">
                    <i class="fa-solid fa-user-plus mr-1.5"></i> Add User
                </button>
            @endcan

            @can('manage_roles')
                <button onclick="document.getElementById('newRoleModal').classList.remove('hidden')" class="px-3.5 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 font-bold text-xs shadow-md transition flex items-center">
                    <i class="fa-solid fa-shield-halved mr-1.5 text-teal-400"></i> Add Role
                </button>
            @endcan

            @can('create_branches')
                <button onclick="document.getElementById('newBranchModal').classList.remove('hidden')" class="px-3.5 py-2 rounded-xl bg-emerald-700 hover:bg-emerald-600 text-white font-bold text-xs shadow-md transition flex items-center">
                    <i class="fa-solid fa-code-branch mr-1.5"></i> Add Branch
                </button>
            @endcan
        </div>
    </div>

    <!-- Summary KPI Cards -->
    <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-5 gap-3">
        <div class="bg-white rounded-xl p-3.5 border border-slate-200 shadow-xs">
            <span class="text-slate-500 text-xs font-semibold block">Total User Accounts</span>
            <p class="text-xl font-black text-slate-900 mt-0.5">{{ number_format($stats['total_users']) }}</p>
        </div>

        <div class="bg-white rounded-xl p-3.5 border border-slate-200 shadow-xs">
            <span class="text-indigo-700 text-xs font-bold block">Active Roles</span>
            <p class="text-xl font-black text-indigo-800 mt-0.5">{{ number_format($stats['total_roles']) }}</p>
        </div>

        <div class="bg-white rounded-xl p-3.5 border border-slate-200 shadow-xs">
            <span class="text-emerald-700 text-xs font-bold block">Active Branches</span>
            <p class="text-xl font-black text-emerald-800 mt-0.5">{{ number_format($stats['active_branches']) }}</p>
        </div>

        <div class="bg-white rounded-xl p-3.5 border border-slate-200 shadow-xs">
            <span class="text-teal-700 text-xs font-bold block">System Permissions</span>
            <p class="text-xl font-black text-teal-800 mt-0.5">{{ number_format($stats['total_permissions']) }}</p>
        </div>

        <div class="bg-white rounded-xl p-3.5 border border-slate-200 shadow-xs col-span-2 sm:col-span-1">
            <span class="text-blue-700 text-xs font-bold block">Active Users</span>
            <p class="text-xl font-black text-blue-800 mt-0.5">{{ number_format($stats['active_users']) }}</p>
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
            <button onclick="switchAccessTab('branches')" id="btn_access_branches" class="py-2.5 px-4 rounded-xl transition flex items-center">
                <i class="fa-solid fa-code-branch mr-2 text-emerald-600"></i> 🏢 Branch Locations ({{ $branches->count() }})
            </button>
            <button onclick="switchAccessTab('permissions')" id="btn_access_permissions" class="py-2.5 px-4 rounded-xl transition flex items-center">
                <i class="fa-solid fa-key mr-2 text-teal-600"></i> 🔑 Permissions Catalog ({{ $permissions->count() }})
            </button>
        </div>

        <div class="p-4 sm:p-5">
            
            <!-- ========================================== -->
            <!-- TAB 1: USER ACCOUNTS MANAGEMENT            -->
            <!-- ========================================== -->
            <div id="tab_content_users" class="space-y-4">
                
                <!-- Search, Role & Branch Filter Bar -->
                <form action="{{ route('users.index') }}" method="GET" class="flex flex-col sm:flex-row gap-3 items-center justify-between text-xs">
                    <input type="hidden" name="tab" value="users">
                    <div class="flex-1 flex flex-col sm:flex-row gap-3 w-full">
                        <div class="w-full sm:w-64">
                            <input type="text" name="search" value="{{ $search }}" placeholder="Search user name or email..." class="w-full py-2 px-3 rounded-xl border border-slate-300 text-xs">
                        </div>
                        <div class="w-full sm:w-44">
                            <select name="role" onchange="this.form.submit()" class="w-full py-2 px-3 rounded-xl border border-slate-300 text-xs font-semibold">
                                <option value="">-- All Roles --</option>
                                @foreach($roles as $r)
                                    <option value="{{ $r->slug }}" {{ $roleFilter == $r->slug ? 'selected' : '' }}>{{ $r->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="w-full sm:w-48">
                            <select name="branch" onchange="this.form.submit()" class="w-full py-2 px-3 rounded-xl border border-slate-300 text-xs font-semibold">
                                <option value="">-- All Branches --</option>
                                <option value="none" {{ $branchFilter === 'none' ? 'selected' : '' }}>Unassigned / HQ Global</option>
                                @foreach($branches as $b)
                                    <option value="{{ $b->id }}" {{ $branchFilter == $b->id ? 'selected' : '' }}>{{ $b->name }} ({{ $b->code }})</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="flex items-center space-x-2 shrink-0">
                        <button type="submit" class="py-2 px-4 rounded-xl bg-slate-900 text-white font-bold text-xs hover:bg-slate-800 transition">Filter</button>
                        @if($search || $roleFilter || $branchFilter)
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
                                <th class="p-3.5">Assigned Branch</th>
                                <th class="p-3.5">Assigned Role</th>
                                <th class="p-3.5">Status</th>
                                <th class="p-3.5">Registered</th>
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
                                        @if($u->branch)
                                            <span class="inline-flex items-center px-2 py-0.5 bg-emerald-50 text-emerald-800 border border-emerald-200 rounded-md font-bold text-[10px]">
                                                <i class="fa-solid fa-location-dot mr-1 text-emerald-600"></i> {{ $u->branch->name }}
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2 py-0.5 bg-slate-100 text-slate-600 border border-slate-200 rounded-md font-semibold text-[10px]">
                                                <i class="fa-solid fa-globe mr-1 text-slate-400"></i> All Branches (HQ)
                                            </span>
                                        @endif
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
                                        @can('edit_users')
                                            <button onclick="openEditUserModal({{ json_encode($u) }})" class="px-2.5 py-1 rounded bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-[11px] transition">
                                                <i class="fa-solid fa-pen-to-square mr-1"></i> Edit
                                            </button>
                                        @endcan

                                        @if($u->id !== auth()->id())
                                            @can('edit_users')
                                                <form action="{{ route('users.toggle', $u) }}" method="POST" class="inline-block">
                                                    @csrf
                                                    <button type="submit" class="px-2.5 py-1 rounded {{ $u->is_active ? 'bg-amber-50 text-amber-700 hover:bg-amber-100' : 'bg-emerald-50 text-emerald-700 hover:bg-emerald-100' }} font-bold text-[11px] transition">
                                                        {{ $u->is_active ? 'Deactivate' : 'Activate' }}
                                                    </button>
                                                </form>
                                            @endcan

                                            @can('delete_users')
                                                <form action="{{ route('users.destroy', $u) }}" method="POST" class="inline-block" onsubmit="return confirm('Delete user {{ $u->name }}?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="px-2 py-1 rounded bg-rose-50 text-rose-600 hover:bg-rose-100 font-bold text-[11px] transition">
                                                        <i class="fa-solid fa-trash"></i>
                                                    </button>
                                                </form>
                                            @endcan
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="p-6 text-center text-slate-400">No user accounts found matching your filter criteria.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="p-3 bg-slate-50 border border-slate-200 rounded-xl">
                    {{ $users->links() }}
                </div>
            </div>

            <!-- ========================================== -->
            <!-- TAB 2: ROLES & PERMISSION MATRIX           -->
            <!-- ========================================== -->
            <div id="tab_content_roles" class="hidden space-y-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-bold text-slate-900">Roles &amp; Module Permissions Matrix</h3>
                        <p class="text-xs text-slate-500">Configure custom user roles and assign fine-grained View, Create, Edit, Delete, Import, Export, Print, and Branch access checkboxes.</p>
                    </div>
                    @can('manage_roles')
                        <button onclick="document.getElementById('newRoleModal').classList.remove('hidden')" class="px-3.5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs shadow-xs">
                            <i class="fa-solid fa-plus mr-1"></i> Add Custom Role
                        </button>
                    @endcan
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

                                <!-- Assigned Permissions Breakdown by Module -->
                                <div>
                                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block mb-1.5">
                                        Assigned Capabilities ({{ $roleObj->permissions->count() }} of {{ $permissions->count() }}):
                                    </span>
                                    <div class="flex flex-wrap gap-1 max-h-36 overflow-y-auto pr-1">
                                        @forelse($roleObj->permissions as $p)
                                            <span class="bg-white text-slate-800 border border-slate-300 text-[10px] font-semibold px-2 py-0.5 rounded shadow-2xs">
                                                {{ $p->name }}
                                            </span>
                                        @empty
                                            <span class="text-[11px] text-slate-400 italic">No permissions assigned.</span>
                                        @endforelse
                                    </div>
                                </div>
                            </div>

                            <div class="pt-3 border-t border-slate-200 flex items-center justify-between text-xs">
                                <span class="text-slate-500 font-medium">Users Assigned: <strong>{{ $roleObj->users->count() }}</strong></span>
                                <div class="flex items-center space-x-2">
                                    @can('manage_roles')
                                        <button onclick="openEditRoleModal({{ json_encode($roleObj->load('permissions')) }})" class="px-3 py-1 bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs rounded-lg transition">
                                            <i class="fa-solid fa-sliders mr-1"></i> Configure Matrix
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
                                    @endcan
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- ========================================== -->
            <!-- TAB 3: BRANCH LOCATIONS & ACCESS           -->
            <!-- ========================================== -->
            <div id="tab_content_branches" class="hidden space-y-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-bold text-slate-900">Company Branches &amp; Regional Service Centers</h3>
                        <p class="text-xs text-slate-500">Manage company office locations. Users and field service vouchers are scoped to their assigned branch.</p>
                    </div>
                    @can('create_branches')
                        <button onclick="document.getElementById('newBranchModal').classList.remove('hidden')" class="px-3.5 py-2 rounded-xl bg-emerald-700 hover:bg-emerald-600 text-white font-bold text-xs shadow-xs">
                            <i class="fa-solid fa-plus mr-1"></i> Add Branch Location
                        </button>
                    @endcan
                </div>

                <!-- Branches Table -->
                <div class="overflow-x-auto border border-slate-200 rounded-xl">
                    <table class="w-full text-left text-xs text-slate-700">
                        <thead class="bg-slate-100 uppercase text-[11px] font-bold text-slate-600 border-b border-slate-200">
                            <tr>
                                <th class="p-3.5">Branch Name &amp; Code</th>
                                <th class="p-3.5">City / Location</th>
                                <th class="p-3.5">Contact Details</th>
                                <th class="p-3.5 text-center">Assigned Users</th>
                                <th class="p-3.5 text-center">PPM Records</th>
                                <th class="p-3.5">Status</th>
                                <th class="p-3.5 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200">
                            @forelse($branches as $branch)
                                <tr class="hover:bg-slate-50 transition">
                                    <td class="p-3.5">
                                        <div class="font-bold text-slate-900 text-sm leading-tight">{{ $branch->name }}</div>
                                        <div class="text-[11px] font-mono text-indigo-700 font-bold">{{ $branch->code }}</div>
                                    </td>
                                    <td class="p-3.5 font-semibold text-slate-800">
                                        <i class="fa-solid fa-city mr-1 text-slate-400"></i> {{ $branch->city ?: 'N/A' }}
                                        <div class="text-[10px] text-slate-500 font-normal truncate max-w-xs">{{ $branch->address ?: 'No address specified' }}</div>
                                    </td>
                                    <td class="p-3.5">
                                        <div class="text-slate-800 font-medium">{{ $branch->phone ?: 'No phone' }}</div>
                                        <div class="text-[11px] text-slate-500 font-mono">{{ $branch->email ?: 'No email' }}</div>
                                    </td>
                                    <td class="p-3.5 text-center font-bold text-slate-900">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded bg-slate-100 border border-slate-200">
                                            {{ $branch->users_count }}
                                        </span>
                                    </td>
                                    <td class="p-3.5 text-center font-bold text-slate-900">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded bg-teal-50 text-teal-800 border border-teal-200">
                                            {{ $branch->ppm_records_count }}
                                        </span>
                                    </td>
                                    <td class="p-3.5">
                                        @if($branch->is_active)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mr-1.5"></span> Active
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800">
                                                <span class="w-1.5 h-1.5 rounded-full bg-rose-500 mr-1.5"></span> Inactive
                                            </span>
                                        @endif
                                    </td>
                                    <td class="p-3.5 text-right space-x-1.5">
                                        @can('edit_branches')
                                            <button onclick="openEditBranchModal({{ json_encode($branch) }})" class="px-2.5 py-1 rounded bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-[11px] transition">
                                                <i class="fa-solid fa-pen-to-square mr-1"></i> Edit
                                            </button>

                                            <form action="{{ route('branches.toggle', $branch) }}" method="POST" class="inline-block">
                                                @csrf
                                                <button type="submit" class="px-2.5 py-1 rounded {{ $branch->is_active ? 'bg-amber-50 text-amber-700 hover:bg-amber-100' : 'bg-emerald-50 text-emerald-700 hover:bg-emerald-100' }} font-bold text-[11px] transition">
                                                    {{ $branch->is_active ? 'Deactivate' : 'Activate' }}
                                                </button>
                                            </form>
                                        @endcan

                                        @can('delete_branches')
                                            @if($branch->users_count == 0 && $branch->ppm_records_count == 0)
                                                <form action="{{ route('branches.destroy', $branch) }}" method="POST" class="inline-block" onsubmit="return confirm('Delete branch {{ $branch->name }}?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="px-2 py-1 rounded bg-rose-50 text-rose-600 hover:bg-rose-100 font-bold text-[11px] transition">
                                                        <i class="fa-solid fa-trash"></i>
                                                    </button>
                                                </form>
                                            @endif
                                        @endcan
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="p-6 text-center text-slate-400">No branch locations registered. Add your company's regional branches above.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- ========================================== -->
            <!-- TAB 4: PERMISSIONS CATALOG                 -->
            <!-- ========================================== -->
            <div id="tab_content_permissions" class="hidden space-y-4">
                <div class="flex items-center justify-between mb-3">
                    <div>
                        <h3 class="text-sm font-bold text-slate-900">System Permissions Catalog</h3>
                        <p class="text-xs text-slate-500">Fine-grained capability slugs grouped by module category (View, Create, Edit, Delete, Import, Export, Print, and Branch Access).</p>
                    </div>
                    @can('manage_permissions')
                        <button onclick="document.getElementById('newPermissionModal').classList.remove('hidden')" class="px-3.5 py-2 rounded-xl bg-teal-600 hover:bg-teal-700 text-white font-bold text-xs shadow-xs">
                            <i class="fa-solid fa-plus mr-1"></i> Add Permission
                        </button>
                    @endcan
                </div>

                <div class="space-y-4">
                    @foreach($groupedPermissions as $groupName => $perms)
                        <div class="bg-slate-50 rounded-xl p-4 border border-slate-200">
                            <h4 class="font-bold text-xs uppercase tracking-wider text-teal-800 mb-2 flex items-center justify-between">
                                <span class="flex items-center">
                                    <i class="fa-solid fa-layer-group mr-1.5"></i> Module: {{ $groupName }}
                                </span>
                                <span class="bg-teal-100 text-teal-800 text-[10px] font-bold px-2 py-0.5 rounded-full">
                                    {{ $perms->count() }} capabilities
                                </span>
                            </h4>
                            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-2">
                                @foreach($perms as $perm)
                                    <div class="bg-white p-2.5 rounded-lg border border-slate-200 flex items-center justify-between shadow-2xs">
                                        <div>
                                            <p class="font-bold text-slate-900 text-xs">{{ $perm->name }}</p>
                                            <p class="font-mono text-[10px] text-slate-500">{{ $perm->slug }}</p>
                                        </div>
                                        @can('manage_permissions')
                                            <form action="{{ route('permissions.destroy', $perm) }}" method="POST" onsubmit="return confirm('Delete permission {{ $perm->name }}?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-slate-300 hover:text-rose-600 text-xs p-1" title="Delete capability">
                                                    <i class="fa-solid fa-xmark"></i>
                                                </button>
                                            </form>
                                        @endcan
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

<!-- ============================================================== -->
<!-- MODAL 1: ADD NEW USER ACCOUNT                                  -->
<!-- ============================================================== -->
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
                <input type="text" name="name" required class="w-full py-2 px-3 border rounded-lg text-xs" placeholder="e.g. Engr. Daniyal Khan">
            </div>

            <div>
                <label class="block font-bold text-slate-700 mb-1">Email Address</label>
                <input type="email" name="email" required class="w-full py-2 px-3 border rounded-lg text-xs" placeholder="daniyal@biomed.com">
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Role Privilege</label>
                    <select name="role" required class="w-full py-2 px-3 border rounded-lg text-xs font-semibold">
                        @foreach($roles as $r)
                            <option value="{{ $r->slug }}">{{ $r->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1">Branch Office</label>
                    <select name="branch_id" class="w-full py-2 px-3 border rounded-lg text-xs font-semibold">
                        <option value="">-- All Branches (HQ) --</option>
                        @foreach($branches as $b)
                            <option value="{{ $b->id }}">{{ $b->name }} ({{ $b->code }})</option>
                        @endforeach
                    </select>
                </div>
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

<!-- ============================================================== -->
<!-- MODAL 2: EDIT USER ACCOUNT                                     -->
<!-- ============================================================== -->
<div id="editUserModal" class="hidden fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl border border-slate-200">
        <div class="flex items-center justify-between border-b pb-3 mb-4">
            <h3 class="font-bold text-sm text-slate-900">Edit User Account &amp; Branch Assignment</h3>
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

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Role Privilege</label>
                    <select name="role" id="edit_role" required class="w-full py-2 px-3 border rounded-lg text-xs font-semibold">
                        @foreach($roles as $r)
                            <option value="{{ $r->slug }}">{{ $r->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1">Branch Office</label>
                    <select name="branch_id" id="edit_branch_id" class="w-full py-2 px-3 border rounded-lg text-xs font-semibold">
                        <option value="">-- All Branches (HQ) --</option>
                        @foreach($branches as $b)
                            <option value="{{ $b->id }}">{{ $b->name }} ({{ $b->code }})</option>
                        @endforeach
                    </select>
                </div>
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

<!-- ============================================================== -->
<!-- MODAL 3: ADD CUSTOM ROLE WITH INTUITIVE PERMISSION MATRIX      -->
<!-- ============================================================== -->
<div id="newRoleModal" class="hidden fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-3xl w-full p-6 shadow-2xl border border-slate-200 max-h-[92vh] overflow-y-auto">
        <div class="flex items-center justify-between border-b pb-3 mb-4">
            <div>
                <h3 class="font-bold text-base text-slate-900">Create Custom Role &amp; Module Permissions</h3>
                <p class="text-xs text-slate-500">Configure granular View, Create, Edit, Delete, Import, Export, and Print permissions per module.</p>
            </div>
            <button onclick="document.getElementById('newRoleModal').classList.add('hidden')" class="text-slate-400 hover:text-slate-700 text-xl font-bold">&times;</button>
        </div>

        <form action="{{ route('roles.store') }}" method="POST" class="space-y-4 text-xs">
            @csrf
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Role Title / Name</label>
                    <input type="text" name="name" required class="w-full py-2 px-3 border rounded-lg text-xs" placeholder="e.g. Biomedical Supervisor / QC Inspector">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Role Description</label>
                    <input type="text" name="description" class="w-full py-2 px-3 border rounded-lg text-xs" placeholder="Brief responsibilities of this role...">
                </div>
            </div>

            <!-- Granular Module Permission Matrix -->
            <div>
                <div class="flex items-center justify-between mb-2">
                    <label class="font-extrabold text-slate-900 text-xs">Module Permissions Matrix:</label>
                    <button type="button" onclick="toggleAllCheckboxes('#newRoleModal', true)" class="text-[11px] text-indigo-600 hover:underline font-bold">Select All</button>
                    <span class="text-slate-300">|</span>
                    <button type="button" onclick="toggleAllCheckboxes('#newRoleModal', false)" class="text-[11px] text-slate-600 hover:underline font-medium">Deselect All</button>
                </div>

                <div class="space-y-3 max-h-96 overflow-y-auto border border-slate-200 rounded-xl p-3 bg-slate-50">
                    @foreach($groupedPermissions as $groupName => $perms)
                        <div class="bg-white rounded-xl border border-slate-200 p-3 shadow-2xs">
                            <div class="flex items-center justify-between pb-2 mb-2 border-b border-slate-100">
                                <h5 class="font-extrabold text-xs text-indigo-900 uppercase tracking-wider flex items-center">
                                    <i class="fa-solid fa-layer-group text-indigo-500 mr-1.5"></i> {{ $groupName }}
                                </h5>
                                <button type="button" onclick="toggleModuleGroup(this, true)" class="text-[10px] text-slate-500 hover:text-indigo-600 font-semibold">Check Group</button>
                            </div>
                            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-2">
                                @foreach($perms as $p)
                                    <label class="flex items-start space-x-2 p-1.5 rounded hover:bg-slate-50 cursor-pointer">
                                        <input type="checkbox" name="permissions[]" value="{{ $p->id }}" class="new-role-perm-cb rounded text-indigo-600 focus:ring-indigo-500 mt-0.5">
                                        <div>
                                            <span class="font-bold text-[11px] text-slate-800 block leading-tight">{{ $p->name }}</span>
                                            <span class="font-mono text-[9px] text-slate-400 block leading-tight">{{ $p->slug }}</span>
                                        </div>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="pt-3 flex justify-end space-x-2 border-t">
                <button type="button" onclick="document.getElementById('newRoleModal').classList.add('hidden')" class="px-4 py-2 border rounded-xl text-slate-600 font-bold">Cancel</button>
                <button type="submit" class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl font-bold shadow-xs">Create Custom Role</button>
            </div>
        </form>
    </div>
</div>

<!-- ============================================================== -->
<!-- MODAL 4: EDIT ROLE & PERMISSION MATRIX                         -->
<!-- ============================================================== -->
<div id="editRoleModal" class="hidden fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-3xl w-full p-6 shadow-2xl border border-slate-200 max-h-[92vh] overflow-y-auto">
        <div class="flex items-center justify-between border-b pb-3 mb-4">
            <div>
                <h3 class="font-bold text-base text-slate-900">Configure Role &amp; Permission Matrix</h3>
                <p class="text-xs text-slate-500">Enable or disable module capabilities (View, Create, Edit, Delete, Import, Export, Print, Branch Access).</p>
            </div>
            <button onclick="document.getElementById('editRoleModal').classList.add('hidden')" class="text-slate-400 hover:text-slate-700 text-xl font-bold">&times;</button>
        </div>

        <form id="editRoleForm" action="" method="POST" class="space-y-4 text-xs">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Role Title / Name</label>
                    <input type="text" name="name" id="edit_role_name" required class="w-full py-2 px-3 border rounded-lg text-xs">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Role Description</label>
                    <input type="text" name="description" id="edit_role_description" class="w-full py-2 px-3 border rounded-lg text-xs">
                </div>
            </div>

            <!-- Granular Module Permission Matrix -->
            <div>
                <div class="flex items-center justify-between mb-2">
                    <label class="font-extrabold text-slate-900 text-xs">Assigned Module Capabilities:</label>
                    <div class="space-x-2">
                        <button type="button" onclick="toggleAllCheckboxes('#editRoleModal', true)" class="text-[11px] text-indigo-600 hover:underline font-bold">Select All</button>
                        <span class="text-slate-300">|</span>
                        <button type="button" onclick="toggleAllCheckboxes('#editRoleModal', false)" class="text-[11px] text-slate-600 hover:underline font-medium">Deselect All</button>
                    </div>
                </div>

                <div class="space-y-3 max-h-96 overflow-y-auto border border-slate-200 rounded-xl p-3 bg-slate-50">
                    @foreach($groupedPermissions as $groupName => $perms)
                        <div class="bg-white rounded-xl border border-slate-200 p-3 shadow-2xs">
                            <div class="flex items-center justify-between pb-2 mb-2 border-b border-slate-100">
                                <h5 class="font-extrabold text-xs text-indigo-900 uppercase tracking-wider flex items-center">
                                    <i class="fa-solid fa-layer-group text-indigo-500 mr-1.5"></i> {{ $groupName }}
                                </h5>
                                <button type="button" onclick="toggleModuleGroup(this)" class="text-[10px] text-slate-500 hover:text-indigo-600 font-semibold">Toggle Group</button>
                            </div>
                            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-2">
                                @foreach($perms as $p)
                                    <label class="flex items-start space-x-2 p-1.5 rounded hover:bg-slate-50 cursor-pointer">
                                        <input type="checkbox" name="permissions[]" value="{{ $p->id }}" id="perm_check_{{ $p->id }}" class="role-perm-checkbox rounded text-indigo-600 focus:ring-indigo-500 mt-0.5">
                                        <div>
                                            <span class="font-bold text-[11px] text-slate-800 block leading-tight">{{ $p->name }}</span>
                                            <span class="font-mono text-[9px] text-slate-400 block leading-tight">{{ $p->slug }}</span>
                                        </div>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="pt-3 flex justify-end space-x-2 border-t">
                <button type="button" onclick="document.getElementById('editRoleModal').classList.add('hidden')" class="px-4 py-2 border rounded-xl text-slate-600 font-bold">Cancel</button>
                <button type="submit" class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl font-bold shadow-xs">Save Matrix Changes</button>
            </div>
        </form>
    </div>
</div>

<!-- ============================================================== -->
<!-- MODAL 5: ADD NEW BRANCH LOCATION                               -->
<!-- ============================================================== -->
<div id="newBranchModal" class="hidden fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl border border-slate-200">
        <div class="flex items-center justify-between border-b pb-3 mb-4">
            <h3 class="font-bold text-sm text-slate-900">Add New Branch Location</h3>
            <button onclick="document.getElementById('newBranchModal').classList.add('hidden')" class="text-slate-400 hover:text-slate-700 text-lg">&times;</button>
        </div>

        <form action="{{ route('branches.store') }}" method="POST" class="space-y-3 text-xs">
            @csrf
            <div>
                <label class="block font-bold text-slate-700 mb-1">Branch Name</label>
                <input type="text" name="name" required class="w-full py-2 px-3 border rounded-lg text-xs" placeholder="e.g. Peshawar Regional Branch">
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Branch Code</label>
                    <input type="text" name="code" required class="w-full py-2 px-3 border rounded-lg text-xs font-mono uppercase" placeholder="e.g. REG-PEW">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">City</label>
                    <input type="text" name="city" class="w-full py-2 px-3 border rounded-lg text-xs" placeholder="e.g. Peshawar">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Contact Phone</label>
                    <input type="text" name="phone" class="w-full py-2 px-3 border rounded-lg text-xs" placeholder="091-1234567">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Contact Email</label>
                    <input type="email" name="email" class="w-full py-2 px-3 border rounded-lg text-xs" placeholder="peshawar@biomed.com">
                </div>
            </div>

            <div>
                <label class="block font-bold text-slate-700 mb-1">Street Address</label>
                <textarea name="address" rows="2" class="w-full py-2 px-3 border rounded-lg text-xs" placeholder="Physical location of branch office..."></textarea>
            </div>

            <div class="pt-3 flex justify-end space-x-2">
                <button type="button" onclick="document.getElementById('newBranchModal').classList.add('hidden')" class="px-3 py-1.5 border rounded-lg text-slate-600 font-bold">Cancel</button>
                <button type="submit" class="px-4 py-1.5 bg-emerald-700 hover:bg-emerald-600 text-white rounded-lg font-bold shadow-xs">Save Branch</button>
            </div>
        </form>
    </div>
</div>

<!-- ============================================================== -->
<!-- MODAL 6: EDIT BRANCH LOCATION                                  -->
<!-- ============================================================== -->
<div id="editBranchModal" class="hidden fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl border border-slate-200">
        <div class="flex items-center justify-between border-b pb-3 mb-4">
            <h3 class="font-bold text-sm text-slate-900">Edit Branch Details</h3>
            <button onclick="document.getElementById('editBranchModal').classList.add('hidden')" class="text-slate-400 hover:text-slate-700 text-lg">&times;</button>
        </div>

        <form id="editBranchForm" action="" method="POST" class="space-y-3 text-xs">
            @csrf
            @method('PUT')

            <div>
                <label class="block font-bold text-slate-700 mb-1">Branch Name</label>
                <input type="text" name="name" id="edit_branch_name" required class="w-full py-2 px-3 border rounded-lg text-xs">
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Branch Code</label>
                    <input type="text" name="code" id="edit_branch_code" required class="w-full py-2 px-3 border rounded-lg text-xs font-mono uppercase">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">City</label>
                    <input type="text" name="city" id="edit_branch_city" class="w-full py-2 px-3 border rounded-lg text-xs">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Contact Phone</label>
                    <input type="text" name="phone" id="edit_branch_phone" class="w-full py-2 px-3 border rounded-lg text-xs">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Contact Email</label>
                    <input type="email" name="email" id="edit_branch_email" class="w-full py-2 px-3 border rounded-lg text-xs">
                </div>
            </div>

            <div>
                <label class="block font-bold text-slate-700 mb-1">Street Address</label>
                <textarea name="address" id="edit_branch_address" rows="2" class="w-full py-2 px-3 border rounded-lg text-xs"></textarea>
            </div>

            <div class="pt-3 flex justify-end space-x-2">
                <button type="button" onclick="document.getElementById('editBranchModal').classList.add('hidden')" class="px-3 py-1.5 border rounded-lg text-slate-600 font-bold">Cancel</button>
                <button type="submit" class="px-4 py-1.5 bg-emerald-700 hover:bg-emerald-600 text-white rounded-lg font-bold shadow-xs">Update Branch</button>
            </div>
        </form>
    </div>
</div>

<!-- ============================================================== -->
<!-- MODAL 7: ADD CUSTOM PERMISSION                                 -->
<!-- ============================================================== -->
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
                <input type="text" name="name" required class="w-full py-2 px-3 border rounded-lg text-xs" placeholder="e.g. Audit Logs Export">
            </div>

            <div>
                <label class="block font-bold text-slate-700 mb-1">Module Category Group</label>
                <input type="text" name="group_name" required class="w-full py-2 px-3 border rounded-lg text-xs" placeholder="e.g. PPM Operations / Branch Management">
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
        const tabs = ['users', 'roles', 'branches', 'permissions'];
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
        document.getElementById('edit_branch_id').value = user.branch_id || '';
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

    // Edit Branch Modal
    function openEditBranchModal(branch) {
        document.getElementById('editBranchForm').action = '/branches/' + branch.id;
        document.getElementById('edit_branch_name').value = branch.name;
        document.getElementById('edit_branch_code').value = branch.code;
        document.getElementById('edit_branch_city').value = branch.city || '';
        document.getElementById('edit_branch_phone').value = branch.phone || '';
        document.getElementById('edit_branch_email').value = branch.email || '';
        document.getElementById('edit_branch_address').value = branch.address || '';
        document.getElementById('editBranchModal').classList.remove('hidden');
    }

    // Toggle All Checkboxes in a Modal
    function toggleAllCheckboxes(modalSelector, checkState) {
        const container = document.querySelector(modalSelector);
        if (container) {
            container.querySelectorAll('input[type="checkbox"]').forEach(cb => cb.checked = checkState);
        }
    }

    // Toggle Group Checkboxes inside a Module Card
    function toggleModuleGroup(btn, forceCheck) {
        const card = btn.closest('.bg-white');
        if (card) {
            const checkboxes = card.querySelectorAll('input[type="checkbox"]');
            const anyUnchecked = Array.from(checkboxes).some(cb => !cb.checked);
            checkboxes.forEach(cb => cb.checked = anyUnchecked);
        }
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

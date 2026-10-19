<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\Permission;
use App\Models\Branch;
use App\Models\User;
use App\Models\PpmRecord;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Seed Initial Branches
        $branchHq = Branch::updateOrCreate(
            ['code' => 'HQ-KHI'],
            [
                'name' => 'Main Branch (Karachi HQ)',
                'city' => 'Karachi',
                'phone' => '021-34567890',
                'email' => 'karachi@biomed.com',
                'address' => 'Main Shahrah-e-Faisal, Karachi, Sindh',
                'is_active' => true,
            ]
        );

        $branchLhr = Branch::updateOrCreate(
            ['code' => 'REG-LHR'],
            [
                'name' => 'Lahore Regional Branch',
                'city' => 'Lahore',
                'phone' => '042-35678901',
                'email' => 'lahore@biomed.com',
                'address' => 'Gulberg III, Main Boulevard, Lahore, Punjab',
                'is_active' => true,
            ]
        );

        $branchIsb = Branch::updateOrCreate(
            ['code' => 'REG-ISB'],
            [
                'name' => 'Islamabad Regional Office',
                'city' => 'Islamabad',
                'phone' => '051-23456789',
                'email' => 'islamabad@biomed.com',
                'address' => 'Blue Area, Jinnah Avenue, Islamabad',
                'is_active' => true,
            ]
        );

        // Assign unassigned legacy records to HQ branch
        PpmRecord::whereNull('branch_id')->update(['branch_id' => $branchHq->id]);

        // 2. Comprehensive Dynamic Permissions Catalog
        $permissions = [
            // PPM Operations
            ['name' => 'View PPM Records', 'slug' => 'view_ppm', 'group_name' => 'PPM Operations'],
            ['name' => 'Create PPM Form', 'slug' => 'create_ppm', 'group_name' => 'PPM Operations'],
            ['name' => 'Edit PPM Form', 'slug' => 'edit_ppm', 'group_name' => 'PPM Operations'],
            ['name' => 'Delete PPM Record', 'slug' => 'delete_ppm', 'group_name' => 'PPM Operations'],
            ['name' => 'Import PPM Records', 'slug' => 'import_ppm', 'group_name' => 'PPM Operations'],
            ['name' => 'Export CSV Reports', 'slug' => 'export_ppm', 'group_name' => 'PPM Operations'],
            ['name' => 'Print Official Voucher', 'slug' => 'print_ppm', 'group_name' => 'PPM Operations'],

            // Customers & Hospitals
            ['name' => 'View Customers', 'slug' => 'view_customers', 'group_name' => 'Customers & Hospitals'],
            ['name' => 'Create Customer', 'slug' => 'create_customers', 'group_name' => 'Customers & Hospitals'],
            ['name' => 'Edit Customer', 'slug' => 'edit_customers', 'group_name' => 'Customers & Hospitals'],
            ['name' => 'Delete Customer', 'slug' => 'delete_customers', 'group_name' => 'Customers & Hospitals'],
            ['name' => 'Import Customers CSV', 'slug' => 'import_customers', 'group_name' => 'Customers & Hospitals'],
            ['name' => 'Export Customers CSV', 'slug' => 'export_customers', 'group_name' => 'Customers & Hospitals'],
            ['name' => 'Print Customer Sheet', 'slug' => 'print_customers', 'group_name' => 'Customers & Hospitals'],

            // Products & Equipment
            ['name' => 'View Products', 'slug' => 'view_products', 'group_name' => 'Products & Equipment'],
            ['name' => 'Create Product', 'slug' => 'create_products', 'group_name' => 'Products & Equipment'],
            ['name' => 'Edit Product', 'slug' => 'edit_products', 'group_name' => 'Products & Equipment'],
            ['name' => 'Delete Product', 'slug' => 'delete_products', 'group_name' => 'Products & Equipment'],
            ['name' => 'Import Products CSV', 'slug' => 'import_products', 'group_name' => 'Products & Equipment'],
            ['name' => 'Export Products CSV', 'slug' => 'export_products', 'group_name' => 'Products & Equipment'],
            ['name' => 'Print Products Sheet', 'slug' => 'print_products', 'group_name' => 'Products & Equipment'],

            // Reports & Analytics Hub
            ['name' => 'View Reports Hub', 'slug' => 'view_reports', 'group_name' => 'Reports & Analytics'],
            ['name' => 'Export CSV Reports', 'slug' => 'export_reports', 'group_name' => 'Reports & Analytics'],
            ['name' => 'Print Official Reports', 'slug' => 'print_reports', 'group_name' => 'Reports & Analytics'],

            // Branch Access & Management
            ['name' => 'View Branches', 'slug' => 'view_branches', 'group_name' => 'Branch Management'],
            ['name' => 'Create Branch', 'slug' => 'create_branches', 'group_name' => 'Branch Management'],
            ['name' => 'Edit Branch', 'slug' => 'edit_branches', 'group_name' => 'Branch Management'],
            ['name' => 'Delete Branch', 'slug' => 'delete_branches', 'group_name' => 'Branch Management'],
            ['name' => 'Access All Branches', 'slug' => 'access_all_branches', 'group_name' => 'Branch Management'],

            // System Administration
            ['name' => 'View User Management', 'slug' => 'view_users', 'group_name' => 'System Administration'],
            ['name' => 'Create User Account', 'slug' => 'create_users', 'group_name' => 'System Administration'],
            ['name' => 'Edit User Account', 'slug' => 'edit_users', 'group_name' => 'System Administration'],
            ['name' => 'Delete User Account', 'slug' => 'delete_users', 'group_name' => 'System Administration'],
            ['name' => 'Manage Roles Matrix', 'slug' => 'manage_roles', 'group_name' => 'System Administration'],
            ['name' => 'Manage System Permissions', 'slug' => 'manage_permissions', 'group_name' => 'System Administration'],
        ];

        $permissionModels = [];
        foreach ($permissions as $p) {
            $permissionModels[$p['slug']] = Permission::updateOrCreate(
                ['slug' => $p['slug']],
                ['name' => $p['name'], 'group_name' => $p['group_name']]
            );
        }

        // Backward compatibility: map old slugs if any
        $oldSlugs = [
            'manage_customers' => ['view_customers', 'create_customers', 'edit_customers', 'delete_customers', 'export_customers'],
            'manage_products'  => ['view_products', 'create_products', 'edit_products', 'delete_products', 'export_products'],
            'manage_users'     => ['view_users', 'create_users', 'edit_users', 'delete_users'],
        ];

        // 3. Seed Roles & Assign Permissions Matrix

        // A. Super Admin Role (All Permissions)
        $adminRole = Role::updateOrCreate(
            ['slug' => 'admin'],
            [
                'name' => 'Super Admin',
                'description' => 'Full un-restricted access across all modules, branches, users, roles, and system configurations.',
                'is_system' => true,
            ]
        );
        $adminRole->permissions()->sync(array_column($permissionModels, 'id'));

        // B. Manager Role
        $managerRole = Role::updateOrCreate(
            ['slug' => 'manager'],
            [
                'name' => 'Manager',
                'description' => 'Operations supervisor with control over PPM records, reports, customers, products, and multi-branch access.',
                'is_system' => true,
            ]
        );
        $managerPerms = [
            'view_ppm', 'create_ppm', 'edit_ppm', 'delete_ppm', 'import_ppm', 'export_ppm', 'print_ppm',
            'view_customers', 'create_customers', 'edit_customers', 'export_customers', 'print_customers',
            'view_products', 'create_products', 'edit_products', 'export_products', 'print_products',
            'view_reports', 'export_reports', 'print_reports',
            'view_branches', 'access_all_branches',
        ];
        $managerRole->permissions()->sync(
            array_values(array_filter(array_map(fn($slug) => $permissionModels[$slug]->id ?? null, $managerPerms)))
        );

        // C. Field Technician Role
        $techRole = Role::updateOrCreate(
            ['slug' => 'technician'],
            [
                'name' => 'Field Technician',
                'description' => 'Biomedical field engineer focused on servicing equipment, filling PPM forms, and viewing master catalogs.',
                'is_system' => true,
            ]
        );
        $techPerms = [
            'view_ppm', 'create_ppm', 'edit_ppm', 'print_ppm',
            'view_customers', 'create_customers',
            'view_products',
            'view_reports',
        ];
        $techRole->permissions()->sync(
            array_values(array_filter(array_map(fn($slug) => $permissionModels[$slug]->id ?? null, $techPerms)))
        );

        // D. Viewer / Auditor Role
        $viewerRole = Role::updateOrCreate(
            ['slug' => 'viewer'],
            [
                'name' => 'Viewer / Auditor',
                'description' => 'Read-only access for hospital inspectors, compliance auditors, and observers.',
                'is_system' => true,
            ]
        );
        $viewerPerms = [
            'view_ppm', 'print_ppm',
            'view_customers', 'print_customers',
            'view_products', 'print_products',
            'view_reports', 'export_reports', 'print_reports',
            'view_branches',
        ];
        $viewerRole->permissions()->sync(
            array_values(array_filter(array_map(fn($slug) => $permissionModels[$slug]->id ?? null, $viewerPerms)))
        );
    }
}

<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Seed Permissions
        $permissions = [
            // PPM Operations
            ['name' => 'View PPM Records', 'slug' => 'view_ppm', 'group_name' => 'PPM Operations'],
            ['name' => 'Create PPM Form', 'slug' => 'create_ppm', 'group_name' => 'PPM Operations'],
            ['name' => 'Edit PPM Form', 'slug' => 'edit_ppm', 'group_name' => 'PPM Operations'],
            ['name' => 'Delete PPM Record', 'slug' => 'delete_ppm', 'group_name' => 'PPM Operations'],

            // Reports & Analytics
            ['name' => 'View Reports Hub', 'slug' => 'view_reports', 'group_name' => 'Reports & Analytics'],
            ['name' => 'Export CSV Reports', 'slug' => 'export_reports', 'group_name' => 'Reports & Analytics'],
            ['name' => 'Print Official Reports', 'slug' => 'print_reports', 'group_name' => 'Reports & Analytics'],

            // Master Data
            ['name' => 'Manage Customers & Hospitals', 'slug' => 'manage_customers', 'group_name' => 'Master Data'],
            ['name' => 'Manage Products & Equipment', 'slug' => 'manage_products', 'group_name' => 'Master Data'],

            // System Administration
            ['name' => 'Manage User Accounts', 'slug' => 'manage_users', 'group_name' => 'System Administration'],
            ['name' => 'Manage Roles & Matrix', 'slug' => 'manage_roles', 'group_name' => 'System Administration'],
            ['name' => 'Manage System Permissions', 'slug' => 'manage_permissions', 'group_name' => 'System Administration'],
        ];

        $permissionModels = [];
        foreach ($permissions as $p) {
            $permissionModels[$p['slug']] = Permission::updateOrCreate(
                ['slug' => $p['slug']],
                ['name' => $p['name'], 'group_name' => $p['group_name']]
            );
        }

        // 2. Seed Roles & Assign Permissions Matrix

        // A. Super Admin Role
        $adminRole = Role::updateOrCreate(
            ['slug' => 'admin'],
            [
                'name' => 'Super Admin',
                'description' => 'Full un-restricted access to system configuration, users, roles, and records.',
                'is_system' => true,
            ]
        );
        $adminRole->permissions()->sync(array_column($permissionModels, 'id'));

        // B. Manager Role
        $managerRole = Role::updateOrCreate(
            ['slug' => 'manager'],
            [
                'name' => 'Manager',
                'description' => 'Field supervisor with control over PPM records, reports, customers, and products.',
                'is_system' => true,
            ]
        );
        $managerPerms = ['view_ppm', 'create_ppm', 'edit_ppm', 'view_reports', 'export_reports', 'print_reports', 'manage_customers', 'manage_products'];
        $managerRole->permissions()->sync(array_map(fn($slug) => $permissionModels[$slug]->id, $managerPerms));

        // C. Field Technician Role
        $techRole = Role::updateOrCreate(
            ['slug' => 'technician'],
            [
                'name' => 'Field Technician',
                'description' => 'Biomedical field engineer focused on servicing equipment and filling PPM forms.',
                'is_system' => true,
            ]
        );
        $techPerms = ['view_ppm', 'create_ppm', 'edit_ppm', 'view_reports'];
        $techRole->permissions()->sync(array_map(fn($slug) => $permissionModels[$slug]->id, $techPerms));

        // D. Viewer / Auditor Role
        $viewerRole = Role::updateOrCreate(
            ['slug' => 'viewer'],
            [
                'name' => 'Viewer / Auditor',
                'description' => 'Read-only access for hospital inspectors and compliance auditors.',
                'is_system' => true,
            ]
        );
        $viewerPerms = ['view_ppm', 'view_reports', 'export_reports', 'print_reports'];
        $viewerRole->permissions()->sync(array_map(fn($slug) => $permissionModels[$slug]->id, $viewerPerms));
    }
}

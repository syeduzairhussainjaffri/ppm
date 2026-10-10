<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Super Admin Account
        User::updateOrCreate(
            ['email' => 'admin@biomed.com'],
            [
                'name' => 'System Administrator',
                'password' => Hash::make('admin'),
                'role' => User::ROLE_ADMIN,
                'is_active' => true,
            ]
        );

        // 2. Manager Account
        User::updateOrCreate(
            ['email' => 'manager@biomed.com'],
            [
                'name' => 'Engr. Daniyal (Manager)',
                'password' => Hash::make('password123'),
                'role' => User::ROLE_MANAGER,
                'is_active' => true,
            ]
        );

        // 3. Biomedical Technician / Engineer Account
        User::updateOrCreate(
            ['email' => 'tech@biomed.com'],
            [
                'name' => 'Engr. Salman (Field Tech)',
                'password' => Hash::make('password123'),
                'role' => User::ROLE_TECHNICIAN,
                'is_active' => true,
            ]
        );

        // 4. Auditor / Viewer Account
        User::updateOrCreate(
            ['email' => 'viewer@biomed.com'],
            [
                'name' => 'Hospital Inspector (Viewer)',
                'password' => Hash::make('password123'),
                'role' => User::ROLE_VIEWER,
                'is_active' => true,
            ]
        );
    }
}

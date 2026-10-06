<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PpmRecord;
use App\Models\Customer;
use App\Models\Product;

class PpmRecordSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Create or Find Customers
        $customer1 = Customer::firstOrCreate(
            ['name' => 'Director Health Services Karachi, Sindh'],
            [
                'department' => 'Biomedical',
                'contact_person' => 'Engr. Daniyal',
                'contact_no' => '0334-2834216',
                'address' => 'Karachi, Sindh',
                'notes' => 'Central Health Department Karachi',
            ]
        );

        $customer2 = Customer::firstOrCreate(
            ['name' => 'General Hospital (Karachi)'],
            [
                'department' => 'Gynae, Neuro, Orthopedic',
                'contact_person' => 'DR / Technician',
                'contact_no' => '021-3456789',
                'address' => 'Karachi',
                'notes' => 'Hospital wards and specialized units',
            ]
        );

        // Additional sample hospital
        Customer::firstOrCreate(
            ['name' => 'Civil Hospital Karachi'],
            [
                'department' => 'Biomedical Engineering',
                'contact_person' => 'Engr. Salman',
                'contact_no' => '0300-1234567',
                'address' => 'Mission Road, Karachi',
                'notes' => 'Government tertiary hospital',
            ]
        );

        // 2. Create or Find Products
        $product1 = Product::firstOrCreate(
            ['name' => 'Oxygen Supply Line 400 Beds'],
            [
                'equipment_name' => 'Oxygen Supply Line System',
                'model_make' => 'Noxerior, Italy',
                'category' => 'Medical Gas System',
                'default_ppm_days' => 30,
                'description' => 'Hospital 400 Beds central oxygen distribution and manifold pipeline system.',
            ]
        );

        $product2 = Product::firstOrCreate(
            ['name' => 'Air Purifier'],
            [
                'equipment_name' => 'Hospital Grade Air Purifier',
                'model_make' => 'Spatel plus (Spacetech Healthcare)',
                'category' => 'Air Filtration & Sterilization',
                'default_ppm_days' => 30,
                'description' => 'Medical grade HEPA air purifier unit for ICU/OT and hospital wards.',
            ]
        );

        // Additional sample products
        Product::firstOrCreate(
            ['name' => 'Anesthesia Machine'],
            [
                'equipment_name' => 'Workstation Anesthesia System',
                'model_make' => 'Mindray / Dräger',
                'category' => 'Critical Care',
                'default_ppm_days' => 60,
                'description' => 'Operation theatre anesthesia delivery system.',
            ]
        );

        // 3. Link or Update existing PPM records
        $rec1 = PpmRecord::where('serial_no', 'SN013517-48')->first();
        if ($rec1) {
            $rec1->update([
                'customer_id' => $customer1->id,
                'product_id' => $product1->id,
            ]);
        }

        $rec2 = PpmRecord::where('serial_no', 'CM01249-12')->first();
        if ($rec2) {
            $rec2->update([
                'customer_id' => $customer2->id,
                'product_id' => $product2->id,
            ]);
        }
    }
}

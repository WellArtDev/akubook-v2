<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Branch;
use App\Models\Department;
use App\Models\Position;
use App\Models\Warehouse;
use App\Models\Customer;
// use App\Models\Supplier; // Skip - migration incomplete
// use App\Models\Item; // Skip - migration incomplete
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class DevelopmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clear existing data (idempotent)
        $this->command->info('Clearing existing data...');
        User::where('email', '!=', 'admin@akubook.com')->delete();
        Branch::truncate();
        Department::truncate();
        Position::truncate();
        Warehouse::truncate();
        Customer::truncate();
        // Supplier::truncate(); // Skip - migration incomplete
        // Item::truncate(); // Skip - migration incomplete

        // Seed Branches
        $this->command->info('Seeding branches...');
        $jakarta = Branch::create([
            'code' => 'JKT01',
            'name' => 'Jakarta Pusat',
            'address' => 'Jl. Sudirman No. 123, Jakarta Pusat',
            'phone' => '021-12345678',
            'email' => 'jakarta@akubook.com',
            'is_active' => true,
        ]);

        $bandung = Branch::create([
            'code' => 'BDG01',
            'name' => 'Bandung',
            'address' => 'Jl. Asia Afrika No. 456, Bandung',
            'phone' => '022-87654321',
            'email' => 'bandung@akubook.com',
            'is_active' => true,
        ]);

        $surabaya = Branch::create([
            'code' => 'SBY01',
            'name' => 'Surabaya',
            'address' => 'Jl. Tunjungan No. 789, Surabaya',
            'phone' => '031-11223344',
            'email' => 'surabaya@akubook.com',
            'is_active' => true,
        ]);

        // Seed Departments
        $this->command->info('Seeding departments...');
        $finance = Department::create([
            'code' => 'FIN',
            'name' => 'Finance',
            'description' => 'Finance and Accounting Department',
            'is_active' => true,
        ]);

        $sales = Department::create([
            'code' => 'SAL',
            'name' => 'Sales',
            'description' => 'Sales and Marketing Department',
            'is_active' => true,
        ]);

        $warehouse = Department::create([
            'code' => 'WHS',
            'name' => 'Warehouse',
            'description' => 'Warehouse and Logistics Department',
            'is_active' => true,
        ]);

        // Seed Positions
        $this->command->info('Seeding positions...');
        Position::create([
            'code' => 'FIN-MGR',
            'name' => 'Finance Manager',
            'description' => 'Manages finance and accounting operations',
            'is_active' => true,
        ]);

        Position::create([
            'code' => 'SAL-MGR',
            'name' => 'Sales Manager',
            'description' => 'Manages sales team and operations',
            'is_active' => true,
        ]);

        Position::create([
            'code' => 'WHS-MGR',
            'name' => 'Warehouse Manager',
            'description' => 'Manages warehouse operations',
            'is_active' => true,
        ]);

        // Seed Warehouses
        $this->command->info('Seeding warehouses...');
        Warehouse::create([
            'branch_id' => $jakarta->id,
            'code' => 'WH-JKT-01',
            'name' => 'Gudang Jakarta Utama',
            'address' => 'Jl. Industri No. 100, Jakarta',
            'is_active' => true,
        ]);

        Warehouse::create([
            'branch_id' => $bandung->id,
            'code' => 'WH-BDG-01',
            'name' => 'Gudang Bandung',
            'address' => 'Jl. Soekarno Hatta No. 200, Bandung',
            'is_active' => true,
        ]);

        // Seed Users
        $this->command->info('Seeding users...');
        $admin = User::firstOrCreate(
            ['email' => 'admin@akubook.com'],
            [
                'name' => 'Administrator',
                'password' => Hash::make('password'),
                'branch_id' => $jakarta->id,
                'email_verified_at' => now(),
            ]
        );

        User::create([
            'name' => 'John Doe',
            'email' => 'john@akubook.com',
            'password' => Hash::make('password'),
            'branch_id' => $jakarta->id,
            'email_verified_at' => now(),
        ]);

        User::create([
            'name' => 'Jane Smith',
            'email' => 'jane@akubook.com',
            'password' => Hash::make('password'),
            'branch_id' => $bandung->id,
            'email_verified_at' => now(),
        ]);

        // Seed Customers
        $this->command->info('Seeding customers...');
        Customer::create([
            'code' => 'CUST-001',
            'name' => 'PT Maju Jaya',
            'customer_type' => 'company',
            'contact_person' => 'Budi Santoso',
            'address' => 'Jl. Gatot Subroto No. 50, Jakarta',
            'city' => 'Jakarta',
            'phone' => '021-55555555',
            'email' => 'info@majujaya.com',
            'tax_id' => '01.234.567.8-901.000',
            'credit_limit' => 50000000,
            'payment_terms_days' => 30,
            'is_active' => true,
        ]);

        Customer::create([
            'code' => 'CUST-002',
            'name' => 'CV Sejahtera Abadi',
            'customer_type' => 'company',
            'contact_person' => 'Siti Rahayu',
            'address' => 'Jl. Braga No. 75, Bandung',
            'city' => 'Bandung',
            'phone' => '022-99999999',
            'email' => 'contact@sejahtera.com',
            'tax_id' => '02.345.678.9-012.000',
            'credit_limit' => 30000000,
            'payment_terms_days' => 14,
            'is_active' => true,
        ]);

        Customer::create([
            'code' => 'CUST-003',
            'name' => 'Toko Elektronik Jaya',
            'customer_type' => 'company',
            'contact_person' => 'Ahmad Wijaya',
            'address' => 'Jl. Tunjungan No. 100, Surabaya',
            'city' => 'Surabaya',
            'phone' => '031-22334455',
            'email' => 'jaya@elektronik.com',
            'credit_limit' => 20000000,
            'payment_terms_days' => 7,
            'is_active' => true,
        ]);

        // Note: Suppliers and Items tables need migration completion before seeding
        $this->command->info('Skipping suppliers and items (migrations incomplete)...');

        $this->command->info('Development seeding completed successfully!');
    }
}

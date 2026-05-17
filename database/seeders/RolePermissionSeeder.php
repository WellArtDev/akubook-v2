<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            'users.view', 'users.create', 'users.edit', 'users.delete',
            'roles.view', 'roles.create', 'roles.edit', 'roles.delete',
            'customers.view', 'customers.create', 'customers.edit', 'customers.delete',
            'sales-orders.view', 'sales-orders.create', 'sales-orders.edit', 'sales-orders.delete', 'sales-orders.confirm',
            'branches.view', 'branches.create', 'branches.edit', 'branches.delete',
            'departments.view', 'departments.create', 'departments.edit', 'departments.delete',
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        $admin = Role::create(['name' => 'Administrator']);
        $salesManager = Role::create(['name' => 'Sales Manager']);
        $salesStaff = Role::create(['name' => 'Sales Staff']);

        $admin->givePermissionTo(Permission::all());
        
        $salesManager->givePermissionTo([
            'customers.view', 'customers.create', 'customers.edit',
            'sales-orders.view', 'sales-orders.create', 'sales-orders.edit', 'sales-orders.confirm',
        ]);
        
        $salesStaff->givePermissionTo([
            'customers.view',
            'sales-orders.view', 'sales-orders.create',
        ]);

        $adminUser = User::create([
            'name' => 'Administrator',
            'email' => 'admin@akubook.test',
            'password' => bcrypt('password'),
            'email_verified_at' => now(),
        ]);
        
        $adminUser->assignRole('Administrator');
    }
}

<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class BootstrapLocalCommand extends Command
{
    protected $signature = 'app:bootstrap-local {--email=admin@akubook.com} {--password=password}';

    protected $description = 'Bootstrap deterministic local admin, roles, permissions, and critical schema checks';

    public function handle(): int
    {
        $missingTables = $this->missingCriticalTables();

        if (! empty($missingTables)) {
            $this->error('Critical schema missing: '.implode(', ', $missingTables));
            $this->line('Run migrations before bootstrap: php artisan migrate --force');

            return self::FAILURE;
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permissions = $this->ensurePermissions();
        $role = Role::firstOrCreate(['name' => 'Administrator', 'guard_name' => 'web']);
        $role->syncPermissions($permissions);

        $email = (string) $this->option('email');
        $password = (string) $this->option('password');

        $user = User::updateOrCreate(
            ['email' => $email],
            [
                'name' => 'Admin AkuBook',
                'password' => Hash::make($password),
            ]
        );
        $user->forceFill(['email_verified_at' => now()])->save();

        if (! $user->hasRole($role->name)) {
            $user->assignRole($role);
        }

        $this->info('Local bootstrap completed.');
        $this->line("Admin: {$email}");
        $this->line('Password: '.$password);
        $this->line('Role: Administrator');

        return self::SUCCESS;
    }

    private function ensurePermissions(): array
    {
        $permissions = [
            'users.view', 'users.create', 'users.edit', 'users.delete',
            'roles.view', 'roles.create', 'roles.edit', 'roles.delete',
            'customers.view', 'customers.create', 'customers.edit', 'customers.delete',
            'suppliers.view', 'suppliers.create', 'suppliers.edit', 'suppliers.delete',
            'sales-orders.view', 'sales-orders.create', 'sales-orders.edit', 'sales-orders.delete', 'sales-orders.confirm',
            'purchase-orders.view', 'purchase-orders.create', 'purchase-orders.edit', 'purchase-orders.delete', 'purchase-orders.approve',
            'governance.view', 'governance.run',
            'reports.view',
            'branches.view', 'branches.create', 'branches.edit', 'branches.delete',
            'departments.view', 'departments.create', 'departments.edit', 'departments.delete',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        return $permissions;
    }

    private function missingCriticalTables(): array
    {
        $tables = [
            'users',
            'roles',
            'permissions',
            'model_has_roles',
            'customers',
            'suppliers',
            'sales_orders',
            'sales_invoices',
            'customer_payments',
            'purchase_orders',
            'purchase_invoices',
            'supplier_payments',
            'data_retention_executions',
            'sensitive_alerts',
            'compliance_export_packs',
        ];

        return array_values(array_filter($tables, fn (string $table) => ! Schema::hasTable($table)));
    }
}

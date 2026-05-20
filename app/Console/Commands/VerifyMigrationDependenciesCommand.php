<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Throwable;

class VerifyMigrationDependenciesCommand extends Command
{
    protected $signature = 'app:verify-migration-dependencies {--simulate-missing-parent : Simulate child-parent mismatch to verify fail mode}';

    protected $description = 'Verify fragile migration dependencies on a fresh isolated sqlite database';

    public function handle(): int
    {
        $dbPath = database_path('migration-guard.sqlite');

        try {
            $this->prepareIsolatedDatabase($dbPath);
            $this->configureSqliteGuardConnection($dbPath);

            $result = Artisan::call('migrate:fresh', [
                '--database' => 'migration_guard',
                '--force' => true,
            ]);

            if ($result !== self::SUCCESS) {
                $this->error('Migration guard failed during migrate:fresh.');
                $this->line(Artisan::output());

                return self::FAILURE;
            }

            $issues = $this->verifyFragileDependencies();

            if ($this->option('simulate-missing-parent')) {
                $issues[] = 'Simulation enabled: child migration detected before parent table.';
            }

            if (! empty($issues)) {
                $this->error('Migration dependency guard failed:');
                foreach ($issues as $issue) {
                    $this->line('- '.$issue);
                }

                return self::FAILURE;
            }

            $this->info('Migration dependency guard passed.');
            $this->line('Checked: sales_return_lines, stock opname, payroll, attendance dependencies.');

            return self::SUCCESS;
        } catch (Throwable $exception) {
            $this->error('Migration dependency guard crashed: '.$exception->getMessage());

            return self::FAILURE;
        } finally {
            Artisan::call('db:wipe', ['--database' => 'migration_guard', '--force' => true]);
            if (File::exists($dbPath)) {
                File::delete($dbPath);
            }
        }
    }

    private function prepareIsolatedDatabase(string $dbPath): void
    {
        if (File::exists($dbPath)) {
            File::delete($dbPath);
        }

        File::put($dbPath, '');
    }

    private function configureSqliteGuardConnection(string $dbPath): void
    {
        Config::set('database.connections.migration_guard', [
            'driver' => 'sqlite',
            'database' => $dbPath,
            'prefix' => '',
            'foreign_key_constraints' => true,
        ]);

        DB::purge('migration_guard');
        DB::reconnect('migration_guard');
    }

    private function verifyFragileDependencies(): array
    {
        $checks = [
            ['sales_returns', 'sales_return_lines', 'sales_return_lines.sales_return_id -> sales_returns.id'],
            ['sales_invoice_lines', 'sales_return_lines', 'sales_return_lines.sales_invoice_line_id -> sales_invoice_lines.id'],
            ['stock_opnames', 'stock_opname_lines', 'stock_opname_lines.stock_opname_id -> stock_opnames.id'],
            ['payroll_runs', 'payroll_run_lines', 'payroll_run_lines.payroll_run_id -> payroll_runs.id'],
            ['payroll_run_lines', 'payroll_bank_transfer_lines', 'payroll_bank_transfer_lines.payroll_run_line_id -> payroll_run_lines.id'],
            ['employees', 'zkteco_attendance_logs', 'zkteco_attendance_logs.employee_id -> employees.id'],
            ['attendance_records', 'zkteco_attendance_logs', 'zkteco_attendance_logs.attendance_record_id -> attendance_records.id'],
        ];

        $issues = [];
        foreach ($checks as [$parent, $child, $label]) {
            $parentExists = DB::connection('migration_guard')->getSchemaBuilder()->hasTable($parent);
            $childExists = DB::connection('migration_guard')->getSchemaBuilder()->hasTable($child);

            if (! $parentExists || ! $childExists) {
                $issues[] = "Missing table dependency for {$label}";
            }
        }

        return $issues;
    }
}

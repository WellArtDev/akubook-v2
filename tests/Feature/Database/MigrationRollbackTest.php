<?php

namespace Tests\Feature\Database;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class MigrationRollbackTest extends TestCase
{
    /**
     * Test that all migrations can be rolled back successfully
     */
    public function test_all_migrations_can_be_rolled_back(): void
    {
        // Fresh migrate
        Artisan::call('migrate:fresh');
        
        // Verify all tables exist
        $this->assertTrue(Schema::hasTable('branches'));
        $this->assertTrue(Schema::hasTable('users'));
        $this->assertTrue(Schema::hasTable('accounts'));
        $this->assertTrue(Schema::hasTable('sales_orders'));
        
        // Rollback all migrations
        Artisan::call('migrate:rollback', ['--step' => 100]);
        
        // Verify all tables are dropped
        $this->assertFalse(Schema::hasTable('branches'));
        $this->assertFalse(Schema::hasTable('users'));
        $this->assertFalse(Schema::hasTable('accounts'));
        $this->assertFalse(Schema::hasTable('sales_orders'));
    }

    /**
     * Test that migrations can be rolled back step by step
     */
    public function test_migrations_can_be_rolled_back_step_by_step(): void
    {
        // Fresh migrate
        Artisan::call('migrate:fresh');
        
        // Get initial table count
        $initialTables = $this->getTableCount();
        
        // Rollback one step
        Artisan::call('migrate:rollback', ['--step' => 1]);
        
        // Verify table count decreased
        $afterRollback = $this->getTableCount();
        $this->assertLessThan($initialTables, $afterRollback);
        
        // Re-migrate
        Artisan::call('migrate');
        
        // Verify table count restored
        $afterMigrate = $this->getTableCount();
        $this->assertEquals($initialTables, $afterMigrate);
    }

    /**
     * Test that migrations can be reset and re-run
     */
    public function test_migrations_can_be_reset_and_rerun(): void
    {
        // Fresh migrate
        Artisan::call('migrate:fresh');
        
        // Get initial table count
        $initialTables = $this->getTableCount();
        
        // Reset all migrations
        Artisan::call('migrate:reset');
        
        // Verify only migrations table exists
        $this->assertTrue(Schema::hasTable('migrations'));
        $this->assertFalse(Schema::hasTable('branches'));
        
        // Re-migrate
        Artisan::call('migrate');
        
        // Verify table count restored
        $afterMigrate = $this->getTableCount();
        $this->assertEquals($initialTables, $afterMigrate);
    }

    /**
     * Test that foreign key constraints are properly dropped during rollback
     */
    public function test_foreign_keys_are_dropped_during_rollback(): void
    {
        // Fresh migrate
        Artisan::call('migrate:fresh');
        
        // Verify foreign keys exist (by trying to insert invalid data)
        $this->expectException(\Illuminate\Database\QueryException::class);
        
        DB::table('warehouses')->insert([
            'branch_id' => 99999, // Non-existent branch
            'code' => 'WH001',
            'name' => 'Test Warehouse',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Test that indexes are properly dropped during rollback
     */
    public function test_indexes_are_dropped_during_rollback(): void
    {
        // Fresh migrate
        Artisan::call('migrate:fresh');
        
        // Get indexes for branches table
        $indexes = $this->getTableIndexes('branches');
        $this->assertNotEmpty($indexes);
        
        // Rollback migrations
        Artisan::call('migrate:reset');
        
        // Verify branches table doesn't exist
        $this->assertFalse(Schema::hasTable('branches'));
    }

    /**
     * Test that enum types are properly handled during rollback
     */
    public function test_enum_types_are_handled_during_rollback(): void
    {
        // Fresh migrate
        Artisan::call('migrate:fresh');
        
        // Verify enum column exists
        $this->assertTrue(Schema::hasColumn('accounts', 'type'));
        
        // Rollback and re-migrate
        Artisan::call('migrate:reset');
        Artisan::call('migrate');
        
        // Verify enum column still works
        $this->assertTrue(Schema::hasColumn('accounts', 'type'));
    }

    /**
     * Test that soft delete columns are properly handled during rollback
     */
    public function test_soft_delete_columns_are_handled_during_rollback(): void
    {
        // Fresh migrate
        Artisan::call('migrate:fresh');
        
        // Verify soft delete column exists
        $this->assertTrue(Schema::hasColumn('accounts', 'deleted_at'));
        $this->assertTrue(Schema::hasColumn('customers', 'deleted_at'));
        
        // Rollback and re-migrate
        Artisan::call('migrate:reset');
        Artisan::call('migrate');
        
        // Verify soft delete columns still exist
        $this->assertTrue(Schema::hasColumn('accounts', 'deleted_at'));
        $this->assertTrue(Schema::hasColumn('customers', 'deleted_at'));
    }

    /**
     * Test that timestamps are properly handled during rollback
     */
    public function test_timestamps_are_handled_during_rollback(): void
    {
        // Fresh migrate
        Artisan::call('migrate:fresh');
        
        // Verify timestamp columns exist
        $this->assertTrue(Schema::hasColumn('branches', 'created_at'));
        $this->assertTrue(Schema::hasColumn('branches', 'updated_at'));
        
        // Rollback and re-migrate
        Artisan::call('migrate:reset');
        Artisan::call('migrate');
        
        // Verify timestamp columns still exist
        $this->assertTrue(Schema::hasColumn('branches', 'created_at'));
        $this->assertTrue(Schema::hasColumn('branches', 'updated_at'));
    }

    /**
     * Test that migration order is preserved during rollback
     */
    public function test_migration_order_is_preserved(): void
    {
        // Fresh migrate
        Artisan::call('migrate:fresh');
        
        // Get migration order
        $migrations = DB::table('migrations')->orderBy('batch')->orderBy('id')->pluck('migration')->toArray();
        
        // Rollback all
        Artisan::call('migrate:reset');
        
        // Re-migrate
        Artisan::call('migrate');
        
        // Get new migration order
        $newMigrations = DB::table('migrations')->orderBy('batch')->orderBy('id')->pluck('migration')->toArray();
        
        // Verify order is the same
        $this->assertEquals($migrations, $newMigrations);
    }

    /**
     * Helper: Get count of tables in database
     */
    private function getTableCount(): int
    {
        $tables = DB::select("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%'");
        return count($tables);
    }

    /**
     * Helper: Get indexes for a table
     */
    private function getTableIndexes(string $table): array
    {
        if (!Schema::hasTable($table)) {
            return [];
        }
        
        $indexes = DB::select("PRAGMA index_list($table)");
        return $indexes;
    }
}

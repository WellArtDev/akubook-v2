<?php

namespace Tests\Feature\Database;

use App\Models\Branch;
use App\Models\Customer;
use App\Models\SalesOrder;
use App\Models\SalesOrderLine;
use App\Models\User;
use App\Models\Account;
use App\Models\FiscalPeriod;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class DataIntegrityConstraintsTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test foreign key constraint: warehouses.branch_id -> branches.id
     */
    public function test_warehouse_requires_valid_branch(): void
    {
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
     * Test foreign key constraint: users.branch_id -> branches.id (nullable)
     */
    public function test_user_can_have_null_branch(): void
    {
        $user = User::factory()->create([
            'branch_id' => null,
        ]);

        $this->assertNull($user->branch_id);
    }

    /**
     * Test foreign key constraint: sales_orders.customer_id -> customers.id
     */
    public function test_sales_order_requires_valid_customer(): void
    {
        $this->expectException(\Illuminate\Database\QueryException::class);
        
        $branch = Branch::factory()->create();
        
        DB::table('sales_orders')->insert([
            'customer_id' => 99999, // Non-existent customer
            'branch_id' => $branch->id,
            'order_number' => 'SO-001',
            'order_date' => now(),
            'status' => 'draft',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Test foreign key constraint: sales_orders.branch_id -> branches.id
     */
    public function test_sales_order_requires_valid_branch(): void
    {
        $this->expectException(\Illuminate\Database\QueryException::class);
        
        $customer = Customer::factory()->create();
        
        DB::table('sales_orders')->insert([
            'customer_id' => $customer->id,
            'branch_id' => 99999, // Non-existent branch
            'order_number' => 'SO-001',
            'order_date' => now(),
            'status' => 'draft',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Test cascade delete: sales_orders -> sales_order_lines
     */
    public function test_deleting_sales_order_cascades_to_lines(): void
    {
        $branch = Branch::factory()->create();
        $customer = Customer::factory()->create();
        
        $order = SalesOrder::factory()->create([
            'customer_id' => $customer->id,
            'branch_id' => $branch->id,
        ]);
        
        $line = SalesOrderLine::factory()->create([
            'sales_order_id' => $order->id,
        ]);

        $lineId = $line->id;
        
        // Delete parent order
        $order->delete();
        
        // Verify line is also deleted
        $this->assertDatabaseMissing('sales_order_lines', ['id' => $lineId]);
    }

    /**
     * Test restrict delete: Cannot delete customer with orders
     */
    public function test_cannot_delete_customer_with_orders(): void
    {
        $this->expectException(\Illuminate\Database\QueryException::class);
        
        $branch = Branch::factory()->create();
        $customer = Customer::factory()->create();
        
        SalesOrder::factory()->create([
            'customer_id' => $customer->id,
            'branch_id' => $branch->id,
        ]);
        
        // Try to delete customer - should fail
        DB::table('customers')->where('id', $customer->id)->delete();
    }

    /**
     * Test restrict delete: Cannot delete branch with warehouses
     */
    public function test_cannot_delete_branch_with_warehouses(): void
    {
        $this->expectException(\Illuminate\Database\QueryException::class);
        
        $branch = Branch::factory()->create();
        
        DB::table('warehouses')->insert([
            'branch_id' => $branch->id,
            'code' => 'WH001',
            'name' => 'Test Warehouse',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        // Try to delete branch - should fail
        DB::table('branches')->where('id', $branch->id)->delete();
    }

    /**
     * Test unique constraint: branches.code
     */
    public function test_branch_code_must_be_unique(): void
    {
        $this->expectException(\Illuminate\Database\QueryException::class);
        
        Branch::factory()->create(['code' => 'BR001']);
        Branch::factory()->create(['code' => 'BR001']); // Duplicate
    }

    /**
     * Test unique constraint: users.email
     */
    public function test_user_email_must_be_unique(): void
    {
        $this->expectException(\Illuminate\Database\QueryException::class);
        
        User::factory()->create(['email' => 'test@example.com']);
        User::factory()->create(['email' => 'test@example.com']); // Duplicate
    }

    /**
     * Test unique constraint: accounts.code
     */
    public function test_account_code_must_be_unique(): void
    {
        $this->expectException(\Illuminate\Database\QueryException::class);
        
        Account::factory()->create(['code' => '1000']);
        Account::factory()->create(['code' => '1000']); // Duplicate
    }

    /**
     * Test check constraint: journal_entry_lines debit/credit
     */
    public function test_journal_line_cannot_have_both_debit_and_credit(): void
    {
        $this->expectException(\Illuminate\Database\QueryException::class);
        
        $fiscalPeriod = FiscalPeriod::factory()->create();
        $account = Account::factory()->create();
        $entry = JournalEntry::factory()->create([
            'fiscal_period_id' => $fiscalPeriod->id,
        ]);
        
        // Try to create line with both debit and credit
        DB::table('journal_entry_lines')->insert([
            'journal_entry_id' => $entry->id,
            'account_id' => $account->id,
            'debit' => 100.00,
            'credit' => 100.00, // Should fail - can't have both
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Test check constraint: sales_order_lines quantity > 0
     */
    public function test_sales_order_line_quantity_must_be_positive(): void
    {
        $this->expectException(\Illuminate\Database\QueryException::class);
        
        $branch = Branch::factory()->create();
        $customer = Customer::factory()->create();
        $order = SalesOrder::factory()->create([
            'customer_id' => $customer->id,
            'branch_id' => $branch->id,
        ]);
        
        // Try to create line with zero quantity
        DB::table('sales_order_lines')->insert([
            'sales_order_id' => $order->id,
            'item_id' => 1,
            'quantity' => 0, // Should fail
            'unit_price' => 100.00,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Test check constraint: sales_order_lines unit_price >= 0
     */
    public function test_sales_order_line_unit_price_cannot_be_negative(): void
    {
        $this->expectException(\Illuminate\Database\QueryException::class);
        
        $branch = Branch::factory()->create();
        $customer = Customer::factory()->create();
        $order = SalesOrder::factory()->create([
            'customer_id' => $customer->id,
            'branch_id' => $branch->id,
        ]);
        
        // Try to create line with negative price
        DB::table('sales_order_lines')->insert([
            'sales_order_id' => $order->id,
            'item_id' => 1,
            'quantity' => 1,
            'unit_price' => -100.00, // Should fail
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Test enum constraint: accounts.account_type
     */
    public function test_account_type_must_be_valid_enum(): void
    {
        $this->expectException(\Illuminate\Database\QueryException::class);
        
        Account::factory()->create([
            'account_type' => 'invalid_type', // Should fail
        ]);
    }

    /**
     * Test enum constraint: accounts.normal_balance
     */
    public function test_account_normal_balance_must_be_valid_enum(): void
    {
        $this->expectException(\Illuminate\Database\QueryException::class);
        
        Account::factory()->create([
            'normal_balance' => 'invalid_balance', // Should fail
        ]);
    }

    /**
     * Test enum constraint: fiscal_periods.status
     */
    public function test_fiscal_period_status_must_be_valid_enum(): void
    {
        $this->expectException(\Illuminate\Database\QueryException::class);
        
        FiscalPeriod::factory()->create([
            'status' => 'invalid_status', // Should fail
        ]);
    }

    /**
     * Test enum constraint: journal_entries.status
     */
    public function test_journal_entry_status_must_be_valid_enum(): void
    {
        $this->expectException(\Illuminate\Database\QueryException::class);
        
        $fiscalPeriod = FiscalPeriod::factory()->create();
        
        JournalEntry::factory()->create([
            'fiscal_period_id' => $fiscalPeriod->id,
            'status' => 'invalid_status', // Should fail
        ]);
    }

    /**
     * Test enum constraint: customers.customer_type
     */
    public function test_customer_type_must_be_valid_enum(): void
    {
        $this->expectException(\Illuminate\Database\QueryException::class);
        
        Customer::factory()->create([
            'customer_type' => 'invalid_type', // Should fail
        ]);
    }

    /**
     * Test enum constraint: sales_orders.status
     */
    public function test_sales_order_status_must_be_valid_enum(): void
    {
        $this->expectException(\Illuminate\Database\QueryException::class);
        
        $branch = Branch::factory()->create();
        $customer = Customer::factory()->create();
        
        SalesOrder::factory()->create([
            'customer_id' => $customer->id,
            'branch_id' => $branch->id,
            'status' => 'invalid_status', // Should fail
        ]);
    }

    /**
     * Test self-referencing foreign key: accounts.parent_id
     */
    public function test_account_parent_must_be_valid_account(): void
    {
        $this->expectException(\Illuminate\Database\QueryException::class);
        
        Account::factory()->create([
            'parent_id' => 99999, // Non-existent parent
        ]);
    }

    /**
     * Test self-referencing foreign key: accounts can have null parent
     */
    public function test_account_can_have_null_parent(): void
    {
        $account = Account::factory()->create([
            'parent_id' => null,
        ]);

        $this->assertNull($account->parent_id);
    }

    /**
     * Test soft delete: deleted records are not returned by default
     */
    public function test_soft_deleted_records_are_hidden(): void
    {
        $branch = Branch::factory()->create();
        
        $branch->delete();
        
        $this->assertDatabaseHas('branches', [
            'id' => $branch->id,
        ]);
        
        $this->assertNull(Branch::find($branch->id));
    }

    /**
     * Test soft delete: can restore deleted records
     */
    public function test_can_restore_soft_deleted_records(): void
    {
        $branch = Branch::factory()->create();
        
        $branch->delete();
        $this->assertNull(Branch::find($branch->id));
        
        $branch->restore();
        $this->assertNotNull(Branch::find($branch->id));
    }

    /**
     * Test default values: is_active defaults to true
     */
    public function test_is_active_defaults_to_true(): void
    {
        $branch = Branch::factory()->create([
            'is_active' => null, // Let default apply
        ]);

        $this->assertTrue($branch->is_active);
    }

    /**
     * Test default values: decimal fields default to 0
     */
    public function test_decimal_fields_default_to_zero(): void
    {
        $branch = Branch::factory()->create();
        $customer = Customer::factory()->create();
        
        $order = SalesOrder::factory()->create([
            'customer_id' => $customer->id,
            'branch_id' => $branch->id,
            'subtotal' => null, // Let default apply
            'tax_amount' => null,
            'discount_amount' => null,
            'total_amount' => null,
        ]);

        $this->assertEquals(0, $order->subtotal);
        $this->assertEquals(0, $order->tax_amount);
        $this->assertEquals(0, $order->discount_amount);
        $this->assertEquals(0, $order->total_amount);
    }

    /**
     * Test index performance: queries with indexed columns are fast
     */
    public function test_indexed_queries_perform_well(): void
    {
        // Create test data
        Branch::factory()->count(100)->create();
        
        $startTime = microtime(true);
        
        // Query by indexed column (code)
        $branch = Branch::where('code', 'BR001')->first();
        
        $endTime = microtime(true);
        $executionTime = ($endTime - $startTime) * 1000; // Convert to milliseconds
        
        // Should be very fast (< 100ms even with 100 records)
        $this->assertLessThan(100, $executionTime);
    }

    /**
     * Test polymorphic relationship: audit_logs.auditable
     */
    public function test_audit_log_can_reference_any_model(): void
    {
        $user = User::factory()->create();
        $branch = Branch::factory()->create();
        
        DB::table('audit_logs')->insert([
            'user_id' => $user->id,
            'event' => 'created',
            'auditable_type' => Branch::class,
            'auditable_id' => $branch->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        $this->assertDatabaseHas('audit_logs', [
            'auditable_type' => Branch::class,
            'auditable_id' => $branch->id,
        ]);
    }

    /**
     * Test Spatie permission: role assignment
     */
    public function test_user_can_be_assigned_roles(): void
    {
        $user = User::factory()->create();
        
        $role = DB::table('roles')->insertGetId([
            'name' => 'admin',
            'guard_name' => 'web',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        DB::table('model_has_roles')->insert([
            'role_id' => $role,
            'model_type' => User::class,
            'model_id' => $user->id,
        ]);
        
        $this->assertDatabaseHas('model_has_roles', [
            'role_id' => $role,
            'model_id' => $user->id,
        ]);
    }

    /**
     * Test Spatie permission: permission assignment to role
     */
    public function test_permissions_can_be_assigned_to_roles(): void
    {
        $role = DB::table('roles')->insertGetId([
            'name' => 'admin',
            'guard_name' => 'web',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        $permission = DB::table('permissions')->insertGetId([
            'name' => 'create-users',
            'guard_name' => 'web',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        DB::table('role_has_permissions')->insert([
            'role_id' => $role,
            'permission_id' => $permission,
        ]);
        
        $this->assertDatabaseHas('role_has_permissions', [
            'role_id' => $role,
            'permission_id' => $permission,
        ]);
    }
}

<?php

namespace Tests\Unit\Models;

use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderLine;
use App\Models\PurchaseRequest;
use App\Models\PurchaseRequestLine;
use App\Models\User;
use App\Models\Supplier;
use App\Models\Department;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PurchaseOrderTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate');
    }

    public function test_it_generates_unique_po_numbers()
    {
        $user = User::factory()->create();
        $supplier = Supplier::factory()->create();

        $po1 = PurchaseOrder::create([
            'po_number' => PurchaseOrder::generatePONumber(),
            'po_date' => now(),
            'supplier_id' => $supplier->id,
            'created_by' => $user->id,
        ]);

        $po2 = PurchaseOrder::create([
            'po_number' => PurchaseOrder::generatePONumber(),
            'po_date' => now(),
            'supplier_id' => $supplier->id,
            'created_by' => $user->id,
        ]);

        $this->assertNotEquals($po1->po_number, $po2->po_number);
        $this->assertStringStartsWith('PO-' . now()->year . '-', $po1->po_number);
    }

    public function test_it_calculates_totals_from_lines()
    {
        $user = User::factory()->create();
        $supplier = Supplier::factory()->create();

        $po = PurchaseOrder::create([
            'po_number' => PurchaseOrder::generatePONumber(),
            'po_date' => now(),
            'supplier_id' => $supplier->id,
            'created_by' => $user->id,
        ]);

        PurchaseOrderLine::create([
            'purchase_order_id' => $po->id,
            'line_number' => 1,
            'product_name' => 'Product A',
            'quantity' => 10,
            'unit' => 'pcs',
            'unit_price' => 100,
            'tax_amount' => 10,
        ]);

        PurchaseOrderLine::create([
            'purchase_order_id' => $po->id,
            'line_number' => 2,
            'product_name' => 'Product B',
            'quantity' => 5,
            'unit' => 'pcs',
            'unit_price' => 200,
            'tax_amount' => 20,
        ]);

        $po->refresh();
        $this->assertEquals(2030, $po->grand_total); // (10*100+10) + (5*200+20)
    }

    public function test_it_requires_approval_above_threshold()
    {
        $user = User::factory()->create();
        $supplier = Supplier::factory()->create();

        $po = PurchaseOrder::create([
            'po_number' => PurchaseOrder::generatePONumber(),
            'po_date' => now(),
            'supplier_id' => $supplier->id,
            'grand_total' => 15000000,
            'created_by' => $user->id,
        ]);

        $this->assertTrue($po->requiresApproval());
    }

    public function test_it_submits_for_approval_when_above_threshold()
    {
        $user = User::factory()->create();
        $supplier = Supplier::factory()->create();

        $po = PurchaseOrder::create([
            'po_number' => PurchaseOrder::generatePONumber(),
            'po_date' => now(),
            'supplier_id' => $supplier->id,
            'grand_total' => 15000000,
            'status' => 'draft',
            'created_by' => $user->id,
        ]);

        $po->submit();

        $this->assertEquals('pending_approval', $po->status);
    }

    public function test_it_auto_approves_when_below_threshold()
    {
        $user = User::factory()->create();
        $supplier = Supplier::factory()->create();

        $po = PurchaseOrder::create([
            'po_number' => PurchaseOrder::generatePONumber(),
            'po_date' => now(),
            'supplier_id' => $supplier->id,
            'grand_total' => 5000000,
            'status' => 'draft',
            'created_by' => $user->id,
        ]);

        $po->submit();

        $this->assertEquals('approved', $po->status);
    }

    public function test_it_can_approve_pending_po()
    {
        $user = User::factory()->create();
        $approver = User::factory()->create();
        $supplier = Supplier::factory()->create();

        $po = PurchaseOrder::create([
            'po_number' => PurchaseOrder::generatePONumber(),
            'po_date' => now(),
            'supplier_id' => $supplier->id,
            'status' => 'pending_approval',
            'created_by' => $user->id,
        ]);

        $po->approve($approver->id);

        $this->assertEquals('approved', $po->status);
        $this->assertEquals($approver->id, $po->approved_by);
    }

    public function test_it_can_create_from_prs()
    {
        $user = User::factory()->create();
        $supplier = Supplier::factory()->create();
        $department = Department::factory()->create();

        // Create approved PR
        $pr = PurchaseRequest::create([
            'pr_number' => PurchaseRequest::generatePRNumber(),
            'pr_date' => now(),
            'department_id' => $department->id,
            'required_date' => now()->addDays(7),
            'status' => 'approved',
            'created_by' => $user->id,
        ]);

        PurchaseRequestLine::create([
            'purchase_request_id' => $pr->id,
            'line_number' => 1,
            'product_name' => 'Product A',
            'quantity' => 10,
            'unit' => 'pcs',
            'estimated_price' => 100,
        ]);

        $this->actingAs($user);

        $po = PurchaseOrder::createFromPRs([$pr->id], [
            'po_date' => now(),
            'supplier_id' => $supplier->id,
        ]);

        $this->assertNotNull($po);
        $this->assertEquals(1, $po->lines->count());
        $this->assertEquals('Product A', $po->lines->first()->product_name);
        
        $pr->refresh();
        $this->assertEquals('converted', $pr->status);
    }
}

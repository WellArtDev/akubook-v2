<?php

namespace Tests\Unit;

use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderLine;
use App\Models\Supplier;
use App\Models\Branch;
use App\Models\Item;
use App\Models\User;
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

    public function test_it_can_calculate_totals()
    {
        $supplier = Supplier::factory()->create();
        $user = User::factory()->create();
        
        $po = PurchaseOrder::create([
            'po_number' => 'PO-2026-0001',
            'po_date' => '2026-05-15',
            'supplier_id' => $supplier->id,
            'status' => 'draft',
            'created_by' => $user->id,
        ]);

        $item = Item::factory()->create();

        $po->lines()->create([
            'line_number' => 1,
            'product_code' => $item->code,
            'product_name' => $item->name,
            'quantity' => 10,
            'unit' => 'pcs',
            'unit_price' => 1000,
            'tax_amount' => 100,
            'line_total' => 10000,
        ]);

        $po->lines()->create([
            'line_number' => 2,
            'product_code' => $item->code,
            'product_name' => $item->name,
            'quantity' => 5,
            'unit' => 'pcs',
            'unit_price' => 2000,
            'tax_amount' => 100,
            'line_total' => 10000,
        ]);

        $po->calculateTotals();

        $this->assertEquals(20000, $po->subtotal);
        $this->assertEquals(200, $po->tax_amount);
        $this->assertEquals(20200, $po->grand_total);
    }

    public function test_it_requires_approval_when_total_exceeds_threshold()
    {
        $supplier = Supplier::factory()->create();
        $user = User::factory()->create();
        
        $po = PurchaseOrder::create([
            'po_number' => 'PO-2026-0001',
            'po_date' => '2026-05-15',
            'supplier_id' => $supplier->id,
            'status' => 'draft',
            'grand_total' => 15000000,
            'created_by' => $user->id,
        ]);

        $this->assertTrue($po->requiresApproval());
    }

    public function test_it_does_not_require_approval_when_total_below_threshold()
    {
        $supplier = Supplier::factory()->create();
        $user = User::factory()->create();
        
        $po = PurchaseOrder::create([
            'po_number' => 'PO-2026-0001',
            'po_date' => '2026-05-15',
            'supplier_id' => $supplier->id,
            'status' => 'draft',
            'grand_total' => 5000000,
            'created_by' => $user->id,
        ]);

        $this->assertFalse($po->requiresApproval());
    }

    public function test_it_has_supplier_relationship()
    {
        $supplier = Supplier::factory()->create();
        $user = User::factory()->create();
        
        $po = PurchaseOrder::create([
            'po_number' => 'PO-2026-0001',
            'po_date' => '2026-05-15',
            'supplier_id' => $supplier->id,
            'status' => 'draft',
            'created_by' => $user->id,
        ]);

        $this->assertInstanceOf(Supplier::class, $po->supplier);
        $this->assertEquals($supplier->id, $po->supplier->id);
    }

    public function test_it_has_lines_relationship()
    {
        $supplier = Supplier::factory()->create();
        $user = User::factory()->create();
        $item = Item::factory()->create();
        
        $po = PurchaseOrder::create([
            'po_number' => 'PO-2026-0001',
            'po_date' => '2026-05-15',
            'supplier_id' => $supplier->id,
            'status' => 'draft',
            'created_by' => $user->id,
        ]);

        $po->lines()->create([
            'line_number' => 1,
            'product_code' => $item->code,
            'product_name' => $item->name,
            'quantity' => 10,
            'unit' => 'pcs',
            'unit_price' => 1000,
            'line_total' => 10000,
        ]);

        $this->assertCount(1, $po->lines);
        $this->assertInstanceOf(PurchaseOrderLine::class, $po->lines->first());
    }
}

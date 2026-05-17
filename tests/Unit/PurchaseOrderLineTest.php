<?php

namespace Tests\Unit;

use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderLine;
use App\Models\Item;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PurchaseOrderLineTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate');
    }

    public function test_it_can_calculate_line_total()
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

        $line = $po->lines()->create([
            'line_number' => 1,
            'item_id' => $item->id,
            'quantity' => 10,
            'unit' => 'pcs',
            'unit_price' => 1500,
            'line_total' => 0,
        ]);

        $line->calculateLineTotal();

        $this->assertEquals(15000, $line->line_total);
    }

    public function test_it_belongs_to_purchase_order()
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

        $line = $po->lines()->create([
            'line_number' => 1,
            'item_id' => $item->id,
            'quantity' => 10,
            'unit' => 'pcs',
            'unit_price' => 1000,
            'line_total' => 10000,
        ]);

        $this->assertInstanceOf(PurchaseOrder::class, $line->purchaseOrder);
        $this->assertEquals($po->id, $line->purchaseOrder->id);
    }

    public function test_it_belongs_to_item()
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

        $line = $po->lines()->create([
            'line_number' => 1,
            'item_id' => $item->id,
            'quantity' => 10,
            'unit' => 'pcs',
            'unit_price' => 1000,
            'line_total' => 10000,
        ]);

        $this->assertInstanceOf(Item::class, $line->item);
        $this->assertEquals($item->id, $line->item->id);
    }
}

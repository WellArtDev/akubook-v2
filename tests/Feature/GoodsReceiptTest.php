<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\GoodsReceipt;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderLine;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GoodsReceiptTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private PurchaseOrder $purchaseOrder;
    private PurchaseOrderLine $poLine;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        $this->user = User::factory()->create();
        $supplier = Supplier::factory()->create();
        $branch = Branch::factory()->create();

        $this->purchaseOrder = PurchaseOrder::factory()->create([
            'supplier_id' => $supplier->id,
            'delivery_address_id' => $branch->id,
            'status' => 'approved',
            'created_by' => $this->user->id,
        ]);

        $this->poLine = PurchaseOrderLine::create([
            'purchase_order_id' => $this->purchaseOrder->id,
            'line_number' => 1,
            'product_code' => 'RM-001',
            'product_name' => 'Raw Material',
            'quantity' => 10,
            'unit' => 'PCS',
            'unit_price' => 1000,
            'tax_amount' => 0,
            'received_quantity' => 0,
            'invoiced_quantity' => 0,
        ]);
    }

    public function test_user_can_create_goods_receipt_from_approved_po(): void
    {
        $payload = [
            'gr_date' => now()->toDateString(),
            'purchase_order_id' => $this->purchaseOrder->id,
            'reference_number' => 'SJ-001',
            'lines' => [
                [
                    'purchase_order_line_id' => $this->poLine->id,
                    'receipt_quantity' => 6,
                ],
            ],
        ];

        $response = $this->actingAs($this->user)->post(route('goods-receipts.store'), $payload);

        $response->assertRedirect();
        $this->assertDatabaseHas('goods_receipts', [
            'purchase_order_id' => $this->purchaseOrder->id,
            'status' => 'draft',
        ]);
        $this->assertDatabaseHas('goods_receipt_lines', [
            'purchase_order_line_id' => $this->poLine->id,
            'receipt_quantity' => 6,
        ]);
    }

    public function test_receiving_goods_receipt_updates_po_received_quantity_and_status(): void
    {
        $goodsReceipt = GoodsReceipt::create([
            'gr_number' => 'GR-' . now()->year . '-0001',
            'gr_date' => now()->toDateString(),
            'purchase_order_id' => $this->purchaseOrder->id,
            'supplier_id' => $this->purchaseOrder->supplier_id,
            'receive_location_id' => $this->purchaseOrder->delivery_address_id,
            'status' => 'draft',
            'created_by' => $this->user->id,
        ]);

        $line = $goodsReceipt->lines()->create([
            'purchase_order_line_id' => $this->poLine->id,
            'line_number' => 1,
            'product_code' => $this->poLine->product_code,
            'product_name' => $this->poLine->product_name,
            'po_quantity' => $this->poLine->quantity,
            'previously_received_quantity' => 0,
            'remaining_quantity' => 10,
            'receipt_quantity' => 6,
            'accepted_quantity' => 0,
            'rejected_quantity' => 0,
            'unit' => $this->poLine->unit,
        ]);

        $payload = [
            'received_at' => now()->toDateTimeString(),
            'lines' => [
                [
                    'id' => $line->id,
                    'accepted_quantity' => 5,
                    'rejected_quantity' => 1,
                    'inspection_notes' => '1 damaged',
                ],
            ],
        ];

        $response = $this->actingAs($this->user)->post(route('goods-receipts.receive', $goodsReceipt), $payload);

        $response->assertRedirect();
        $this->assertDatabaseHas('goods_receipts', ['id' => $goodsReceipt->id, 'status' => 'received']);
        $this->assertDatabaseHas('purchase_order_lines', ['id' => $this->poLine->id, 'received_quantity' => 5]);
        $this->assertDatabaseHas('purchase_orders', ['id' => $this->purchaseOrder->id, 'status' => 'in_progress']);
    }

    public function test_partial_receipts_keep_remaining_quantities_valid(): void
    {
        $first = $this->actingAs($this->user)->post(route('goods-receipts.store'), [
            'gr_date' => now()->toDateString(),
            'purchase_order_id' => $this->purchaseOrder->id,
            'lines' => [['purchase_order_line_id' => $this->poLine->id, 'receipt_quantity' => 4]],
        ]);
        $first->assertRedirect();

        $gr1 = GoodsReceipt::latest('id')->first();
        $line1 = $gr1->lines()->first();

        $this->actingAs($this->user)->post(route('goods-receipts.receive', $gr1), [
            'received_at' => now()->toDateTimeString(),
            'lines' => [[
                'id' => $line1->id,
                'accepted_quantity' => 4,
                'rejected_quantity' => 0,
            ]],
        ])->assertRedirect();

        $second = $this->actingAs($this->user)->post(route('goods-receipts.store'), [
            'gr_date' => now()->toDateString(),
            'purchase_order_id' => $this->purchaseOrder->id,
            'lines' => [['purchase_order_line_id' => $this->poLine->id, 'receipt_quantity' => 6]],
        ]);

        $second->assertRedirect();
        $this->assertDatabaseCount('goods_receipts', 2);
    }

    public function test_cannot_receive_more_than_receipt_quantity(): void
    {
        $goodsReceipt = GoodsReceipt::create([
            'gr_number' => 'GR-' . now()->year . '-0001',
            'gr_date' => now()->toDateString(),
            'purchase_order_id' => $this->purchaseOrder->id,
            'supplier_id' => $this->purchaseOrder->supplier_id,
            'receive_location_id' => $this->purchaseOrder->delivery_address_id,
            'status' => 'draft',
            'created_by' => $this->user->id,
        ]);

        $line = $goodsReceipt->lines()->create([
            'purchase_order_line_id' => $this->poLine->id,
            'line_number' => 1,
            'product_code' => $this->poLine->product_code,
            'product_name' => $this->poLine->product_name,
            'po_quantity' => $this->poLine->quantity,
            'previously_received_quantity' => 0,
            'remaining_quantity' => 10,
            'receipt_quantity' => 5,
            'accepted_quantity' => 0,
            'rejected_quantity' => 0,
            'unit' => $this->poLine->unit,
        ]);

        $this->actingAs($this->user)
            ->post(route('goods-receipts.receive', $goodsReceipt), [
                'received_at' => now()->toDateTimeString(),
                'lines' => [[
                    'id' => $line->id,
                    'accepted_quantity' => 5,
                    'rejected_quantity' => 2,
                ]],
            ])
            ->assertStatus(422);
    }
}

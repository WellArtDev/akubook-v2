<?php

namespace Tests\Feature;

use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Models\Branch;
use App\Models\Item;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PurchaseOrderTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $supplier;
    protected $branch;
    protected $item;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate');
        
        $this->user = User::factory()->create();
        $this->supplier = Supplier::factory()->create();
        $this->branch = Branch::factory()->create();
        $this->item = Item::factory()->create([
            'unit' => 'pcs',
            'purchase_price' => 1000,
        ]);
    }

    /** @test */
    public function user_can_view_purchase_orders_index()
    {
        $response = $this->actingAs($this->user)->get(route('purchase-orders.index'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page->component('PurchaseOrders/Index'));
    }

    /** @test */
    public function user_can_view_create_purchase_order_form()
    {
        $response = $this->actingAs($this->user)->get(route('purchase-orders.create'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page->component('PurchaseOrders/Create'));
    }

    /** @test */
    public function user_can_create_purchase_order()
    {
        $data = [
            'po_date' => '2026-05-15',
            'supplier_id' => $this->supplier->id,
            'delivery_address_id' => $this->branch->id,
            'payment_terms' => 'Net 30',
            'delivery_terms' => 'FOB',
            'expected_delivery_date' => '2026-05-20',
            'notes' => 'Test PO',
            'lines' => [
                [
                    'item_id' => $this->item->id,
                    'description' => 'Test item',
                    'quantity' => 10,
                    'unit' => 'pcs',
                    'unit_price' => 1000,
                    'tax_amount' => 100,
                ],
            ],
        ];

        $response = $this->actingAs($this->user)->post(route('purchase-orders.store'), $data);

        $response->assertRedirect(route('purchase-orders.index'));
        $this->assertDatabaseHas('purchase_orders', [
            'supplier_id' => $this->supplier->id,
            'status' => 'draft',
        ]);
        $this->assertDatabaseHas('purchase_order_lines', [
            'item_id' => $this->item->id,
            'quantity' => 10,
        ]);
    }

    /** @test */
    public function user_can_view_purchase_order_details()
    {
        $po = PurchaseOrder::factory()->create([
            'supplier_id' => $this->supplier->id,
            'created_by' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)->get(route('purchase-orders.show', $po));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page->component('PurchaseOrders/Show'));
    }

    /** @test */
    public function user_can_edit_draft_purchase_order()
    {
        $po = PurchaseOrder::factory()->create([
            'supplier_id' => $this->supplier->id,
            'status' => 'draft',
            'created_by' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)->get(route('purchase-orders.edit', $po));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page->component('PurchaseOrders/Edit'));
    }

    /** @test */
    public function user_can_update_draft_purchase_order()
    {
        $po = PurchaseOrder::factory()->create([
            'supplier_id' => $this->supplier->id,
            'status' => 'draft',
            'created_by' => $this->user->id,
        ]);

        $data = [
            'po_date' => '2026-05-16',
            'supplier_id' => $this->supplier->id,
            'delivery_address_id' => $this->branch->id,
            'payment_terms' => 'Net 45',
            'delivery_terms' => 'CIF',
            'expected_delivery_date' => '2026-05-25',
            'notes' => 'Updated PO',
            'lines' => [
                [
                    'item_id' => $this->item->id,
                    'description' => 'Updated item',
                    'quantity' => 20,
                    'unit' => 'pcs',
                    'unit_price' => 1500,
                    'tax_amount' => 150,
                ],
            ],
        ];

        $response = $this->actingAs($this->user)->put(route('purchase-orders.update', $po), $data);

        $response->assertRedirect(route('purchase-orders.index'));
        $this->assertDatabaseHas('purchase_orders', [
            'id' => $po->id,
            'payment_terms' => 'Net 45',
        ]);
    }

    /** @test */
    public function user_cannot_edit_approved_purchase_order()
    {
        $po = PurchaseOrder::factory()->create([
            'supplier_id' => $this->supplier->id,
            'status' => 'approved',
            'created_by' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)->get(route('purchase-orders.edit', $po));

        $response->assertRedirect();
        $response->assertSessionHasErrors();
    }

    /** @test */
    public function user_can_delete_draft_purchase_order()
    {
        $po = PurchaseOrder::factory()->create([
            'supplier_id' => $this->supplier->id,
            'status' => 'draft',
            'created_by' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)->delete(route('purchase-orders.destroy', $po));

        $response->assertRedirect(route('purchase-orders.index'));
        $this->assertDatabaseMissing('purchase_orders', ['id' => $po->id]);
    }

    /** @test */
    public function user_cannot_delete_approved_purchase_order()
    {
        $po = PurchaseOrder::factory()->create([
            'supplier_id' => $this->supplier->id,
            'status' => 'approved',
            'created_by' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)->delete(route('purchase-orders.destroy', $po));

        $response->assertRedirect();
        $response->assertSessionHasErrors();
        $this->assertDatabaseHas('purchase_orders', ['id' => $po->id]);
    }

    /** @test */
    public function user_can_submit_purchase_order_for_approval()
    {
        $po = PurchaseOrder::factory()->create([
            'supplier_id' => $this->supplier->id,
            'status' => 'draft',
            'grand_total' => 15000000,
            'approval_required' => true,
            'created_by' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)->post(route('purchase-orders.submit-approval', $po));

        $response->assertRedirect(route('purchase-orders.show', $po));
        $this->assertDatabaseHas('purchase_orders', [
            'id' => $po->id,
            'status' => 'pending_approval',
        ]);
    }

    /** @test */
    public function user_can_approve_pending_purchase_order()
    {
        $po = PurchaseOrder::factory()->create([
            'supplier_id' => $this->supplier->id,
            'status' => 'pending_approval',
            'created_by' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)->post(route('purchase-orders.approve', $po));

        $response->assertRedirect(route('purchase-orders.show', $po));
        $this->assertDatabaseHas('purchase_orders', [
            'id' => $po->id,
            'status' => 'approved',
            'approved_by' => $this->user->id,
        ]);
        $this->assertNotNull($po->fresh()->approved_at);
    }

    /** @test */
    public function purchase_order_requires_approval_when_total_exceeds_threshold()
    {
        $data = [
            'po_date' => '2026-05-15',
            'supplier_id' => $this->supplier->id,
            'lines' => [
                [
                    'item_id' => $this->item->id,
                    'quantity' => 15000,
                    'unit' => 'pcs',
                    'unit_price' => 1000,
                    'tax_amount' => 0,
                ],
            ],
        ];

        $response = $this->actingAs($this->user)->post(route('purchase-orders.store'), $data);

        $po = PurchaseOrder::latest()->first();
        $this->assertTrue($po->approval_required);
        $this->assertGreaterThan(10000000, $po->grand_total);
    }

    /** @test */
    public function purchase_order_validation_requires_supplier()
    {
        $data = [
            'po_date' => '2026-05-15',
            'lines' => [
                [
                    'item_id' => $this->item->id,
                    'quantity' => 10,
                    'unit' => 'pcs',
                    'unit_price' => 1000,
                ],
            ],
        ];

        $response = $this->actingAs($this->user)->post(route('purchase-orders.store'), $data);

        $response->assertSessionHasErrors('supplier_id');
    }

    /** @test */
    public function purchase_order_validation_requires_at_least_one_line()
    {
        $data = [
            'po_date' => '2026-05-15',
            'supplier_id' => $this->supplier->id,
            'lines' => [],
        ];

        $response = $this->actingAs($this->user)->post(route('purchase-orders.store'), $data);

        $response->assertSessionHasErrors('lines');
    }
}

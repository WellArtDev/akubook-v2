<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Customer;
use App\Models\DeliveryOrder;
use App\Models\Item;
use App\Models\SalesOrder;
use App\Models\SalesOrderLine;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeliveryOrderTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Customer $customer;
    private Branch $branch;
    private Item $item;
    private SalesOrder $salesOrder;
    private SalesOrderLine $salesOrderLine;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->customer = Customer::factory()->create();
        $this->branch = Branch::factory()->create();
        $this->item = Item::factory()->create(['is_active' => true, 'unit' => 'pcs']);

        $this->salesOrder = SalesOrder::factory()->create([
            'customer_id' => $this->customer->id,
            'sales_person_id' => $this->user->id,
            'created_by' => $this->user->id,
            'delivery_address_id' => $this->branch->id,
            'status' => 'approved',
        ]);

        $this->salesOrderLine = $this->salesOrder->lines()->create([
            'line_number' => 1,
            'item_id' => $this->item->id,
            'description' => 'Item A',
            'quantity' => 10,
            'unit' => 'pcs',
            'unit_price' => 10000,
            'discount_percent' => 0,
            'discount_amount' => 0,
            'tax_amount' => 0,
            'line_total' => 100000,
            'delivered_quantity' => 0,
            'invoiced_quantity' => 0,
        ]);
    }

    public function test_user_can_create_delivery_order_from_approved_sales_order(): void
    {
        $response = $this->actingAs($this->user)->post(route('delivery-orders.store'), $this->payload());

        $response->assertRedirect();
        $this->assertDatabaseCount('delivery_orders', 1);
        $this->assertDatabaseHas('delivery_orders', [
            'sales_order_id' => $this->salesOrder->id,
            'status' => 'draft',
        ]);
        $this->assertDatabaseCount('delivery_order_lines', 1);
    }

    public function test_delivery_order_can_be_confirmed_and_shipped(): void
    {
        $deliveryOrder = $this->createDeliveryOrder();

        $this->actingAs($this->user)
            ->post(route('delivery-orders.confirm', $deliveryOrder))
            ->assertSessionHasNoErrors();

        $deliveryOrder->refresh();
        $this->assertSame('ready_to_ship', $deliveryOrder->status);

        $this->actingAs($this->user)
            ->post(route('delivery-orders.ship', $deliveryOrder))
            ->assertSessionHasNoErrors();

        $deliveryOrder->refresh();
        $this->assertSame('in_transit', $deliveryOrder->status);
    }

    public function test_mark_delivered_updates_sales_order_line_and_status(): void
    {
        $deliveryOrder = $this->createDeliveryOrder();
        $deliveryOrder->update(['status' => 'in_transit']);

        $this->actingAs($this->user)
            ->post(route('delivery-orders.deliver', $deliveryOrder), [
                'received_by' => 'Budi',
                'received_at' => now()->toDateTimeString(),
                'signature_path' => 'signatures/do.png',
                'pod_notes' => 'ok',
            ])
            ->assertSessionHasNoErrors();

        $deliveryOrder->refresh();
        $this->salesOrderLine->refresh();
        $this->salesOrder->refresh();

        $this->assertSame('delivered', $deliveryOrder->status);
        $this->assertEquals(6.0, (float) $this->salesOrderLine->delivered_quantity);
        $this->assertSame('in_progress', $this->salesOrder->status);
    }

    public function test_delivered_delivery_order_can_be_cancelled_and_reversed(): void
    {
        $deliveryOrder = $this->createDeliveryOrder();
        $deliveryOrder->update([
            'status' => 'delivered',
            'received_by' => 'Budi',
            'received_at' => now(),
        ]);
        $this->salesOrderLine->update(['delivered_quantity' => 6]);
        $this->salesOrder->update(['status' => 'in_progress']);

        $this->actingAs($this->user)
            ->post(route('delivery-orders.cancel', $deliveryOrder), ['reason' => 'Alamat salah'])
            ->assertSessionHasNoErrors();

        $deliveryOrder->refresh();
        $this->salesOrderLine->refresh();
        $this->salesOrder->refresh();

        $this->assertSame('cancelled', $deliveryOrder->status);
        $this->assertSame('Alamat salah', $deliveryOrder->cancellation_reason);
        $this->assertEquals(0.0, (float) $this->salesOrderLine->delivered_quantity);
        $this->assertSame('approved', $this->salesOrder->status);
    }

    public function test_delivery_quantity_cannot_exceed_remaining(): void
    {
        $this->salesOrderLine->update(['delivered_quantity' => 8]);

        $this->actingAs($this->user)
            ->post(route('delivery-orders.store'), $this->payload([
                'lines' => [[
                    'sales_order_line_id' => $this->salesOrderLine->id,
                    'delivery_quantity' => 5,
                ]],
            ]))
            ->assertStatus(422);
    }

    private function createDeliveryOrder(): DeliveryOrder
    {
        $this->actingAs($this->user)->post(route('delivery-orders.store'), $this->payload());

        return DeliveryOrder::firstOrFail();
    }

    private function payload(array $overrides = []): array
    {
        return array_replace_recursive([
            'do_date' => '2026-05-17',
            'sales_order_id' => $this->salesOrder->id,
            'delivery_date' => '2026-05-18',
            'driver_name' => 'Agus',
            'vehicle_number' => 'B 1234 CD',
            'notes' => 'Kirim pagi',
            'lines' => [[
                'sales_order_line_id' => $this->salesOrderLine->id,
                'delivery_quantity' => 6,
                'notes' => null,
            ]],
        ], $overrides);
    }
}

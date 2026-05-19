<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Customer;
use App\Models\Item;
use App\Models\SalesOrder;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SalesOrderTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Customer $customer;
    private Branch $branch;
    private Item $item;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->customer = Customer::factory()->create([
            'credit_limit' => 5000000,
            'payment_terms' => 30,
        ]);
        $this->branch = Branch::factory()->create();
        $this->item = Item::factory()->create([
            'unit' => 'pcs',
            'selling_price' => 100000,
            'is_active' => true,
        ]);
    }

    public function test_user_can_create_sales_order()
    {
        $response = $this->actingAs($this->user)->post(route('sales-orders.store'), $this->payload());

        $response->assertRedirect(route('sales-orders.index'));
        $this->assertDatabaseHas('sales_orders', [
            'customer_id' => $this->customer->id,
            'status' => 'draft',
            'grand_total' => 1110000,
        ]);
        $this->assertDatabaseCount('sales_order_lines', 1);
    }

    public function test_large_sales_order_requires_approval_and_can_be_approved()
    {
        $salesOrder = SalesOrder::factory()->create([
            'customer_id' => $this->customer->id,
            'sales_person_id' => $this->user->id,
            'created_by' => $this->user->id,
            'status' => 'draft',
            'grand_total' => 15000000,
            'approval_required' => true,
        ]);

        $this->actingAs($this->user)
            ->post(route('sales-orders.submit-approval', $salesOrder))
            ->assertRedirect(route('sales-orders.show', $salesOrder));

        $salesOrder->refresh();
        $this->assertSame('pending_approval', $salesOrder->status);

        $this->actingAs($this->user)
            ->post(route('sales-orders.approve', $salesOrder))
            ->assertRedirect(route('sales-orders.show', $salesOrder));

        $salesOrder->refresh();
        $this->assertSame('approved', $salesOrder->status);
        $this->assertSame($this->user->id, $salesOrder->approved_by);
        $this->assertNotNull($salesOrder->approved_at);
    }

    public function test_pending_sales_order_can_be_rejected_to_draft_with_reason()
    {
        $salesOrder = SalesOrder::factory()->create([
            'customer_id' => $this->customer->id,
            'sales_person_id' => $this->user->id,
            'created_by' => $this->user->id,
            'status' => 'pending_approval',
            'approval_required' => true,
        ]);

        $this->actingAs($this->user)
            ->post(route('sales-orders.reject', $salesOrder), ['reason' => 'Need price update'])
            ->assertRedirect(route('sales-orders.show', $salesOrder));

        $salesOrder->refresh();
        $this->assertSame('draft', $salesOrder->status);
        $this->assertSame('Need price update', $salesOrder->rejection_reason);
        $this->assertSame($this->user->id, $salesOrder->rejected_by);
        $this->assertNotNull($salesOrder->rejected_at);
    }

    public function test_sales_order_can_be_cancelled_with_reason()
    {
        $salesOrder = SalesOrder::factory()->create([
            'customer_id' => $this->customer->id,
            'sales_person_id' => $this->user->id,
            'created_by' => $this->user->id,
            'status' => 'approved',
        ]);

        $this->actingAs($this->user)
            ->post(route('sales-orders.cancel', $salesOrder), ['reason' => 'Customer cancelled'])
            ->assertRedirect(route('sales-orders.show', $salesOrder));

        $salesOrder->refresh();
        $this->assertSame('cancelled', $salesOrder->status);
        $this->assertSame('Customer cancelled', $salesOrder->cancellation_reason);
        $this->assertSame($this->user->id, $salesOrder->cancelled_by);
        $this->assertNotNull($salesOrder->cancelled_at);
    }

    public function test_sales_order_can_be_duplicated_as_draft()
    {
        $salesOrder = SalesOrder::factory()->create([
            'customer_id' => $this->customer->id,
            'sales_person_id' => $this->user->id,
            'created_by' => $this->user->id,
            'status' => 'approved',
            'subtotal' => 1000000,
            'tax_amount' => 110000,
            'grand_total' => 1110000,
        ]);
        $salesOrder->lines()->create([
            'line_number' => 1,
            'item_id' => $this->item->id,
            'description' => 'Test item',
            'quantity' => 10,
            'unit' => 'pcs',
            'unit_price' => 100000,
            'discount_percent' => 0,
            'discount_amount' => 0,
            'tax_amount' => 110000,
            'line_total' => 1000000,
        ]);

        $this->actingAs($this->user)
            ->post(route('sales-orders.duplicate', $salesOrder))
            ->assertRedirect();

        $this->assertDatabaseCount('sales_orders', 2);
        $clone = SalesOrder::where('id', '!=', $salesOrder->id)->first();
        $this->assertSame('draft', $clone->status);
        $this->assertNotSame($salesOrder->so_number, $clone->so_number);
        $this->assertSame(1, $clone->lines()->count());
    }

    public function test_sales_order_requires_line_items()
    {
        $payload = $this->payload();
        $payload['lines'] = [];

        $this->actingAs($this->user)
            ->post(route('sales-orders.store'), $payload)
            ->assertSessionHasErrors('lines');
    }

    private function payload(array $overrides = []): array
    {
        return array_replace_recursive([
            'so_date' => '2026-05-17',
            'customer_id' => $this->customer->id,
            'customer_po_number' => 'PO-001',
            'payment_terms' => 'Net 30',
            'delivery_terms' => 'FOB',
            'delivery_address_id' => $this->branch->id,
            'requested_delivery_date' => '2026-05-24',
            'notes' => 'Test order',
            'lines' => [
                [
                    'item_id' => $this->item->id,
                    'description' => 'Test item',
                    'quantity' => 10,
                    'unit' => 'pcs',
                    'unit_price' => 100000,
                    'discount_percent' => 0,
                    'discount_amount' => 0,
                    'tax_amount' => 110000,
                ],
            ],
        ], $overrides);
    }
}

<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\DeliveryOrder;
use App\Models\DeliveryOrderLine;
use App\Models\FakturPajak;
use App\Models\Item;
use App\Models\SalesInvoice;
use App\Models\SalesOrder;
use App\Models\SalesOrderLine;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FakturPajakTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Customer $customer;
    private SalesInvoice $invoice;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
        $this->customer = Customer::factory()->create();

        $salesOrder = SalesOrder::factory()->create([
            'customer_id' => $this->customer->id,
            'status' => 'approved',
        ]);

        $item = Item::factory()->create([
            'unit' => 'pcs',
            'selling_price' => 1000,
        ]);

        $salesOrderLine = SalesOrderLine::factory()->create([
            'sales_order_id' => $salesOrder->id,
            'item_id' => $item->id,
            'quantity' => 10,
            'unit_price' => 1000,
            'line_total' => 10000,
            'tax_amount' => 1100,
            'delivered_quantity' => 10,
        ]);

        $deliveryOrder = DeliveryOrder::factory()->create([
            'sales_order_id' => $salesOrder->id,
            'customer_id' => $this->customer->id,
            'status' => 'delivered',
            'created_by' => $this->user->id,
        ]);

        DeliveryOrderLine::factory()->create([
            'delivery_order_id' => $deliveryOrder->id,
            'sales_order_line_id' => $salesOrderLine->id,
            'item_id' => $item->id,
            'delivery_quantity' => 10,
            'remaining_quantity' => 0,
            'unit' => 'pcs',
        ]);

        $this->invoice = SalesInvoice::factory()->create([
            'sales_order_id' => $salesOrder->id,
            'customer_id' => $this->customer->id,
            'subtotal' => 100000,
            'discount_amount' => 0,
            'tax_amount' => 11000,
            'grand_total' => 111000,
            'created_by' => $this->user->id,
        ]);
    }

    public function test_index_page_can_be_opened(): void
    {
        $this->get(route('faktur-pajaks.index'))->assertOk();
    }

    public function test_user_can_create_faktur_from_invoice(): void
    {
        $response = $this->post(route('faktur-pajaks.store'), [
            'faktur_date' => now()->toDateString(),
            'sales_invoice_id' => $this->invoice->id,
            'notes' => 'Test faktur',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('faktur_pajaks', [
            'sales_invoice_id' => $this->invoice->id,
            'customer_id' => $this->customer->id,
            'dpp' => 100000,
            'ppn_amount' => 11000,
            'grand_total' => 111000,
            'status' => 'draft',
        ]);
    }

    public function test_faktur_can_be_issued_and_cancelled(): void
    {
        $faktur = FakturPajak::query()->create([
            'faktur_number' => FakturPajak::generateNumber(),
            'faktur_date' => now()->toDateString(),
            'sales_invoice_id' => $this->invoice->id,
            'customer_id' => $this->customer->id,
            'dpp' => 100000,
            'ppn_amount' => 11000,
            'grand_total' => 111000,
            'status' => 'draft',
            'created_by' => $this->user->id,
        ]);

        $this->post(route('faktur-pajaks.issue', $faktur))->assertRedirect();
        $this->assertDatabaseHas('faktur_pajaks', [
            'id' => $faktur->id,
            'status' => 'issued',
            'issued_by' => $this->user->id,
        ]);

        $this->post(route('faktur-pajaks.cancel', $faktur))->assertRedirect();
        $this->assertDatabaseHas('faktur_pajaks', [
            'id' => $faktur->id,
            'status' => 'cancelled',
            'cancelled_by' => $this->user->id,
        ]);
    }
}

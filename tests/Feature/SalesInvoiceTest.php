<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\DeliveryOrder;
use App\Models\DeliveryOrderLine;
use App\Models\FiscalPeriod;
use App\Models\Item;
use App\Models\SalesInvoice;
use App\Models\SalesOrder;
use App\Models\SalesOrderLine;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SalesInvoiceTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $customer;
    protected $salesOrder;
    protected $deliveryOrder;
    protected $deliveryOrderLine;


    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();

        $this->user = User::factory()->create();
        $this->customer = Customer::factory()->create();
        $this->salesOrder = SalesOrder::factory()->create([
            'customer_id' => $this->customer->id,
            'status' => 'approved',
        ]);

        $item = Item::factory()->create([
            'name' => 'Test Product',
            'unit' => 'pcs',
            'selling_price' => 1000,
        ]);

        $salesOrderLine = SalesOrderLine::factory()->create([
            'sales_order_id' => $this->salesOrder->id,
            'item_id' => $item->id,
            'description' => 'Test Product',
            'quantity' => 10,
            'unit' => 'pcs',
            'unit_price' => 1000,
            'discount_amount' => 0,
            'tax_amount' => 1100,
            'line_total' => 11100,
            'delivered_quantity' => 10,
        ]);

        $this->deliveryOrder = DeliveryOrder::factory()->create([
            'sales_order_id' => $this->salesOrder->id,
            'customer_id' => $this->customer->id,
            'status' => 'delivered',
            'received_by' => 'Test Receiver',
            'received_at' => now(),
            'created_by' => $this->user->id,
        ]);

        $this->deliveryOrderLine = DeliveryOrderLine::factory()->create([
            'delivery_order_id' => $this->deliveryOrder->id,
            'sales_order_line_id' => $salesOrderLine->id,
            'item_id' => $item->id,
            'description' => 'Test Product',
            'so_quantity' => 10,
            'previously_delivered_quantity' => 0,
            'remaining_quantity' => 10,
            'delivery_quantity' => 10,
            'unit' => 'pcs',
        ]);
    }


    /** @test */
    public function test_user_can_view_invoices_index()
    {
        $response = $this->actingAs($this->user)->get(route('sales-invoices.index'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page->component('SalesInvoices/Index'));
    }

    /** @test */
    public function test_user_can_view_create_invoice_form()
    {
        $response = $this->actingAs($this->user)->get(route('sales-invoices.create'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page->component('SalesInvoices/Create'));
    }

    /** @test */
    public function test_user_can_create_invoice()
    {
        $data = [
            'invoice_date' => '2026-05-15',
            'due_date' => '2026-06-15',
            'delivery_order_id' => $this->deliveryOrder->id,
            'sales_order_id' => $this->salesOrder->id,
            'customer_id' => $this->customer->id,
            'billing_address' => 'Test Address',
            'payment_terms' => 'Net 30',
            'generate_tax_invoice' => true,
            'lines' => [
                [
                    'delivery_order_line_id' => $this->deliveryOrderLine->id,
                    'quantity' => 10,
                ],
            ],
        ];

        $response = $this->actingAs($this->user)->post(route('sales-invoices.store'), $data);

        $response->assertRedirect();
        $this->assertDatabaseHas('sales_invoices', [
            'customer_id' => $this->customer->id,
            'status' => 'draft',
        ]);
    }

    /** @test */
    public function test_invoice_number_is_generated_automatically()
    {
        $invoice = SalesInvoice::factory()->create();
        
        $this->assertNotNull($invoice->invoice_number);
        $this->assertStringStartsWith('INV-', $invoice->invoice_number);
    }

    /** @test */
    public function test_tax_invoice_number_is_generated_when_requested()
    {
        $data = [
            'invoice_date' => '2026-05-15',
            'due_date' => '2026-06-15',
            'delivery_order_id' => $this->deliveryOrder->id,
            'sales_order_id' => $this->salesOrder->id,
            'customer_id' => $this->customer->id,
            'generate_tax_invoice' => true,
            'lines' => [
                [
                    'delivery_order_line_id' => $this->deliveryOrderLine->id,
                    'quantity' => 10,
                ],
            ],
        ];

        $this->actingAs($this->user)->post(route('sales-invoices.store'), $data);

        $invoice = SalesInvoice::first();
        $this->assertNotNull($invoice->tax_invoice_number);
        $this->assertStringStartsWith('FP-', $invoice->tax_invoice_number);
    }

    /** @test */
    public function test_invoice_calculates_totals_correctly()
    {
        $secondItem = Item::factory()->create([
            'name' => 'Product 2',
            'unit' => 'pcs',
            'selling_price' => 2000,
        ]);

        $secondSalesOrderLine = SalesOrderLine::factory()->create([
            'sales_order_id' => $this->salesOrder->id,
            'item_id' => $secondItem->id,
            'description' => 'Product 2',
            'quantity' => 5,
            'unit' => 'pcs',
            'unit_price' => 2000,
            'discount_amount' => 0,
            'tax_amount' => 1100,
            'line_total' => 11100,
            'delivered_quantity' => 5,
        ]);

        $secondDeliveryLine = DeliveryOrderLine::factory()->create([
            'delivery_order_id' => $this->deliveryOrder->id,
            'sales_order_line_id' => $secondSalesOrderLine->id,
            'item_id' => $secondItem->id,
            'description' => 'Product 2',
            'so_quantity' => 5,
            'previously_delivered_quantity' => 0,
            'remaining_quantity' => 5,
            'delivery_quantity' => 5,
            'unit' => 'pcs',
        ]);

        $data = [
            'invoice_date' => '2026-05-15',
            'due_date' => '2026-06-15',
            'delivery_order_id' => $this->deliveryOrder->id,
            'sales_order_id' => $this->salesOrder->id,
            'customer_id' => $this->customer->id,
            'lines' => [
                [
                    'delivery_order_line_id' => $this->deliveryOrderLine->id,
                    'quantity' => 10,
                ],
                [
                    'delivery_order_line_id' => $secondDeliveryLine->id,
                    'quantity' => 5,
                ],
            ],
        ];

        $this->actingAs($this->user)->post(route('sales-invoices.store'), $data);

        $invoice = SalesInvoice::first();
        
        $this->assertEquals(20000, $invoice->subtotal);
        $this->assertEquals(2200, $invoice->tax_amount);
        $this->assertEquals(22200, $invoice->grand_total);
        $this->assertEquals(22200, $invoice->amount_due);
    }

    /** @test */
    public function test_user_can_send_draft_invoice()
    {
        $invoice = SalesInvoice::factory()->create([
            'status' => 'draft',
            'created_by' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)->post(route('sales-invoices.send', $invoice));

        $response->assertRedirect();
        $invoice->refresh();
        $this->assertEquals('sent', $invoice->status);
        $this->assertNotNull($invoice->sent_at);
    }

    /** @test */
    public function test_cannot_send_non_draft_invoice()
    {
        $invoice = SalesInvoice::factory()->create([
            'status' => 'sent',
            'created_by' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)->post(route('sales-invoices.send', $invoice));

        $response->assertSessionHasErrors();
    }

    /** @test */
    public function test_user_can_record_payment()
    {
        $invoice = SalesInvoice::factory()->create([
            'status' => 'sent',
            'grand_total' => 10000,
            'amount_paid' => 0,
            'amount_due' => 10000,
        ]);

        $response = $this->actingAs($this->user)->post(
            route('sales-invoices.record-payment', $invoice),
            [
                'amount' => 5000,
                'payment_method' => 'bank_transfer',
            ]
        );

        $response->assertRedirect();
        $invoice->refresh();
        $this->assertEquals(5000, $invoice->amount_paid);
        $this->assertEquals(5000, $invoice->amount_due);
        $this->assertEquals('partially_paid', $invoice->status);
    }

    /** @test */
    public function test_invoice_status_updates_to_paid_when_fully_paid()
    {
        $invoice = SalesInvoice::factory()->create([
            'status' => 'sent',
            'grand_total' => 10000,
            'amount_paid' => 0,
            'amount_due' => 10000,
        ]);

        $this->actingAs($this->user)->post(
            route('sales-invoices.record-payment', $invoice),
            [
                'amount' => 10000,
                'payment_method' => 'bank_transfer',
            ]
        );

        $invoice->refresh();
        $this->assertEquals(10000, $invoice->amount_paid);
        $this->assertEquals(0, $invoice->amount_due);
        $this->assertEquals('paid', $invoice->status);
    }

    /** @test */
    public function test_cannot_record_payment_exceeding_amount_due()
    {
        $invoice = SalesInvoice::factory()->create([
            'status' => 'sent',
            'grand_total' => 10000,
            'amount_paid' => 0,
            'amount_due' => 10000,
        ]);

        $response = $this->actingAs($this->user)->post(
            route('sales-invoices.record-payment', $invoice),
            [
                'amount' => 15000,
                'payment_method' => 'bank_transfer',
            ]
        );

        $response->assertSessionHasErrors();
    }

    /** @test */
    public function test_user_can_cancel_invoice_without_payments()
    {
        $invoice = SalesInvoice::factory()->create([
            'status' => 'sent',
            'amount_paid' => 0,
        ]);

        $response = $this->actingAs($this->user)->post(
            route('sales-invoices.cancel', $invoice),
            ['cancellation_reason' => 'Customer request']
        );

        $response->assertRedirect();
        $invoice->refresh();
        $this->assertEquals('cancelled', $invoice->status);
        $this->assertNotNull($invoice->cancelled_at);
        $this->assertEquals('Customer request', $invoice->cancellation_reason);
    }

    /** @test */
    public function test_cannot_cancel_invoice_with_payments()
    {
        $invoice = SalesInvoice::factory()->create([
            'status' => 'partially_paid',
            'amount_paid' => 5000,
        ]);

        $response = $this->actingAs($this->user)->post(
            route('sales-invoices.cancel', $invoice),
            ['cancellation_reason' => 'Test']
        );

        $response->assertSessionHasErrors();
        $invoice->refresh();
        $this->assertNotEquals('cancelled', $invoice->status);
    }

    /** @test */
    public function test_user_can_delete_draft_invoice()
    {
        $invoice = SalesInvoice::factory()->create([
            'status' => 'draft',
        ]);

        $response = $this->actingAs($this->user)->delete(route('sales-invoices.destroy', $invoice));

        $response->assertRedirect(route('sales-invoices.index'));
        $this->assertSoftDeleted('sales_invoices', ['id' => $invoice->id]);
    }

    /** @test */
    public function test_posting_draft_invoice_generates_posted_sales_journal()
    {
        FiscalPeriod::create([
            'name' => 'FY 2026',
            'start_date' => '2026-01-01',
            'end_date' => '2026-12-31',
            'status' => 'open',
            'is_current' => true,
        ]);

        $arAccount = Account::factory()->create(['code' => '1-1300', 'name' => 'Piutang Usaha', 'type' => 'asset', 'category' => 'current_asset', 'balance' => 0]);
        $salesAccount = Account::factory()->create(['code' => '4-1000', 'name' => 'Pendapatan Penjualan', 'type' => 'revenue', 'category' => 'operating_revenue', 'balance' => 0]);
        $taxAccount = Account::factory()->create(['code' => '2-1200', 'name' => 'Hutang PPN', 'type' => 'liability', 'category' => 'current_liability', 'balance' => 0]);
        Account::factory()->create(['code' => '1-1200', 'name' => 'Legacy AR', 'type' => 'asset', 'category' => 'current_asset', 'balance' => 0]);

        $invoice = SalesInvoice::factory()->create([
            'invoice_date' => '2026-05-15',
            'customer_id' => $this->customer->id,
            'status' => 'draft',
            'subtotal' => 10000,
            'tax_amount' => 1100,
            'grand_total' => 11100,
            'created_by' => $this->user->id,
            'journal_entry_id' => null,
        ]);

        $response = $this->actingAs($this->user)->post(route('sales-invoices.post', $invoice));

        $response->assertRedirect();
        $invoice->refresh();

        $this->assertEquals('sent', $invoice->status);
        $this->assertNotNull($invoice->journal_entry_id);
        $this->assertDatabaseHas('journal_entries', [
            'id' => $invoice->journal_entry_id,
            'journal_date' => '2026-05-15 00:00:00',
            'reference_type' => 'sales_invoice',
            'reference_id' => $invoice->id,
            'type' => 'auto_sales',
            'status' => 'posted',
            'total_debit' => 11100,
            'total_credit' => 11100,
        ]);
        $this->assertDatabaseHas('journal_entry_lines', ['journal_entry_id' => $invoice->journal_entry_id, 'account_id' => $arAccount->id, 'debit' => 11100, 'credit' => 0]);
        $this->assertDatabaseHas('journal_entry_lines', ['journal_entry_id' => $invoice->journal_entry_id, 'account_id' => $salesAccount->id, 'debit' => 0, 'credit' => 10000]);
        $this->assertDatabaseHas('journal_entry_lines', ['journal_entry_id' => $invoice->journal_entry_id, 'account_id' => $taxAccount->id, 'debit' => 0, 'credit' => 1100]);
    }

    /** @test */
    public function test_cannot_delete_non_draft_invoice()
    {
        $invoice = SalesInvoice::factory()->create([
            'status' => 'sent',
        ]);

        $response = $this->actingAs($this->user)->delete(route('sales-invoices.destroy', $invoice));

        $response->assertSessionHasErrors();
        $this->assertDatabaseHas('sales_invoices', ['id' => $invoice->id]);
    }
}


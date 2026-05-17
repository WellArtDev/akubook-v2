<?php

namespace Tests\Feature;

use App\Models\SalesInvoice;
use App\Models\SalesOrder;
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

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate');
        
        $this->user = User::factory()->create();
        $this->customer = Customer::factory()->create();
        $this->salesOrder = SalesOrder::factory()->create([
            'customer_id' => $this->customer->id,
            'status' => 'approved',
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
            'sales_order_id' => $this->salesOrder->id,
            'customer_id' => $this->customer->id,
            'billing_address' => 'Test Address',
            'payment_terms' => 'Net 30',
            'generate_tax_invoice' => true,
            'lines' => [
                [
                    'product_name' => 'Test Product',
                    'quantity' => 10,
                    'unit' => 'pcs',
                    'unit_price' => 1000,
                    'discount_amount' => 0,
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
            'sales_order_id' => $this->salesOrder->id,
            'customer_id' => $this->customer->id,
            'generate_tax_invoice' => true,
            'lines' => [
                [
                    'product_name' => 'Test Product',
                    'quantity' => 10,
                    'unit' => 'pcs',
                    'unit_price' => 1000,
                    'discount_amount' => 0,
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
        $data = [
            'invoice_date' => '2026-05-15',
            'due_date' => '2026-06-15',
            'sales_order_id' => $this->salesOrder->id,
            'customer_id' => $this->customer->id,
            'lines' => [
                [
                    'product_name' => 'Product 1',
                    'quantity' => 10,
                    'unit' => 'pcs',
                    'unit_price' => 1000,
                    'discount_amount' => 100,
                ],
                [
                    'product_name' => 'Product 2',
                    'quantity' => 5,
                    'unit' => 'pcs',
                    'unit_price' => 2000,
                    'discount_amount' => 0,
                ],
            ],
        ];

        $this->actingAs($this->user)->post(route('sales-invoices.store'), $data);

        $invoice = SalesInvoice::first();
        
        // Subtotal: (10 * 1000 - 100) + (5 * 2000) = 9900 + 10000 = 19900
        $this->assertEquals(19900, $invoice->subtotal);
        
        // Tax: 19900 * 0.11 = 2189
        $this->assertEquals(2189, $invoice->tax_amount);
        
        // Grand total: 19900 + 2189 = 22089
        $this->assertEquals(22089, $invoice->grand_total);
        
        // Amount due should equal grand total initially
        $this->assertEquals(22089, $invoice->amount_due);
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


<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\Customer;
use App\Models\FiscalPeriod;
use App\Models\Item;
use App\Models\SalesInvoice;
use App\Models\SalesInvoiceLine;
use App\Models\SalesOrder;
use App\Models\SalesReturn;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SalesReturnTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Customer $customer;
    protected SalesInvoice $invoice;
    protected SalesInvoiceLine $invoiceLine;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();

        $this->user = User::factory()->create();
        $this->customer = Customer::factory()->create();
        $salesOrder = SalesOrder::factory()->create(['customer_id' => $this->customer->id]);
        $item = Item::factory()->create(['name' => 'Item Return']);

        $this->invoice = SalesInvoice::factory()->create([
            'sales_order_id' => $salesOrder->id,
            'customer_id' => $this->customer->id,
            'status' => 'sent',
            'created_by' => $this->user->id,
        ]);

        $this->invoiceLine = SalesInvoiceLine::create([
            'sales_invoice_id' => $this->invoice->id,
            'sales_order_line_id' => null,
            'delivery_order_line_id' => null,
            'line_number' => 1,
            'product_id' => $item->id,
            'product_name' => $item->name,
            'description' => $item->name,
            'quantity' => 5,
            'unit' => 'pcs',
            'unit_price' => 1000,
            'discount_amount' => 0,
            'tax_amount' => 550,
            'line_total' => 5550,
        ]);
    }

    public function test_user_can_create_sales_return(): void
    {
        $payload = [
            'return_date' => now()->toDateString(),
            'sales_invoice_id' => $this->invoice->id,
            'return_reason' => 'Defective',
            'lines' => [
                [
                    'sales_invoice_line_id' => $this->invoiceLine->id,
                    'return_quantity' => 2,
                    'inspection_notes' => 'Box rusak',
                ],
            ],
        ];

        $response = $this->actingAs($this->user)->post(route('sales-returns.store'), $payload);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect();
        $this->assertDatabaseHas('sales_returns', [
            'sales_invoice_id' => $this->invoice->id,
            'status' => 'pending',
        ]);
        $salesReturn = SalesReturn::first();
        $this->assertEquals(2000, (float) $salesReturn->subtotal);
        $this->assertEquals(220, (float) $salesReturn->tax_amount);
        $this->assertEquals(2220, (float) $salesReturn->total_amount);
    }

    public function test_cannot_create_return_exceeding_invoice_quantity(): void
    {
        $payload = [
            'return_date' => now()->toDateString(),
            'sales_invoice_id' => $this->invoice->id,
            'return_reason' => 'Defective',
            'lines' => [
                [
                    'sales_invoice_line_id' => $this->invoiceLine->id,
                    'return_quantity' => 7,
                ],
            ],
        ];

        $response = $this->actingAs($this->user)->post(route('sales-returns.store'), $payload);

        $response->assertSessionHasErrors('error');
    }

    public function test_return_can_be_approved_received_completed(): void
    {
        FiscalPeriod::create([
            'name' => '2026-05',
            'start_date' => '2026-05-01',
            'end_date' => '2026-05-31',
            'status' => 'open',
            'is_current' => true,
            'created_by' => $this->user->id,
        ]);

        Account::factory()->create(['code' => '4-9000']);
        Account::factory()->create(['code' => '1-1300']);

        $salesReturn = SalesReturn::factory()->create([
            'sales_invoice_id' => $this->invoice->id,
            'customer_id' => $this->customer->id,
            'created_by' => $this->user->id,
            'status' => 'pending',
        ]);

        $line = $salesReturn->lines()->create([
            'sales_invoice_line_id' => $this->invoiceLine->id,
            'product_id' => $this->invoiceLine->product_id,
            'product_name' => $this->invoiceLine->product_name,
            'return_quantity' => 2,
            'accepted_quantity' => 0,
            'rejected_quantity' => 0,
            'unit_price' => 1000,
            'tax_amount' => 220,
            'line_total' => 2000,
        ]);

        $salesReturn->subtotal = 2000;
        $salesReturn->tax_amount = 220;
        $salesReturn->total_amount = 2220;
        $salesReturn->save();

        $this->actingAs($this->user)->post(route('sales-returns.approve', $salesReturn));
        $salesReturn->refresh();
        $this->assertEquals('approved', $salesReturn->status);

        $this->actingAs($this->user)->post(route('sales-returns.receive', $salesReturn), [
            'lines' => [
                [
                    'id' => $line->id,
                    'accepted_quantity' => 2,
                    'rejected_quantity' => 0,
                ],
            ],
        ]);
        $salesReturn->refresh();
        $this->assertEquals('received', $salesReturn->status);

        $this->actingAs($this->user)->post(route('sales-returns.complete', $salesReturn));
        $salesReturn->refresh();
        $this->assertEquals('completed', $salesReturn->status);
        $this->assertNotNull($salesReturn->journal_entry_id);
    }

    public function test_return_can_be_rejected(): void
    {
        $salesReturn = SalesReturn::factory()->create([
            'sales_invoice_id' => $this->invoice->id,
            'customer_id' => $this->customer->id,
            'created_by' => $this->user->id,
            'status' => 'pending',
        ]);

        $this->actingAs($this->user)->post(route('sales-returns.reject', $salesReturn), [
            'reason' => 'Tidak sesuai kebijakan',
        ]);

        $salesReturn->refresh();
        $this->assertEquals('rejected', $salesReturn->status);
        $this->assertEquals('Tidak sesuai kebijakan', $salesReturn->rejection_reason);
    }
}

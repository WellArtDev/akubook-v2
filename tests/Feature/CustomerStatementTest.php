<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\CustomerPayment;
use App\Models\SalesInvoice;
use App\Models\SalesOrder;
use App\Models\SalesReturn;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerStatementTest extends TestCase
{
    use RefreshDatabase;

    public function test_page_loads_and_shows_filters(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('customer-statements.index'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('CustomerStatements/Index')
            ->has('customers')
            ->where('statement', null)
        );
    }

    public function test_statement_calculates_closing_balance(): void
    {
        $user = User::factory()->create();
        $customer = Customer::factory()->create();

        $salesOrder = SalesOrder::create([
            'so_number' => 'SO-' . now()->year . '-0001',
            'so_date' => now()->toDateString(),
            'customer_id' => $customer->id,
            'sales_person_id' => $user->id,
            'status' => 'approved',
            'subtotal' => 1000,
            'discount_amount' => 0,
            'tax_amount' => 0,
            'grand_total' => 1000,
            'total_amount' => 1000,
            'created_by' => $user->id,
        ]);

        $invoice = SalesInvoice::create([
            'invoice_number' => 'INV-' . now()->year . '-0001',
            'invoice_date' => now()->subDays(5)->toDateString(),
            'due_date' => now()->addDays(10)->toDateString(),
            'sales_order_id' => $salesOrder->id,
            'customer_id' => $customer->id,
            'status' => 'sent',
            'subtotal' => 1000,
            'discount_amount' => 0,
            'tax_amount' => 0,
            'grand_total' => 1000,
            'amount_paid' => 0,
            'amount_due' => 1000,
            'created_by' => $user->id,
        ]);

        CustomerPayment::create([
            'payment_number' => 'PAY-' . now()->year . '-0001',
            'payment_date' => now()->subDays(3)->toDateString(),
            'customer_id' => $customer->id,
            'payment_method' => 'cash',
            'total_amount' => 300,
            'allocated_amount' => 0,
            'unapplied_amount' => 300,
            'status' => 'posted',
            'created_by' => $user->id,
        ]);

        SalesReturn::create([
            'rma_number' => 'RMA-' . now()->year . '-0001',
            'return_date' => now()->subDays(2)->toDateString(),
            'sales_invoice_id' => $invoice->id,
            'customer_id' => $customer->id,
            'return_reason' => 'Test return',
            'status' => 'completed',
            'subtotal' => 100,
            'tax_amount' => 0,
            'total_amount' => 100,
            'created_by' => $user->id,
        ]);

        $response = $this->actingAs($user)->get(route('customer-statements.index', [
            'customer_id' => $customer->id,
            'date_from' => now()->subDays(10)->toDateString(),
            'date_to' => now()->toDateString(),
        ]));

        $response->assertInertia(fn ($page) => $page
            ->component('CustomerStatements/Index')
            ->where('statement.closing_balance', 600)
            ->has('statement.transactions', 3)
        );
    }

    public function test_pdf_endpoint_returns_json_payload(): void
    {
        $user = User::factory()->create();
        $customer = Customer::factory()->create();

        $response = $this->actingAs($user)->get(route('customer-statements.pdf', [
            'customer_id' => $customer->id,
            'date_from' => now()->startOfMonth()->toDateString(),
            'date_to' => now()->toDateString(),
        ]));

        $response->assertOk();
        $response->assertJsonPath('format', 'pdf-ready-json');
    }
}

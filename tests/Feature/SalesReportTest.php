<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Item;
use App\Models\SalesInvoice;
use App\Models\SalesOrder;
use App\Models\SalesOrderLine;
use App\Models\SalesQuotation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SalesReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_sales_report_page_loads_with_expected_shape(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('sales-reports.index'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('SalesReports/Index')
                ->has('summary')
                ->has('by_customer')
                ->has('by_product')
                ->has('by_salesperson')
                ->has('pipeline')
                ->has('aging')
            );
    }

    public function test_sales_report_aggregates_core_numbers(): void
    {
        $user = User::factory()->create();
        $salesPerson = User::factory()->create();
        $customer = Customer::factory()->create();
        $item = Item::factory()->create();

        $order = SalesOrder::create([
            'so_number' => 'SO-' . now()->year . '-0001',
            'so_date' => now()->toDateString(),
            'customer_id' => $customer->id,
            'sales_person_id' => $salesPerson->id,
            'status' => 'approved',
            'subtotal' => 1000000,
            'discount_amount' => 0,
            'tax_amount' => 0,
            'grand_total' => 1000000,
            'total_amount' => 1000000,
            'created_by' => $user->id,
        ]);

        SalesOrderLine::create([
            'sales_order_id' => $order->id,
            'line_number' => 1,
            'item_id' => $item->id,
            'description' => 'Produk A',
            'quantity' => 2,
            'unit' => 'pcs',
            'unit_price' => 500000,
            'discount_percent' => 0,
            'discount_amount' => 0,
            'tax_amount' => 0,
            'line_total' => 1000000,
            'delivered_quantity' => 0,
            'invoiced_quantity' => 0,
        ]);

        SalesQuotation::create([
            'quotation_number' => 'SQ-' . now()->year . '-0001',
            'quotation_date' => now()->toDateString(),
            'valid_until' => now()->addDays(14)->toDateString(),
            'customer_id' => $customer->id,
            'sales_person_id' => $salesPerson->id,
            'status' => 'approved',
            'grand_total' => 1000000,
            'created_by' => $user->id,
            'converted_to_sales_order_id' => $order->id,
        ]);

        SalesInvoice::create([
            'invoice_number' => 'INV-' . now()->year . '-0001',
            'invoice_date' => now()->subDays(40)->toDateString(),
            'due_date' => now()->subDays(10)->toDateString(),
            'sales_order_id' => $order->id,
            'customer_id' => $customer->id,
            'status' => 'overdue',
            'subtotal' => 1000000,
            'discount_amount' => 0,
            'tax_amount' => 0,
            'grand_total' => 1000000,
            'amount_paid' => 0,
            'amount_due' => 1000000,
            'created_by' => $user->id,
        ]);

        $this->actingAs($user)
            ->get(route('sales-reports.index', [
                'date_from' => now()->subMonths(2)->toDateString(),
                'date_to' => now()->addDay()->toDateString(),
            ]))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->where('summary.total_sales', 1000000)
                ->where('summary.transaction_count', 1)
                ->where('pipeline.quotations_converted', 1)
                ->where('pipeline.invoices_overdue', 1)
                ->where('aging.over_90', 0)
            );
    }

    public function test_sales_report_export_returns_rows(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('sales-reports.export'))
            ->assertOk()
            ->assertJsonStructure(['period' => ['date_from', 'date_to'], 'rows']);
    }
}

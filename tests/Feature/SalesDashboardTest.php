<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\CustomerPayment;
use App\Models\Item;
use App\Models\SalesInvoice;
use App\Models\SalesOrder;
use App\Models\SalesOrderLine;
use App\Models\SalesQuotation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SalesDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_sales_dashboard_page_loads_with_expected_shape(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('sales-dashboard.index'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('SalesDashboard/Index')
            ->has('kpis')
            ->has('charts.sales_trend')
            ->has('charts.top_customers')
            ->has('charts.top_products')
            ->has('charts.top_salespeople')
            ->has('recent_activity')
            ->has('alerts')
        );
    }

    public function test_sales_dashboard_kpis_charts_and_activity_include_sales_data(): void
    {
        $user = User::factory()->create();
        $salesperson = User::factory()->create(['name' => 'Sales A']);
        $customer = Customer::factory()->create(['name' => 'Customer A']);
        $item = Item::factory()->create(['name' => 'Produk A']);

        $order = SalesOrder::create([
            'so_number' => 'SO-' . now()->year . '-0001',
            'so_date' => now()->toDateString(),
            'customer_id' => $customer->id,
            'sales_person_id' => $salesperson->id,
            'status' => 'pending_approval',
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
            'discount_amount' => 0,
            'tax_amount' => 0,
            'line_total' => 1000000,
        ]);

        SalesQuotation::create([
            'quotation_number' => 'QUO-' . now()->year . '-0001',
            'quotation_date' => now()->toDateString(),
            'valid_until' => now()->addDays(14)->toDateString(),
            'customer_id' => $customer->id,
            'sales_person_id' => $salesperson->id,
            'status' => 'sent',
            'subtotal' => 1000000,
            'discount_amount' => 0,
            'tax_amount' => 0,
            'grand_total' => 1000000,
            'created_by' => $user->id,
        ]);

        SalesInvoice::create([
            'invoice_number' => 'INV-' . now()->year . '-0001',
            'invoice_date' => now()->toDateString(),
            'due_date' => now()->subDays(5)->toDateString(),
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

        CustomerPayment::create([
            'payment_number' => 'PAY-' . now()->year . '-0001',
            'payment_date' => now()->toDateString(),
            'customer_id' => $customer->id,
            'payment_method' => 'cash',
            'total_amount' => 250000,
            'allocated_amount' => 250000,
            'unapplied_amount' => 0,
            'status' => 'posted',
            'created_by' => $user->id,
        ]);

        $response = $this->actingAs($user)->get(route('sales-dashboard.index', [
            'date_from' => now()->startOfMonth()->toDateString(),
            'date_to' => now()->addDay()->toDateString(),
        ]));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->where('kpis.pending_quotations', 1)
            ->where('kpis.pending_approvals', 1)
            ->where('kpis.overdue_invoices', 1)
            ->has('charts.top_customers', 1)
            ->has('charts.top_products', 1)
            ->has('charts.top_salespeople', 1)
            ->has('recent_activity.quotations', 1)
            ->has('recent_activity.orders', 1)
            ->has('recent_activity.invoices', 1)
            ->has('recent_activity.payments', 1)
        );
    }
}

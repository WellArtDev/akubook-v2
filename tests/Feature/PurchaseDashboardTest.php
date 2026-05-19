<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\GoodsReceipt;
use App\Models\PurchaseInvoice;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderLine;
use App\Models\PurchaseRequest;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PurchaseDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_page_loads_with_expected_shape(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('purchase-dashboard.index'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('PurchaseDashboard/Index')
            ->has('kpis')
            ->has('charts.purchase_trend')
            ->has('charts.top_suppliers')
            ->has('charts.top_products')
        );
    }

    public function test_dashboard_kpis_and_charts_include_purchase_data(): void
    {
        $user = User::factory()->create();
        $supplier = Supplier::factory()->create(['name' => 'Supplier A']);

        $department = Department::factory()->create();

        PurchaseRequest::create([
            'pr_number' => 'PR-' . now()->year . '-0001',
            'pr_date' => now()->toDateString(),
            'department_id' => $department->id,
            'required_date' => now()->addDays(7)->toDateString(),
            'status' => 'pending_approval',
            'total_estimated_amount' => 1000000,
            'created_by' => $user->id,
        ]);

        $po = PurchaseOrder::factory()->create([
            'supplier_id' => $supplier->id,
            'status' => 'pending_approval',
            'po_date' => now()->toDateString(),
            'grand_total' => 2500000,
            'created_by' => $user->id,
        ]);

        PurchaseOrderLine::create([
            'purchase_order_id' => $po->id,
            'line_number' => 1,
            'product_code' => 'ITM-01',
            'product_name' => 'Test Product',
            'quantity' => 5,
            'unit' => 'pcs',
            'unit_price' => 500000,
            'line_total' => 2500000,
            'tax_amount' => 0,
        ]);

        $goodsReceipt = GoodsReceipt::create([
            'gr_number' => 'GR-' . now()->year . '-0001',
            'gr_date' => now()->toDateString(),
            'purchase_order_id' => $po->id,
            'supplier_id' => $supplier->id,
            'status' => 'received',
            'created_by' => $user->id,
        ]);

        PurchaseInvoice::create([
            'invoice_number' => 'PINV-' . now()->year . '-0001',
            'invoice_date' => now()->subDays(5)->toDateString(),
            'due_date' => now()->subDays(2)->toDateString(),
            'goods_receipt_id' => $goodsReceipt->id,
            'purchase_order_id' => $po->id,
            'supplier_id' => $supplier->id,
            'status' => 'posted',
            'outstanding_amount' => 1000000,
            'total_amount' => 1000000,
            'paid_amount' => 0,
            'subtotal' => 1000000,
            'tax_amount' => 0,
            'created_by' => $user->id,
        ]);

        $response = $this->actingAs($user)->get(route('purchase-dashboard.index', [
            'date_from' => now()->startOfMonth()->toDateString(),
            'date_to' => now()->addDay()->toDateString(),
        ]));

        $response->assertInertia(fn ($page) => $page
            ->where('kpis.pending_prs', 1)
            ->where('kpis.pending_po_approvals', 1)
            ->where('kpis.overdue_invoices', 1)
            ->has('charts.top_suppliers', 1)
            ->has('charts.top_products', 1)
        );
    }
}

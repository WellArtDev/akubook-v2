<?php

namespace Tests\Feature;

use App\Models\FiscalPeriod;
use App\Models\GoodsReceipt;
use App\Models\PurchaseInvoice;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderLine;
use App\Models\Supplier;
use App\Models\SupplierPayment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PurchaseReportTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_purchase_report_page_loads_with_expected_shape(): void
    {
        $response = $this->actingAs($this->user)->get(route('purchase-reports.index'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('PurchaseReports/Index')
            ->has('summary')
            ->has('purchase_summary')
            ->has('by_supplier')
            ->has('ap_aging')
        );
    }

    public function test_purchase_report_aggregates_data(): void
    {
        $supplier = Supplier::factory()->create();
        $po = PurchaseOrder::factory()->create([
            'supplier_id' => $supplier->id,
            'po_date' => now()->toDateString(),
            'status' => 'approved',
            'grand_total' => 200000,
        ]);

        PurchaseOrderLine::create([
            'purchase_order_id' => $po->id,
            'line_number' => 1,
            'product_code' => 'ITM-1',
            'product_name' => 'Item 1',
            'quantity' => 10,
            'unit' => 'pcs',
            'unit_price' => 20000,
            'tax_amount' => 0,
            'line_total' => 200000,
        ]);

        $goodsReceipt = GoodsReceipt::create([
            'gr_number' => 'GR-' . now()->format('Y') . '-0001',
            'gr_date' => now()->toDateString(),
            'purchase_order_id' => $po->id,
            'supplier_id' => $supplier->id,
            'status' => 'received',
            'created_by' => $this->user->id,
        ]);

        PurchaseInvoice::factory()->create([
            'supplier_id' => $supplier->id,
            'purchase_order_id' => $po->id,
            'goods_receipt_id' => $goodsReceipt->id,
            'created_by' => $this->user->id,
            'invoice_date' => now()->toDateString(),
            'due_date' => now()->subDays(40)->toDateString(),
            'status' => 'posted',
            'total_amount' => 200000,
            'paid_amount' => 50000,
            'outstanding_amount' => 150000,
        ]);

        SupplierPayment::factory()->create([
            'supplier_id' => $supplier->id,
            'payment_date' => now()->toDateString(),
            'status' => 'posted',
            'total_amount' => 50000,
        ]);

        $response = $this->actingAs($this->user)->get(route('purchase-reports.index', [
            'date_from' => now()->startOfMonth()->toDateString(),
            'date_to' => now()->toDateString(),
        ]));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->where('summary.purchase_order_count', 1)
            ->where('summary.purchase_order_total', 200000)
            ->where('summary.payment_total', 50000)
        );
    }

    public function test_purchase_report_export_returns_rows(): void
    {
        $supplier = Supplier::factory()->create(['name' => 'PT Supplier']);

        PurchaseOrder::factory()->create([
            'supplier_id' => $supplier->id,
            'po_date' => now()->toDateString(),
            'status' => 'approved',
            'grand_total' => 100000,
        ]);

        $response = $this->actingAs($this->user)->get(route('purchase-reports.export', [
            'date_from' => now()->startOfMonth()->toDateString(),
            'date_to' => now()->toDateString(),
        ]));

        $response->assertOk();
        $response->assertJsonStructure([
            'period' => ['dateFrom', 'dateTo'],
            'rows',
        ]);
    }
}

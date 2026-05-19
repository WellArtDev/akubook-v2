<?php

namespace Tests\Feature;

use App\Models\DeliveryOrder;
use App\Models\GoodsReceipt;
use App\Models\PurchaseOrder;
use App\Models\SalesInvoice;
use App\Models\StockTransaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OperationalReportTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        $this->actingAs(User::factory()->create());
    }

    public function test_operational_report_page_can_be_opened(): void
    {
        $this->get(route('operational-reports.index'))->assertOk();
    }

    public function test_operational_report_summarizes_sales_purchase_and_stock(): void
    {
        SalesInvoice::factory()->create([
            'invoice_date' => '2026-05-15',
            'status' => 'sent',
            'grand_total' => 150000,
        ]);

        $purchaseOrder = PurchaseOrder::factory()->create([
            'po_date' => '2026-05-14',
            'status' => 'approved',
            'grand_total' => 200000,
        ]);

        GoodsReceipt::create([
            'gr_number' => 'GR-2026-0001',
            'gr_date' => '2026-05-16',
            'purchase_order_id' => $purchaseOrder->id,
            'supplier_id' => $purchaseOrder->supplier_id,
            'status' => 'received',
            'created_by' => auth()->id(),
        ]);

        DeliveryOrder::factory()->create([
            'do_date' => '2026-05-17',
            'status' => 'delivered',
        ]);

        StockTransaction::factory()->create([
            'movement_date' => '2026-05-18',
            'movement_type' => 'purchase_receipt',
            'quantity_in' => 10,
            'quantity_out' => 0,
        ]);

        StockTransaction::factory()->create([
            'movement_date' => '2026-05-18',
            'movement_type' => 'sales_delivery',
            'quantity_in' => 0,
            'quantity_out' => 4,
        ]);

        $this->get(route('operational-reports.index', [
            'date_from' => '2026-05-01',
            'date_to' => '2026-05-31',
        ]))
            ->assertInertia(fn ($page) => $page
                ->component('OperationalReports/Index')
                ->where('summary.sales_invoice_count', 1)
                ->where('summary.sales_invoice_total', 150000)
                ->where('summary.purchase_order_count', 1)
                ->where('summary.purchase_order_total', 200000)
                ->where('summary.goods_receipt_count', 1)
                ->where('summary.delivery_order_count', 1)
                ->where('summary.stock_movement_count', 2)
                ->where('summary.stock_net_quantity', 6)
            );
    }

    public function test_operational_report_filters_by_period(): void
    {
        SalesInvoice::factory()->create([
            'invoice_date' => '2026-04-15',
            'status' => 'sent',
            'grand_total' => 150000,
        ]);

        $this->get(route('operational-reports.index', [
            'date_from' => '2026-05-01',
            'date_to' => '2026-05-31',
        ]))
            ->assertInertia(fn ($page) => $page
                ->component('OperationalReports/Index')
                ->where('summary.sales_invoice_count', 0)
                ->where('summary.sales_invoice_total', 0)
            );
    }
}

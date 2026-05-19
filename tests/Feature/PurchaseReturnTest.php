<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\Branch;
use App\Models\FiscalPeriod;
use App\Models\GoodsReceipt;
use App\Models\GoodsReceiptLine;
use App\Models\PurchaseInvoice;
use App\Models\PurchaseInvoiceLine;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderLine;
use App\Models\PurchaseReturn;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PurchaseReturnTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Supplier $supplier;
    protected PurchaseOrder $purchaseOrder;
    protected PurchaseInvoice $purchaseInvoice;
    protected PurchaseInvoiceLine $invoiceLine;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();

        $this->user = User::factory()->create();
        $this->supplier = Supplier::factory()->create();
        $branch = Branch::factory()->create();

        $this->purchaseOrder = PurchaseOrder::factory()->create([
            'supplier_id' => $this->supplier->id,
            'delivery_address_id' => $branch->id,
            'status' => 'in_progress',
            'created_by' => $this->user->id,
        ]);

        $poLine = PurchaseOrderLine::create([
            'purchase_order_id' => $this->purchaseOrder->id,
            'line_number' => 1,
            'product_code' => 'PRD-001',
            'product_name' => 'Raw Material A',
            'description' => 'Raw Material A',
            'quantity' => 10,
            'unit' => 'pcs',
            'unit_price' => 1000,
            'tax_amount' => 0,
            'line_total' => 10000,
            'received_quantity' => 8,
            'invoiced_quantity' => 0,
        ]);

        $goodsReceipt = GoodsReceipt::factory()->create([
            'gr_number' => 'GR-2026-0001',
            'gr_date' => now()->toDateString(),
            'purchase_order_id' => $this->purchaseOrder->id,
            'supplier_id' => $this->supplier->id,
            'receive_location_id' => $branch->id,
            'status' => 'received',
            'created_by' => $this->user->id,
        ]);

        $grLine = GoodsReceiptLine::factory()->create([
            'goods_receipt_id' => $goodsReceipt->id,
            'purchase_order_line_id' => $poLine->id,
            'line_number' => 1,
            'product_code' => $poLine->product_code,
            'product_name' => $poLine->product_name,
            'description' => $poLine->description,
            'po_quantity' => 10,
            'previously_received_quantity' => 0,
            'remaining_quantity' => 2,
            'receipt_quantity' => 8,
            'accepted_quantity' => 8,
            'rejected_quantity' => 0,
            'unit' => $poLine->unit,
        ]);

        $this->purchaseInvoice = PurchaseInvoice::factory()->create([
            'goods_receipt_id' => $goodsReceipt->id,
            'purchase_order_id' => $this->purchaseOrder->id,
            'supplier_id' => $this->supplier->id,
            'status' => 'posted',
            'created_by' => $this->user->id,
        ]);

        $this->invoiceLine = PurchaseInvoiceLine::create([
            'purchase_invoice_id' => $this->purchaseInvoice->id,
            'goods_receipt_line_id' => $grLine->id,
            'purchase_order_line_id' => $poLine->id,
            'line_number' => 1,
            'product_code' => $poLine->product_code,
            'product_name' => $poLine->product_name,
            'description' => $poLine->description,
            'ordered_quantity' => 10,
            'received_quantity' => 8,
            'previously_invoiced_quantity' => 0,
            'remaining_to_invoice_quantity' => 8,
            'invoice_quantity' => 8,
            'unit' => $poLine->unit,
            'unit_price' => 1000,
            'tax_percentage' => 11,
            'tax_amount' => 880,
            'line_total' => 8000,
        ]);
    }

    public function test_user_can_create_purchase_return_from_purchase_invoice(): void
    {
        $payload = [
            'return_date' => now()->toDateString(),
            'purchase_invoice_id' => $this->purchaseInvoice->id,
            'return_reason' => 'Defective item',
            'lines' => [
                [
                    'purchase_invoice_line_id' => $this->invoiceLine->id,
                    'return_quantity' => 3,
                    'tax_percentage' => 11,
                ],
            ],
        ];

        $response = $this->actingAs($this->user)->post(route('purchase-returns.store'), $payload);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect(route('purchase-returns.index'));
        $this->assertDatabaseHas('purchase_returns', [
            'purchase_invoice_id' => $this->purchaseInvoice->id,
            'status' => 'draft',
        ]);
        $this->assertDatabaseHas('purchase_return_lines', [
            'purchase_invoice_line_id' => $this->invoiceLine->id,
            'return_quantity' => 3,
        ]);
    }

    public function test_cannot_return_more_than_remaining_quantity(): void
    {
        $payload = [
            'return_date' => now()->toDateString(),
            'purchase_invoice_id' => $this->purchaseInvoice->id,
            'return_reason' => 'Wrong item',
            'lines' => [
                [
                    'purchase_invoice_line_id' => $this->invoiceLine->id,
                    'return_quantity' => 9,
                    'tax_percentage' => 11,
                ],
            ],
        ];

        $response = $this->actingAs($this->user)->post(route('purchase-returns.store'), $payload);

        $response->assertStatus(422);
    }

    public function test_purchase_return_can_be_approved_received_completed_with_journal(): void
    {
        FiscalPeriod::create([
            'name' => '2026-05',
            'start_date' => '2026-05-01',
            'end_date' => '2026-05-31',
            'status' => 'open',
            'is_current' => true,
            'created_by' => $this->user->id,
        ]);

        Account::factory()->create(['code' => '2-1100']);
        Account::factory()->create(['code' => '1-1400']);
        Account::factory()->create(['code' => '1-1500']);

        $purchaseReturn = PurchaseReturn::factory()->create([
            'purchase_invoice_id' => $this->purchaseInvoice->id,
            'purchase_order_id' => $this->purchaseOrder->id,
            'supplier_id' => $this->supplier->id,
            'status' => 'draft',
            'created_by' => $this->user->id,
        ]);

        $line = $purchaseReturn->lines()->create([
            'purchase_invoice_line_id' => $this->invoiceLine->id,
            'line_number' => 1,
            'product_id' => null,
            'product_name' => $this->invoiceLine->product_name,
            'return_quantity' => 2,
            'accepted_quantity' => 0,
            'rejected_quantity' => 0,
            'unit' => $this->invoiceLine->unit,
            'unit_price' => 1000,
            'tax_percentage' => 11,
            'tax_amount' => 220,
            'line_total' => 2000,
        ]);

        $purchaseReturn->update([
            'subtotal' => 2000,
            'tax_amount' => 220,
            'total_amount' => 2220,
        ]);

        $this->actingAs($this->user)->post(route('purchase-returns.approve', $purchaseReturn));
        $purchaseReturn->refresh();
        $this->assertEquals('approved', $purchaseReturn->status);

        $this->actingAs($this->user)->post(route('purchase-returns.receive', $purchaseReturn), [
            'lines' => [
                [
                    'id' => $line->id,
                    'accepted_quantity' => 2,
                    'rejected_quantity' => 0,
                ],
            ],
        ]);
        $purchaseReturn->refresh();
        $this->assertEquals('received', $purchaseReturn->status);

        $this->actingAs($this->user)->post(route('purchase-returns.complete', $purchaseReturn));
        $purchaseReturn->refresh();
        $this->assertEquals('completed', $purchaseReturn->status);
        $this->assertNotNull($purchaseReturn->journal_entry_id);
    }
}

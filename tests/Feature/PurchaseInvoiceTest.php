<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\Branch;
use App\Models\FiscalPeriod;
use App\Models\GoodsReceipt;
use App\Models\JournalEntry;
use App\Models\PurchaseInvoice;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderLine;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PurchaseInvoiceTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private GoodsReceipt $goodsReceipt;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        $this->user = User::factory()->create();
        $supplier = Supplier::factory()->create();
        $branch = Branch::factory()->create();

        $purchaseOrder = PurchaseOrder::factory()->create([
            'supplier_id' => $supplier->id,
            'delivery_address_id' => $branch->id,
            'status' => 'in_progress',
            'created_by' => $this->user->id,
        ]);

        $poLine = PurchaseOrderLine::create([
            'purchase_order_id' => $purchaseOrder->id,
            'line_number' => 1,
            'product_code' => 'RM-001',
            'product_name' => 'Raw Material',
            'quantity' => 10,
            'unit' => 'PCS',
            'unit_price' => 1000,
            'tax_amount' => 0,
            'received_quantity' => 8,
            'invoiced_quantity' => 0,
        ]);

        $this->goodsReceipt = GoodsReceipt::create([
            'gr_number' => 'GR-' . now()->year . '-0001',
            'gr_date' => now()->toDateString(),
            'purchase_order_id' => $purchaseOrder->id,
            'supplier_id' => $supplier->id,
            'receive_location_id' => $branch->id,
            'status' => 'received',
            'received_by' => $this->user->id,
            'received_at' => now(),
            'created_by' => $this->user->id,
        ]);

        $this->goodsReceipt->lines()->create([
            'purchase_order_line_id' => $poLine->id,
            'line_number' => 1,
            'product_code' => 'RM-001',
            'product_name' => 'Raw Material',
            'po_quantity' => 10,
            'previously_received_quantity' => 0,
            'remaining_quantity' => 10,
            'receipt_quantity' => 8,
            'accepted_quantity' => 8,
            'rejected_quantity' => 0,
            'unit' => 'PCS',
        ]);
    }

    public function test_user_can_create_purchase_invoice_from_goods_receipt(): void
    {
        $grLine = $this->goodsReceipt->lines()->first();

        $response = $this->actingAs($this->user)->post(route('purchase-invoices.store'), [
            'invoice_date' => now()->toDateString(),
            'due_date' => now()->addDays(30)->toDateString(),
            'goods_receipt_id' => $this->goodsReceipt->id,
            'supplier_invoice_number' => 'SUP-INV-001',
            'lines' => [[
                'goods_receipt_line_id' => $grLine->id,
                'invoice_quantity' => 5,
                'tax_percentage' => 11,
            ]],
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('purchase_invoices', [
            'goods_receipt_id' => $this->goodsReceipt->id,
            'status' => 'draft',
        ]);
        $this->assertDatabaseHas('purchase_invoice_lines', [
            'goods_receipt_line_id' => $grLine->id,
            'invoice_quantity' => 5,
        ]);
    }

    public function test_cannot_invoice_more_than_remaining_quantity(): void
    {
        $grLine = $this->goodsReceipt->lines()->first();

        $this->actingAs($this->user)
            ->post(route('purchase-invoices.store'), [
                'invoice_date' => now()->toDateString(),
                'due_date' => now()->addDays(30)->toDateString(),
                'goods_receipt_id' => $this->goodsReceipt->id,
                'lines' => [[
                    'goods_receipt_line_id' => $grLine->id,
                    'invoice_quantity' => 9,
                ]],
            ])
            ->assertStatus(422);
    }

    public function test_posting_purchase_invoice_creates_journal_and_updates_po_invoiced_quantity(): void
    {
        FiscalPeriod::create([
            'name' => now()->format('F Y'),
            'start_date' => now()->startOfMonth()->toDateString(),
            'end_date' => now()->endOfMonth()->toDateString(),
            'status' => 'open',
            'is_current' => true,
        ]);

        Account::factory()->create(['code' => '1-1400', 'name' => 'Inventory', 'type' => 'asset', 'category' => 'current_asset']);
        Account::factory()->create(['code' => '1-1500', 'name' => 'Input Tax', 'type' => 'asset', 'category' => 'current_asset']);
        Account::factory()->create(['code' => '2-1100', 'name' => 'AP', 'type' => 'liability', 'category' => 'current_liability']);

        $grLine = $this->goodsReceipt->lines()->first();
        $invoice = PurchaseInvoice::create([
            'invoice_number' => PurchaseInvoice::generateNumber(),
            'invoice_date' => now()->toDateString(),
            'due_date' => now()->addDays(30)->toDateString(),
            'goods_receipt_id' => $this->goodsReceipt->id,
            'purchase_order_id' => $this->goodsReceipt->purchase_order_id,
            'supplier_id' => $this->goodsReceipt->supplier_id,
            'status' => 'draft',
            'created_by' => $this->user->id,
        ]);

        $invoice->lines()->create([
            'goods_receipt_line_id' => $grLine->id,
            'purchase_order_line_id' => $grLine->purchase_order_line_id,
            'line_number' => 1,
            'product_code' => $grLine->product_code,
            'product_name' => $grLine->product_name,
            'ordered_quantity' => 10,
            'received_quantity' => 8,
            'previously_invoiced_quantity' => 0,
            'remaining_to_invoice_quantity' => 8,
            'invoice_quantity' => 4,
            'unit' => 'PCS',
            'unit_price' => 1000,
            'tax_percentage' => 11,
            'tax_amount' => 440,
            'line_total' => 4000,
        ]);
        $invoice->calculateTotals();
        $invoice->save();

        $response = $this->actingAs($this->user)->post(route('purchase-invoices.post', $invoice));

        $response->assertRedirect();
        $invoice->refresh();
        $this->assertSame('posted', $invoice->status);
        $this->assertNotNull($invoice->journal_entry_id);
        $this->assertDatabaseHas('purchase_order_lines', [
            'id' => $grLine->purchase_order_line_id,
            'invoiced_quantity' => 4,
        ]);

        $journal = JournalEntry::find($invoice->journal_entry_id);
        $this->assertNotNull($journal);
        $this->assertDatabaseHas('journal_entry_lines', [
            'journal_entry_id' => $journal->id,
            'debit' => 4000,
            'credit' => 0,
        ]);
        $this->assertDatabaseHas('journal_entry_lines', [
            'journal_entry_id' => $journal->id,
            'debit' => 440,
            'credit' => 0,
        ]);
        $this->assertDatabaseHas('journal_entry_lines', [
            'journal_entry_id' => $journal->id,
            'debit' => 0,
            'credit' => 4440,
        ]);
    }
}

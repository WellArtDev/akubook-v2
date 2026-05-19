<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\FiscalPeriod;
use App\Models\GoodsReceipt;
use App\Models\PurchaseInvoice;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Models\SupplierPayment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SupplierPaymentTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Supplier $supplier;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->supplier = Supplier::factory()->create();

        FiscalPeriod::create([
            'name' => now()->format('F Y'),
            'status' => 'open',
            'is_current' => true,
            'start_date' => now()->startOfMonth()->toDateString(),
            'end_date' => now()->endOfMonth()->toDateString(),
        ]);

        Account::factory()->create(['code' => '2-1100', 'type' => 'liability', 'category' => 'current_liability']);
        Account::factory()->create(['code' => '1-1100', 'type' => 'asset', 'category' => 'current_asset']);
    }

    public function test_user_can_create_supplier_payment_with_number_format(): void
    {
        $response = $this->actingAs($this->user)->post(route('supplier-payments.store'), [
            'payment_date' => now()->toDateString(),
            'supplier_id' => $this->supplier->id,
            'payment_method' => 'bank_transfer',
            'total_amount' => 500000,
            'notes' => 'Payment test',
        ]);

        $payment = SupplierPayment::latest()->first();

        $response->assertRedirect(route('supplier-payments.show', $payment));
        $this->assertMatchesRegularExpression('/^SPAY-' . now()->year . '-\d{4}$/', $payment->payment_number);
        $this->assertSame('draft', $payment->status);
    }

    public function test_user_can_allocate_partial_payment_to_invoice(): void
    {
        $purchaseOrder = PurchaseOrder::factory()->create([
            'supplier_id' => $this->supplier->id,
            'created_by' => $this->user->id,
        ]);

        $goodsReceipt = GoodsReceipt::create([
            'gr_number' => 'GR-' . now()->year . '-0001',
            'gr_date' => now()->toDateString(),
            'purchase_order_id' => $purchaseOrder->id,
            'supplier_id' => $this->supplier->id,
            'status' => 'received',
            'created_by' => $this->user->id,
        ]);

        $invoice = PurchaseInvoice::create([
            'invoice_number' => 'PINV-' . now()->year . '-0001',
            'invoice_date' => now()->toDateString(),
            'due_date' => now()->addDays(30)->toDateString(),
            'goods_receipt_id' => $goodsReceipt->id,
            'purchase_order_id' => $purchaseOrder->id,
            'supplier_id' => $this->supplier->id,
            'status' => 'posted',
            'subtotal' => 1000000,
            'tax_amount' => 0,
            'total_amount' => 1000000,
            'paid_amount' => 0,
            'outstanding_amount' => 1000000,
            'created_by' => $this->user->id,
        ]);

        $this->actingAs($this->user)->post(route('supplier-payments.store'), [
            'payment_date' => now()->toDateString(),
            'supplier_id' => $this->supplier->id,
            'payment_method' => 'cash',
            'total_amount' => 400000,
            'allocations' => [
                ['purchase_invoice_id' => $invoice->id, 'allocated_amount' => 400000],
            ],
        ]);

        $invoice->refresh();
        $payment = SupplierPayment::latest()->first();

        $this->assertSame('400000.00', (string) $payment->allocated_amount);
        $this->assertSame('600000.00', (string) $invoice->outstanding_amount);
        $this->assertSame('partially_paid', $invoice->status);
    }

    public function test_posting_supplier_payment_creates_journal_entry(): void
    {
        $payment = SupplierPayment::factory()->create([
            'supplier_id' => $this->supplier->id,
            'total_amount' => 750000,
            'unapplied_amount' => 750000,
            'status' => 'draft',
            'created_by' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)->post(route('supplier-payments.post', $payment));

        $payment->refresh();

        $response->assertRedirect();
        $this->assertSame('posted', $payment->status);
        $this->assertNotNull($payment->journal_entry_id);
        $this->assertDatabaseHas('journal_entries', [
            'id' => $payment->journal_entry_id,
            'reference_type' => 'supplier_payment',
            'reference_id' => $payment->id,
        ]);
    }
}

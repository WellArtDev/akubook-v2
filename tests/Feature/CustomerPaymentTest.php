<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\Customer;
use App\Models\CustomerPayment;
use App\Models\FiscalPeriod;
use App\Models\SalesInvoice;
use App\Models\SalesOrder;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerPaymentTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Customer $customer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->customer = Customer::factory()->create();
        $this->withoutVite();
    }

    public function test_create_payment_uses_payment_number_format_and_supported_method(): void
    {
        $response = $this->actingAs($this->user)->post(route('customer-payments.store'), [
            'payment_date' => now()->toDateString(),
            'customer_id' => $this->customer->id,
            'payment_method' => 'credit_card',
            'reference_number' => 'CC-001',
            'total_amount' => 500000,
        ]);

        $payment = CustomerPayment::query()->firstOrFail();

        $response->assertRedirect(route('customer-payments.show', $payment));
        $this->assertMatchesRegularExpression('/^PAY-' . now()->year . '-\d{4}$/', $payment->payment_number);
        $this->assertSame('draft', $payment->status);
        $this->assertSame('credit_card', $payment->payment_method);
    }

    public function test_partial_allocation_updates_invoice_balance(): void
    {
        $invoice = $this->salesInvoice(1000000);

        $response = $this->actingAs($this->user)->post(route('customer-payments.store'), [
            'payment_date' => now()->toDateString(),
            'customer_id' => $this->customer->id,
            'payment_method' => 'bank_transfer',
            'total_amount' => 300000,
            'allocations' => [
                [
                    'sales_invoice_id' => $invoice->id,
                    'allocated_amount' => 300000,
                ],
            ],
        ]);

        $payment = CustomerPayment::query()->firstOrFail();
        $invoice->refresh();

        $response->assertRedirect(route('customer-payments.show', $payment));
        $this->assertSame('300000.00', (string) $payment->allocated_amount);
        $this->assertSame('0.00', (string) $payment->unapplied_amount);
        $this->assertSame('300000.00', (string) $invoice->amount_paid);
        $this->assertSame('700000.00', (string) $invoice->amount_due);
        $this->assertSame('partially_paid', $invoice->status);
    }

    public function test_overpayment_keeps_unapplied_amount(): void
    {
        $invoice = $this->salesInvoice(1000000);

        $this->actingAs($this->user)->post(route('customer-payments.store'), [
            'payment_date' => now()->toDateString(),
            'customer_id' => $this->customer->id,
            'payment_method' => 'cash',
            'total_amount' => 1200000,
            'allocations' => [
                [
                    'sales_invoice_id' => $invoice->id,
                    'allocated_amount' => 1000000,
                ],
            ],
        ]);

        $payment = CustomerPayment::query()->firstOrFail();
        $invoice->refresh();

        $this->assertSame('1000000.00', (string) $payment->allocated_amount);
        $this->assertSame('200000.00', (string) $payment->unapplied_amount);
        $this->assertSame('paid', $invoice->status);
    }

    public function test_posting_creates_cash_and_ar_journal_entry(): void
    {
        $this->accountingSetup();
        $invoice = $this->salesInvoice(1000000);

        $this->actingAs($this->user)->post(route('customer-payments.store'), [
            'payment_date' => now()->toDateString(),
            'customer_id' => $this->customer->id,
            'payment_method' => 'cash',
            'total_amount' => 1000000,
            'allocations' => [
                [
                    'sales_invoice_id' => $invoice->id,
                    'allocated_amount' => 1000000,
                ],
            ],
        ]);

        $payment = CustomerPayment::query()->firstOrFail();

        $this->actingAs($this->user)->post(route('customer-payments.post', $payment));

        $payment->refresh();

        $this->assertSame('posted', $payment->status);
        $this->assertNotNull($payment->journal_entry_id);
        $this->assertDatabaseHas('journal_entries', [
            'id' => $payment->journal_entry_id,
            'reference_type' => 'customer_payment',
            'reference_id' => $payment->id,
            'status' => 'posted',
        ]);
        $this->assertDatabaseHas('journal_entry_lines', [
            'journal_entry_id' => $payment->journal_entry_id,
            'account_id' => Account::where('code', '1-1100')->value('id'),
            'debit' => 1000000,
            'credit' => 0,
        ]);
        $this->assertDatabaseHas('journal_entry_lines', [
            'journal_entry_id' => $payment->journal_entry_id,
            'account_id' => Account::where('code', '1-1200')->value('id'),
            'debit' => 0,
            'credit' => 1000000,
        ]);
    }

    private function salesInvoice(float $amount): SalesInvoice
    {
        $salesOrder = SalesOrder::create([
            'so_number' => 'SO-' . now()->year . '-' . str_pad((string) (SalesOrder::query()->count() + 1), 4, '0', STR_PAD_LEFT),
            'so_date' => now()->toDateString(),
            'customer_id' => $this->customer->id,
            'sales_person_id' => $this->user->id,
            'status' => 'approved',
            'subtotal' => $amount,
            'grand_total' => $amount,
            'total_amount' => $amount,
            'created_by' => $this->user->id,
        ]);

        return SalesInvoice::create([
            'invoice_number' => 'INV-' . now()->year . '-' . str_pad((string) (SalesInvoice::query()->count() + 1), 4, '0', STR_PAD_LEFT),
            'invoice_date' => now()->toDateString(),
            'due_date' => now()->addDays(30)->toDateString(),
            'sales_order_id' => $salesOrder->id,
            'customer_id' => $this->customer->id,
            'status' => 'sent',
            'subtotal' => $amount,
            'grand_total' => $amount,
            'amount_paid' => 0,
            'amount_due' => $amount,
            'created_by' => $this->user->id,
        ]);
    }

    private function accountingSetup(): void
    {
        FiscalPeriod::create([
            'name' => now()->format('F Y'),
            'start_date' => now()->startOfMonth()->toDateString(),
            'end_date' => now()->endOfMonth()->toDateString(),
            'status' => 'open',
            'is_current' => true,
        ]);

        Account::create(['code' => '1-1100', 'name' => 'Cash', 'type' => 'asset', 'is_active' => true]);
        Account::create(['code' => '1-1200', 'name' => 'Accounts Receivable', 'type' => 'asset', 'is_active' => true]);
        Account::create(['code' => '2-1300', 'name' => 'Unapplied Cash', 'type' => 'liability', 'is_active' => true]);
    }
}

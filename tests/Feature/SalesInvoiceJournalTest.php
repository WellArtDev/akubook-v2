<?php

namespace Tests\Feature;

use App\Models\SalesInvoice;
use App\Models\SalesOrder;
use App\Models\Customer;
use App\Models\User;
use App\Models\Account;
use App\Models\FiscalPeriod;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SalesInvoiceJournalTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $customer;
    protected $salesOrder;
    protected $fiscalPeriod;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate');
        
        // Seed accounts
        $this->artisan('db:seed', ['--class' => 'ChartOfAccountsSeeder']);
        
        // Create tax payable account
        $liabilityParent = Account::where('code', '2-1000')->first();
        Account::create([
            'code' => '2-1200',
            'name' => 'Hutang Pajak (PPN)',
            'type' => 'liability',
            'category' => 'current_liability',
            'parent_id' => $liabilityParent->id,
            'level' => 3,
            'is_header' => false,
            'is_active' => true,
            'balance' => 0,
        ]);
        
        // Create fiscal period
        $this->fiscalPeriod = FiscalPeriod::create([
            'name' => '2026',
            'start_date' => '2026-01-01',
            'end_date' => '2026-12-31',
            'status' => 'open',
            'is_current' => true,
        ]);
        
        $this->user = User::factory()->create();
        $this->customer = Customer::factory()->create();
        $this->salesOrder = SalesOrder::factory()->create([
            'customer_id' => $this->customer->id,
            'status' => 'approved',
        ]);
    }

    /** @test */
    public function test_journal_entry_is_created_when_invoice_is_sent()
    {
        $invoice = SalesInvoice::factory()->create([
            'status' => 'draft',
            'sales_order_id' => $this->salesOrder->id,
            'customer_id' => $this->customer->id,
            'created_by' => $this->user->id,
            'subtotal' => 10000,
            'tax_amount' => 1100,
            'grand_total' => 11100,
        ]);

        $this->actingAs($this->user);
        $invoice->send();

        $invoice->refresh();
        $this->assertNotNull($invoice->journal_entry_id);
        $this->assertEquals('sent', $invoice->status);

        $journal = $invoice->journalEntry;
        $this->assertNotNull($journal);
        $this->assertEquals(11100, $journal->total_debit);
        $this->assertEquals(11100, $journal->total_credit);
        $this->assertEquals('posted', $journal->status);
        $this->assertEquals(3, $journal->lines->count()); // DR AR, CR Sales, CR Tax
    }

    /** @test */
    public function test_journal_entry_lines_are_correct()
    {
        $invoice = SalesInvoice::factory()->create([
            'status' => 'draft',
            'sales_order_id' => $this->salesOrder->id,
            'customer_id' => $this->customer->id,
            'created_by' => $this->user->id,
            'subtotal' => 10000,
            'tax_amount' => 1100,
            'grand_total' => 11100,
        ]);

        $this->actingAs($this->user);
        $invoice->send();

        $journal = $invoice->journalEntry;
        $lines = $journal->lines;

        // DR: Accounts Receivable
        $arLine = $lines->where('debit', '>', 0)->first();
        $this->assertEquals(11100, $arLine->debit);
        $this->assertEquals(0, $arLine->credit);

        // CR: Sales Revenue
        $salesLine = $lines->where('credit', 10000)->first();
        $this->assertNotNull($salesLine);
        $this->assertEquals(0, $salesLine->debit);

        // CR: Tax Payable
        $taxLine = $lines->where('credit', 1100)->first();
        $this->assertNotNull($taxLine);
        $this->assertEquals(0, $taxLine->debit);
    }

    /** @test */
    public function test_journal_entry_is_reversed_when_invoice_is_cancelled()
    {
        $invoice = SalesInvoice::factory()->create([
            'status' => 'draft',
            'sales_order_id' => $this->salesOrder->id,
            'customer_id' => $this->customer->id,
            'created_by' => $this->user->id,
            'subtotal' => 10000,
            'tax_amount' => 1100,
            'grand_total' => 11100,
            'amount_paid' => 0,
        ]);

        $this->actingAs($this->user);
        $invoice->send();

        $originalJournalId = $invoice->journal_entry_id;

        $invoice->cancel($this->user->id, 'Customer request');

        $invoice->refresh();
        $this->assertEquals('cancelled', $invoice->status);

        // Check reversal journal was created
        $reversalJournal = \App\Models\JournalEntry::where('type', 'manual')
            ->where('reference_id', $invoice->id)
            ->where('description', 'like', 'Reversal%')
            ->first();

        $this->assertNotNull($reversalJournal);
        $this->assertEquals(11100, $reversalJournal->total_debit);
        $this->assertEquals(11100, $reversalJournal->total_credit);
    }
}

<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\BankAccount;
use App\Models\CashAccount;
use App\Models\JournalEntry;
use App\Models\FiscalPeriod;
use App\Models\Voucher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VoucherTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();

        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    }

    public function test_user_can_create_payment_voucher(): void
    {
        $cashAccount = $this->createCashAccount();
        $counterpart = Account::factory()->create([
            'type' => 'expense',
            'category' => 'operating_expense',
            'is_header' => false,
            'is_active' => true,
        ]);

        $response = $this->post(route('vouchers.store'), [
            'voucher_type' => 'payment',
            'voucher_date' => now()->toDateString(),
            'cash_bank_type' => 'cash',
            'cash_bank_account_id' => $cashAccount->id,
            'counterpart_account_id' => $counterpart->id,
            'amount' => 150000,
            'reference_number' => 'INV-001',
            'notes' => 'Payment test',
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('vouchers', [
            'voucher_type' => 'payment',
            'cash_bank_type' => 'cash',
            'amount' => 150000,
            'status' => 'draft',
        ]);
    }

    public function test_posting_payment_voucher_creates_journal(): void
    {
        $cashAccount = $this->createCashAccount();
        $counterpart = Account::factory()->create([
            'type' => 'expense',
            'category' => 'operating_expense',
            'is_header' => false,
            'is_active' => true,
        ]);

        FiscalPeriod::create([
            'name' => now()->format('F Y'),
            'start_date' => now()->startOfMonth()->toDateString(),
            'end_date' => now()->endOfMonth()->toDateString(),
            'status' => 'open',
            'is_current' => true,
        ]);

        $voucher = Voucher::query()->create([
            'voucher_number' => Voucher::generateNumber('payment'),
            'voucher_type' => 'payment',
            'voucher_date' => now()->toDateString(),
            'cash_bank_type' => 'cash',
            'cash_bank_account_id' => $cashAccount->id,
            'counterpart_account_id' => $counterpart->id,
            'amount' => 200000,
            'status' => 'draft',
            'created_by' => $this->user->id,
        ]);

        $response = $this->post(route('vouchers.post', $voucher));

        $response->assertRedirect();
        $voucher->refresh();

        $this->assertSame('posted', $voucher->status);
        $this->assertNotNull($voucher->journal_entry_id);

        $journal = JournalEntry::query()->findOrFail($voucher->journal_entry_id);
        $this->assertSame('auto_payment', $journal->type);
        $this->assertDatabaseHas('journal_entry_lines', [
            'journal_entry_id' => $journal->id,
            'account_id' => $counterpart->id,
            'debit' => 200000,
            'credit' => 0,
        ]);
        $this->assertDatabaseHas('journal_entry_lines', [
            'journal_entry_id' => $journal->id,
            'account_id' => $cashAccount->account_id,
            'debit' => 0,
            'credit' => 200000,
        ]);
    }

    public function test_posting_receipt_voucher_creates_journal(): void
    {
        $bankAccount = $this->createBankAccount();
        $counterpart = Account::factory()->create([
            'type' => 'revenue',
            'category' => 'operating_revenue',
            'is_header' => false,
            'is_active' => true,
        ]);

        FiscalPeriod::create([
            'name' => now()->format('F Y'),
            'start_date' => now()->startOfMonth()->toDateString(),
            'end_date' => now()->endOfMonth()->toDateString(),
            'status' => 'open',
            'is_current' => true,
        ]);

        $voucher = Voucher::query()->create([
            'voucher_number' => Voucher::generateNumber('receipt'),
            'voucher_type' => 'receipt',
            'voucher_date' => now()->toDateString(),
            'cash_bank_type' => 'bank',
            'cash_bank_account_id' => $bankAccount->id,
            'counterpart_account_id' => $counterpart->id,
            'amount' => 300000,
            'status' => 'draft',
            'created_by' => $this->user->id,
        ]);

        $response = $this->post(route('vouchers.post', $voucher));

        $response->assertRedirect();
        $voucher->refresh();

        $this->assertSame('posted', $voucher->status);

        $journal = JournalEntry::query()->findOrFail($voucher->journal_entry_id);
        $this->assertSame('auto_receipt', $journal->type);
        $this->assertDatabaseHas('journal_entry_lines', [
            'journal_entry_id' => $journal->id,
            'account_id' => $bankAccount->account_id,
            'debit' => 300000,
            'credit' => 0,
        ]);
        $this->assertDatabaseHas('journal_entry_lines', [
            'journal_entry_id' => $journal->id,
            'account_id' => $counterpart->id,
            'debit' => 0,
            'credit' => 300000,
        ]);
    }

    public function test_user_can_cancel_voucher(): void
    {
        $cashAccount = $this->createCashAccount();
        $counterpart = Account::factory()->create([
            'type' => 'expense',
            'category' => 'operating_expense',
            'is_header' => false,
            'is_active' => true,
        ]);

        $voucher = Voucher::query()->create([
            'voucher_number' => Voucher::generateNumber('payment'),
            'voucher_type' => 'payment',
            'voucher_date' => now()->toDateString(),
            'cash_bank_type' => 'cash',
            'cash_bank_account_id' => $cashAccount->id,
            'counterpart_account_id' => $counterpart->id,
            'amount' => 50000,
            'status' => 'draft',
            'created_by' => $this->user->id,
        ]);

        $response = $this->post(route('vouchers.cancel', $voucher));

        $response->assertRedirect();

        $this->assertDatabaseHas('vouchers', [
            'id' => $voucher->id,
            'status' => 'cancelled',
            'cancelled_by' => $this->user->id,
        ]);
    }

    private function createCashAccount(): CashAccount
    {
        $asset = Account::factory()->create([
            'type' => 'asset',
            'category' => 'current_asset',
            'is_header' => false,
            'is_active' => true,
        ]);

        return CashAccount::query()->create([
            'code' => 'CASH-MAIN',
            'name' => 'Kas Utama',
            'account_id' => $asset->id,
            'opening_balance' => 1000000,
            'is_active' => true,
            'created_by' => $this->user->id,
        ]);
    }

    private function createBankAccount(): BankAccount
    {
        $asset = Account::factory()->create([
            'type' => 'asset',
            'category' => 'current_asset',
            'is_header' => false,
            'is_active' => true,
        ]);

        return BankAccount::query()->create([
            'code' => 'BANK-BCA',
            'name' => 'BCA Operasional',
            'bank_name' => 'BCA',
            'account_number' => '1234567890',
            'account_holder' => 'PT AkuBook',
            'account_id' => $asset->id,
            'opening_balance' => 5000000,
            'is_active' => true,
            'created_by' => $this->user->id,
        ]);
    }
}

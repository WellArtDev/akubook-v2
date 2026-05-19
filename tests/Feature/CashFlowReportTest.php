<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\BankAccount;
use App\Models\CashAccount;
use App\Models\User;
use App\Models\Voucher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CashFlowReportTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    }

    public function test_cash_flow_report_page_can_be_opened(): void
    {
        $response = $this->get(route('cash-flow-reports.index'));

        $response->assertOk();
    }

    public function test_cash_flow_summary_is_calculated_from_posted_vouchers(): void
    {
        $cash = $this->createCashAccount(1000000);
        $counterpart = Account::factory()->create(['is_header' => false, 'is_active' => true]);

        Voucher::query()->create([
            'voucher_number' => 'RV-2026-0001',
            'voucher_type' => 'receipt',
            'voucher_date' => now()->toDateString(),
            'cash_bank_type' => 'cash',
            'cash_bank_account_id' => $cash->id,
            'counterpart_account_id' => $counterpart->id,
            'amount' => 300000,
            'status' => 'posted',
            'created_by' => $this->user->id,
        ]);

        Voucher::query()->create([
            'voucher_number' => 'PV-2026-0001',
            'voucher_type' => 'payment',
            'voucher_date' => now()->toDateString(),
            'cash_bank_type' => 'cash',
            'cash_bank_account_id' => $cash->id,
            'counterpart_account_id' => $counterpart->id,
            'amount' => 100000,
            'status' => 'posted',
            'created_by' => $this->user->id,
        ]);

        $response = $this->get(route('cash-flow-reports.index', [
            'cash_bank_type' => 'cash',
            'cash_bank_account_id' => $cash->id,
            'date_from' => now()->startOfMonth()->toDateString(),
            'date_to' => now()->endOfMonth()->toDateString(),
        ]));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->where('summary.opening_balance', 1000000)
            ->where('summary.cash_in', 300000)
            ->where('summary.cash_out', 100000)
            ->where('summary.closing_balance', 1200000)
        );
    }

    public function test_cash_flow_filter_by_bank_type_works(): void
    {
        $bank = $this->createBankAccount(2000000);
        $counterpart = Account::factory()->create(['is_header' => false, 'is_active' => true]);

        Voucher::query()->create([
            'voucher_number' => 'RV-2026-0002',
            'voucher_type' => 'receipt',
            'voucher_date' => now()->toDateString(),
            'cash_bank_type' => 'bank',
            'cash_bank_account_id' => $bank->id,
            'counterpart_account_id' => $counterpart->id,
            'amount' => 500000,
            'status' => 'posted',
            'created_by' => $this->user->id,
        ]);

        $response = $this->get(route('cash-flow-reports.index', [
            'cash_bank_type' => 'bank',
            'cash_bank_account_id' => $bank->id,
        ]));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->where('summary.opening_balance', 2000000)
            ->where('summary.cash_in', 500000)
            ->where('summary.cash_out', 0)
            ->where('summary.closing_balance', 2500000)
        );
    }

    private function createCashAccount(float $opening): CashAccount
    {
        $coa = Account::factory()->create([
            'type' => 'asset',
            'category' => 'current_asset',
            'is_header' => false,
            'is_active' => true,
        ]);

        return CashAccount::query()->create([
            'code' => 'CASH-TEST',
            'name' => 'Kas Test',
            'account_id' => $coa->id,
            'opening_balance' => $opening,
            'is_active' => true,
            'created_by' => $this->user->id,
        ]);
    }

    private function createBankAccount(float $opening): BankAccount
    {
        $coa = Account::factory()->create([
            'type' => 'asset',
            'category' => 'current_asset',
            'is_header' => false,
            'is_active' => true,
        ]);

        return BankAccount::query()->create([
            'code' => 'BANK-TEST',
            'name' => 'Bank Test',
            'bank_name' => 'BCA',
            'account_number' => '9876543210',
            'account_holder' => 'PT AkuBook',
            'account_id' => $coa->id,
            'opening_balance' => $opening,
            'is_active' => true,
            'created_by' => $this->user->id,
        ]);
    }
}

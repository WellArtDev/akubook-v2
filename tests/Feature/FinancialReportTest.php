<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\FiscalPeriod;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FinancialReportTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected FiscalPeriod $period;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();

        $this->user = User::factory()->create();
        $this->actingAs($this->user);

        $this->period = FiscalPeriod::create([
            'name' => 'May 2026',
            'start_date' => '2026-05-01',
            'end_date' => '2026-05-31',
            'status' => 'open',
            'is_current' => true,
        ]);
    }

    public function test_financial_report_page_can_be_opened(): void
    {
        $this->get(route('financial-reports.index'))->assertOk();
    }

    public function test_financial_report_calculates_trial_balance_profit_loss_and_balance_sheet(): void
    {
        $cash = $this->account('1-1000', 'Cash', 'asset', 'current_asset');
        $ap = $this->account('2-1000', 'Accounts Payable', 'liability', 'current_liability');
        $capital = $this->account('3-1000', 'Capital', 'equity', 'equity');
        $revenue = $this->account('4-1000', 'Sales Revenue', 'revenue', 'operating_revenue');
        $expense = $this->account('5-1000', 'Office Expense', 'expense', 'operating_expense');

        $entry = JournalEntry::create([
            'journal_number' => 'JV-TEST-001',
            'journal_date' => '2026-05-15',
            'fiscal_period_id' => $this->period->id,
            'type' => 'manual',
            'description' => 'Financial report test',
            'total_debit' => 150000,
            'total_credit' => 150000,
            'status' => 'posted',
            'posted_at' => now(),
            'posted_by' => $this->user->id,
            'created_by' => $this->user->id,
        ]);

        JournalEntryLine::create(['journal_entry_id' => $entry->id, 'account_id' => $cash->id, 'description' => 'Cash', 'debit' => 100000, 'credit' => 0]);
        JournalEntryLine::create(['journal_entry_id' => $entry->id, 'account_id' => $expense->id, 'description' => 'Expense', 'debit' => 50000, 'credit' => 0]);
        JournalEntryLine::create(['journal_entry_id' => $entry->id, 'account_id' => $revenue->id, 'description' => 'Revenue', 'debit' => 0, 'credit' => 120000]);
        JournalEntryLine::create(['journal_entry_id' => $entry->id, 'account_id' => $ap->id, 'description' => 'AP', 'debit' => 0, 'credit' => 20000]);
        JournalEntryLine::create(['journal_entry_id' => $entry->id, 'account_id' => $capital->id, 'description' => 'Capital', 'debit' => 0, 'credit' => 10000]);

        $this->get(route('financial-reports.index', ['date_from' => '2026-05-01', 'date_to' => '2026-05-31']))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('FinancialReports/Index')
                ->where('trial_balance.summary.total_debit', 150000)
                ->where('trial_balance.summary.total_credit', 150000)
                ->where('profit_loss.revenue', 120000)
                ->where('profit_loss.expense', 50000)
                ->where('profit_loss.net_profit', 70000)
                ->where('balance_sheet.assets', 100000)
                ->where('balance_sheet.liabilities', 20000)
                ->where('balance_sheet.equity', 10000)
                ->has('trial_balance.rows', 5)
            );
    }

    public function test_financial_report_filters_by_period(): void
    {
        $cash = $this->account('1-1000', 'Cash', 'asset', 'current_asset');
        $revenue = $this->account('4-1000', 'Revenue', 'revenue', 'operating_revenue');

        $entry = JournalEntry::create([
            'journal_number' => 'JV-OUT-001',
            'journal_date' => '2026-04-15',
            'fiscal_period_id' => $this->period->id,
            'type' => 'manual',
            'description' => 'Outside period',
            'total_debit' => 100000,
            'total_credit' => 100000,
            'status' => 'posted',
            'created_by' => $this->user->id,
        ]);

        JournalEntryLine::create(['journal_entry_id' => $entry->id, 'account_id' => $cash->id, 'description' => 'Cash', 'debit' => 100000, 'credit' => 0]);
        JournalEntryLine::create(['journal_entry_id' => $entry->id, 'account_id' => $revenue->id, 'description' => 'Revenue', 'debit' => 0, 'credit' => 100000]);

        $this->get(route('financial-reports.index', ['date_from' => '2026-05-01', 'date_to' => '2026-05-31']))
            ->assertInertia(fn ($page) => $page
                ->where('trial_balance.summary.total_debit', 0)
                ->where('trial_balance.summary.total_credit', 0)
                ->has('trial_balance.rows', 0)
            );
    }

    private function account(string $code, string $name, string $type, string $category): Account
    {
        return Account::factory()->create([
            'code' => $code,
            'name' => $name,
            'type' => $type,
            'category' => $category,
            'is_header' => false,
            'is_active' => true,
        ]);
    }
}

<?php

namespace Tests\Unit\Services;

use App\Models\Account;
use App\Models\FiscalPeriod;
use App\Models\JournalEntry;
use App\Models\User;
use App\Services\PostMigrationReconciliationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PostMigrationReconciliationServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_success_path_has_no_critical_issue(): void
    {
        $this->seedBalancedJournal();

        $result = (new PostMigrationReconciliationService)->run();

        $this->assertSame(0, $result['severity_counts']['critical']);
        $this->assertTrue($result['export_ready']);
        $this->assertSame(1, $result['summary']['journal_entries']);
    }

    public function test_unbalanced_journal_detected_as_critical(): void
    {
        $this->seedBalancedJournal(false);

        $result = (new PostMigrationReconciliationService)->run();

        $this->assertGreaterThan(0, $result['severity_counts']['critical']);
        $this->assertContains('journal_entries', array_column($result['issues'], 'module'));
    }

    public function test_header_account_usage_detected_as_warning(): void
    {
        $journal = $this->seedBalancedJournal();
        $header = Account::create([
            'code' => '1-0000',
            'name' => 'Asset Header',
            'type' => 'asset',
            'category' => 'current_asset',
            'is_header' => true,
            'is_active' => true,
            'balance' => 0,
        ]);

        $journal->lines()->create([
            'account_id' => $header->id,
            'description' => 'Header misuse',
            'debit' => 0,
            'credit' => 0,
        ]);

        $result = (new PostMigrationReconciliationService)->run();

        $this->assertGreaterThan(0, $result['severity_counts']['warning']);
        $this->assertSame(1, $result['failed_checks']);
        $this->assertContains('header-account-usage', array_column($result['issues'], 'identifier'));
    }

    public function test_draft_journal_lines_do_not_affect_trial_balance(): void
    {
        $journal = $this->seedBalancedJournal();

        $draft = JournalEntry::create([
            'journal_number' => 'REC-DRAFT-001',
            'journal_date' => '2026-01-11',
            'fiscal_period_id' => $journal->fiscal_period_id,
            'type' => 'manual',
            'description' => 'Draft journal',
            'total_debit' => 10000,
            'total_credit' => 5000,
            'status' => 'draft',
            'created_by' => $journal->created_by,
        ]);

        $debitAccountId = $journal->lines()->where('debit', '>', 0)->value('account_id');
        $creditAccountId = $journal->lines()->where('credit', '>', 0)->value('account_id');

        $draft->lines()->create([
            'account_id' => $debitAccountId,
            'description' => 'Draft debit',
            'debit' => 10000,
            'credit' => 0,
        ]);
        $draft->lines()->create([
            'account_id' => $creditAccountId,
            'description' => 'Draft credit',
            'debit' => 0,
            'credit' => 5000,
        ]);

        $result = (new PostMigrationReconciliationService)->run();

        $this->assertSame(0, $result['severity_counts']['critical']);
        $this->assertNotContains('trial_balance', array_column($result['issues'], 'module'));
    }

    private function seedBalancedJournal(bool $balanced = true): JournalEntry
    {
        $user = User::factory()->create();
        $period = FiscalPeriod::create([
            'name' => '2026-01',
            'start_date' => '2026-01-01',
            'end_date' => '2026-12-31',
            'status' => 'open',
            'is_current' => true,
        ]);

        $cash = Account::create([
            'code' => '1-1000',
            'name' => 'Kas',
            'type' => 'asset',
            'category' => 'current_asset',
            'is_header' => false,
            'is_active' => true,
            'balance' => 0,
        ]);

        $revenue = Account::create([
            'code' => '4-1000',
            'name' => 'Pendapatan',
            'type' => 'revenue',
            'category' => 'operating_revenue',
            'is_header' => false,
            'is_active' => true,
            'balance' => 0,
        ]);

        $journal = JournalEntry::create([
            'journal_number' => 'REC-001',
            'journal_date' => '2026-01-10',
            'fiscal_period_id' => $period->id,
            'type' => 'manual',
            'description' => 'Reconciliation fixture',
            'total_debit' => 10000,
            'total_credit' => $balanced ? 10000 : 5000,
            'status' => 'posted',
            'posted_at' => now(),
            'created_by' => $user->id,
        ]);

        $journal->lines()->create([
            'account_id' => $cash->id,
            'description' => 'Debit',
            'debit' => 10000,
            'credit' => 0,
        ]);
        $journal->lines()->create([
            'account_id' => $revenue->id,
            'description' => 'Credit',
            'debit' => 0,
            'credit' => $balanced ? 10000 : 5000,
        ]);

        return $journal;
    }
}

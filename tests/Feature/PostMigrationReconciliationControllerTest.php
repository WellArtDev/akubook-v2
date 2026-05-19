<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\FiscalPeriod;
use App\Models\JournalEntry;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PostMigrationReconciliationControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_run_endpoint_returns_export_ready_summary(): void
    {
        $user = User::factory()->create();
        $this->seedBalancedJournal($user);

        $response = $this->actingAs($user)->postJson(route('migration.post-migration-reconciliation.run'));

        $response->assertOk()
            ->assertJsonPath('export_ready', true)
            ->assertJsonStructure([
                'timestamp',
                'duration_ms',
                'summary',
                'issues',
                'severity_counts',
                'total_checks',
                'passed_checks',
                'failed_checks',
            ]);
    }

    private function seedBalancedJournal(User $user): void
    {
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
            'total_credit' => 10000,
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
            'credit' => 10000,
        ]);
    }
}

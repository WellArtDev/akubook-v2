<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\AssetDepreciation;
use App\Models\FiscalPeriod;
use App\Models\FixedAsset;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AssetDepreciationJournalTest extends TestCase
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

    public function test_index_page_can_be_opened(): void
    {
        $response = $this->get(route('asset-depreciation-journals.index', ['period' => '2026-05']));

        $response->assertOk();
    }

    public function test_run_posts_journal_and_marks_depreciation_rows(): void
    {
        $expense = Account::factory()->create(['type' => 'expense', 'is_header' => false, 'is_active' => true]);
        $accum = Account::factory()->create(['type' => 'asset', 'is_header' => false, 'is_active' => true]);
        Account::factory()->create(['type' => 'asset', 'is_header' => false, 'is_active' => true]);

        $asset = FixedAsset::factory()->create([
            'depreciation_expense_account_id' => $expense->id,
            'accumulated_depreciation_account_id' => $accum->id,
            'asset_account_id' => $accum->id,
        ]);

        AssetDepreciation::create([
            'fixed_asset_id' => $asset->id,
            'period' => '2026-05',
            'monthly_depreciation' => 100000,
            'accumulated_depreciation' => 100000,
            'book_value_end' => 900000,
            'created_by' => $this->user->id,
        ]);

        FiscalPeriod::create([
            'name' => 'May 2026',
            'start_date' => '2026-05-01',
            'end_date' => '2026-05-31',
            'status' => 'open',
            'is_current' => true,
        ]);

        $response = $this->post(route('asset-depreciation-journals.run'), ['period' => '2026-05']);

        $response->assertRedirect(route('asset-depreciation-journals.index', ['period' => '2026-05']));
        $this->assertDatabaseCount('journal_entries', 1);
        $this->assertDatabaseHas('asset_depreciations', ['period' => '2026-05']);

        $journal = JournalEntry::first();
        $this->assertDatabaseHas('asset_depreciations', [
            'period' => '2026-05',
            'journal_entry_id' => $journal->id,
        ]);

        $this->assertDatabaseHas('journal_entry_lines', [
            'journal_entry_id' => $journal->id,
            'account_id' => $expense->id,
            'debit' => 100000,
            'credit' => 0,
        ]);

        $this->assertDatabaseHas('journal_entry_lines', [
            'journal_entry_id' => $journal->id,
            'account_id' => $accum->id,
            'debit' => 0,
            'credit' => 100000,
        ]);
    }

    public function test_rerun_period_is_idempotent_no_duplicate_journal(): void
    {
        $expense = Account::factory()->create(['type' => 'expense', 'is_header' => false, 'is_active' => true]);
        $accum = Account::factory()->create(['type' => 'asset', 'is_header' => false, 'is_active' => true]);

        $asset = FixedAsset::factory()->create([
            'depreciation_expense_account_id' => $expense->id,
            'accumulated_depreciation_account_id' => $accum->id,
            'asset_account_id' => $accum->id,
        ]);

        AssetDepreciation::create([
            'fixed_asset_id' => $asset->id,
            'period' => '2026-06',
            'monthly_depreciation' => 50000,
            'accumulated_depreciation' => 150000,
            'book_value_end' => 850000,
            'created_by' => $this->user->id,
        ]);

        FiscalPeriod::create([
            'name' => 'June 2026',
            'start_date' => '2026-06-01',
            'end_date' => '2026-06-30',
            'status' => 'open',
            'is_current' => true,
        ]);

        $this->post(route('asset-depreciation-journals.run'), ['period' => '2026-06']);
        $this->post(route('asset-depreciation-journals.run'), ['period' => '2026-06']);

        $this->assertDatabaseCount('journal_entries', 1);
        $this->assertDatabaseCount('journal_entry_lines', 2);
    }
}

<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\AssetDepreciation;
use App\Models\AssetDisposal;
use App\Models\FiscalPeriod;
use App\Models\FixedAsset;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AssetDisposalTest extends TestCase
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

    public function test_user_can_create_asset_disposal(): void
    {
        $assetAccount = Account::factory()->create(['type' => 'asset', 'is_header' => false, 'is_active' => true]);
        $accDepAccount = Account::factory()->create(['type' => 'asset', 'is_header' => false, 'is_active' => true]);
        $depExpAccount = Account::factory()->create(['type' => 'expense', 'is_header' => false, 'is_active' => true]);
        $gainLossAccount = Account::factory()->create(['type' => 'revenue', 'is_header' => false, 'is_active' => true]);

        $asset = FixedAsset::factory()->create([
            'status' => 'active',
            'acquisition_cost' => 1000000,
            'asset_account_id' => $assetAccount->id,
            'accumulated_depreciation_account_id' => $accDepAccount->id,
            'depreciation_expense_account_id' => $depExpAccount->id,
        ]);

        AssetDepreciation::create([
            'fixed_asset_id' => $asset->id,
            'period' => '2026-06',
            'monthly_depreciation' => 100000,
            'accumulated_depreciation' => 300000,
            'book_value_end' => 700000,
            'created_by' => $this->user->id,
        ]);

        $response = $this->post(route('asset-disposals.store'), [
            'fixed_asset_id' => $asset->id,
            'disposal_date' => '2026-06-30',
            'proceeds_amount' => 750000,
            'proceeds_account_id' => $assetAccount->id,
            'gain_loss_account_id' => $gainLossAccount->id,
            'notes' => 'Sell old machine',
        ]);

        $response->assertRedirect(route('asset-disposals.index'));

        $this->assertDatabaseHas('asset_disposals', [
            'fixed_asset_id' => $asset->id,
            'book_value' => 700000,
            'status' => 'draft',
        ]);
    }

    public function test_post_disposal_creates_journal_and_marks_asset_disposed(): void
    {
        $assetAccount = Account::factory()->create(['type' => 'asset', 'is_header' => false, 'is_active' => true]);
        $accDepAccount = Account::factory()->create(['type' => 'asset', 'is_header' => false, 'is_active' => true]);
        $depExpAccount = Account::factory()->create(['type' => 'expense', 'is_header' => false, 'is_active' => true]);
        $proceedsAccount = Account::factory()->create(['type' => 'asset', 'is_header' => false, 'is_active' => true]);
        $gainLossAccount = Account::factory()->create(['type' => 'revenue', 'is_header' => false, 'is_active' => true]);

        $asset = FixedAsset::factory()->create([
            'status' => 'active',
            'asset_account_id' => $assetAccount->id,
            'accumulated_depreciation_account_id' => $accDepAccount->id,
            'depreciation_expense_account_id' => $depExpAccount->id,
            'acquisition_cost' => 1000000,
        ]);

        FiscalPeriod::create([
            'name' => 'June 2026',
            'start_date' => '2026-06-01',
            'end_date' => '2026-06-30',
            'status' => 'open',
            'is_current' => true,
        ]);

        $disposal = AssetDisposal::create([
            'disposal_number' => AssetDisposal::generateNumber(),
            'disposal_date' => '2026-06-30',
            'fixed_asset_id' => $asset->id,
            'acquisition_cost' => 1000000,
            'accumulated_depreciation' => 300000,
            'book_value' => 700000,
            'proceeds_amount' => 800000,
            'proceeds_account_id' => $proceedsAccount->id,
            'gain_loss_account_id' => $gainLossAccount->id,
            'status' => 'draft',
            'created_by' => $this->user->id,
        ]);

        $response = $this->post(route('asset-disposals.post', $disposal));
        $response->assertRedirect(route('asset-disposals.show', $disposal));

        $this->assertDatabaseHas('asset_disposals', [
            'id' => $disposal->id,
            'status' => 'posted',
        ]);
        $this->assertDatabaseHas('fixed_assets', [
            'id' => $asset->id,
            'status' => 'disposed',
        ]);
        $this->assertDatabaseHas('journal_entries', [
            'reference_type' => 'asset_disposal',
            'reference_id' => $disposal->id,
            'status' => 'posted',
        ]);
    }

    public function test_cannot_repost_posted_disposal(): void
    {
        $assetAccount = Account::factory()->create(['type' => 'asset', 'is_header' => false, 'is_active' => true]);
        $accDepAccount = Account::factory()->create(['type' => 'asset', 'is_header' => false, 'is_active' => true]);
        $depExpAccount = Account::factory()->create(['type' => 'expense', 'is_header' => false, 'is_active' => true]);
        $gainLossAccount = Account::factory()->create(['type' => 'revenue', 'is_header' => false, 'is_active' => true]);

        $asset = FixedAsset::factory()->create([
            'status' => 'disposed',
            'asset_account_id' => $assetAccount->id,
            'accumulated_depreciation_account_id' => $accDepAccount->id,
            'depreciation_expense_account_id' => $depExpAccount->id,
        ]);

        $disposal = AssetDisposal::create([
            'disposal_number' => AssetDisposal::generateNumber(),
            'disposal_date' => '2026-06-30',
            'fixed_asset_id' => $asset->id,
            'acquisition_cost' => 1000000,
            'accumulated_depreciation' => 300000,
            'book_value' => 700000,
            'proceeds_amount' => 0,
            'proceeds_account_id' => null,
            'gain_loss_account_id' => $gainLossAccount->id,
            'status' => 'posted',
            'created_by' => $this->user->id,
            'posted_by' => $this->user->id,
            'posted_at' => now(),
        ]);

        $response = $this->post(route('asset-disposals.post', $disposal));

        $response->assertSessionHasErrors('status');
    }
}

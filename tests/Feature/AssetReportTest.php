<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\AssetDepreciation;
use App\Models\AssetDisposal;
use App\Models\FixedAsset;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AssetReportTest extends TestCase
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

    public function test_asset_report_page_can_be_opened(): void
    {
        $response = $this->get(route('asset-reports.index'));

        $response->assertOk();
    }

    public function test_asset_report_shows_register_and_summary_values(): void
    {
        $assetAccount = Account::factory()->create(['is_header' => false, 'type' => 'asset']);
        $accumulatedAccount = Account::factory()->create(['is_header' => false, 'type' => 'asset']);
        $expenseAccount = Account::factory()->create(['is_header' => false, 'type' => 'expense']);
        $gainLossAccount = Account::factory()->create(['is_header' => false, 'type' => 'expense']);

        $asset = FixedAsset::factory()->create([
            'asset_code' => 'FA-2026-0001',
            'acquisition_cost' => 1200000,
            'status' => 'active',
            'asset_account_id' => $assetAccount->id,
            'accumulated_depreciation_account_id' => $accumulatedAccount->id,
            'depreciation_expense_account_id' => $expenseAccount->id,
        ]);

        AssetDepreciation::factory()->create([
            'fixed_asset_id' => $asset->id,
            'period' => '2026-05',
            'monthly_depreciation' => 100000,
            'accumulated_depreciation' => 300000,
            'book_value_end' => 900000,
            'created_by' => $this->user->id,
        ]);

        AssetDisposal::factory()->create([
            'fixed_asset_id' => $asset->id,
            'book_value' => 900000,
            'proceeds_amount' => 950000,
            'gain_loss_account_id' => $gainLossAccount->id,
            'created_by' => $this->user->id,
            'status' => 'posted',
        ]);

        $response = $this->get(route('asset-reports.index'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('AssetReports/Index')
            ->where('summary.total_acquisition_cost', 1200000)
            ->where('summary.total_accumulated_depreciation', 300000)
            ->where('summary.total_book_value', 900000)
            ->where('summary.total_disposal_proceeds', 950000)
            ->where('assets.0.asset_code', 'FA-2026-0001')
            ->where('assets.0.book_value', 900000)
        );
    }

    public function test_asset_report_can_filter_status(): void
    {
        $assetAccount = Account::factory()->create(['is_header' => false, 'type' => 'asset']);
        $accumulatedAccount = Account::factory()->create(['is_header' => false, 'type' => 'asset']);
        $expenseAccount = Account::factory()->create(['is_header' => false, 'type' => 'expense']);

        FixedAsset::factory()->create([
            'asset_code' => 'FA-ACTIVE',
            'status' => 'active',
            'asset_account_id' => $assetAccount->id,
            'accumulated_depreciation_account_id' => $accumulatedAccount->id,
            'depreciation_expense_account_id' => $expenseAccount->id,
        ]);

        FixedAsset::factory()->create([
            'asset_code' => 'FA-DISPOSED',
            'status' => 'disposed',
            'asset_account_id' => $assetAccount->id,
            'accumulated_depreciation_account_id' => $accumulatedAccount->id,
            'depreciation_expense_account_id' => $expenseAccount->id,
        ]);

        $response = $this->get(route('asset-reports.index', ['status' => 'disposed']));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('AssetReports/Index')
            ->where('assets.0.asset_code', 'FA-DISPOSED')
        );
    }
}

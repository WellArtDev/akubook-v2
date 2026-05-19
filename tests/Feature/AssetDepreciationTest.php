<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\AssetDepreciation;
use App\Models\FixedAsset;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AssetDepreciationTest extends TestCase
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

    public function test_index_page_can_be_opened(): void
    {
        $this->get(route('asset-depreciations.index'))->assertOk();
    }

    public function test_run_depreciation_creates_period_rows(): void
    {
        $accounts = Account::factory()->count(3)->create(['is_active' => true, 'is_header' => false]);

        $asset = FixedAsset::query()->create([
            'asset_code' => 'FA-2026-0001',
            'name' => 'Mesin A',
            'acquisition_date' => now()->subMonths(1)->startOfMonth()->toDateString(),
            'acquisition_cost' => 1200000,
            'useful_life_months' => 12,
            'residual_value' => 0,
            'status' => 'active',
            'asset_account_id' => $accounts[0]->id,
            'accumulated_depreciation_account_id' => $accounts[1]->id,
            'depreciation_expense_account_id' => $accounts[2]->id,
            'created_by' => $this->user->id,
        ]);

        $period = now()->format('Y-m');

        $this->get(route('asset-depreciations.index', ['period' => $period, 'run' => 1]))->assertOk();

        $this->assertDatabaseHas('asset_depreciations', [
            'fixed_asset_id' => $asset->id,
            'period' => $period,
            'monthly_depreciation' => 100000,
        ]);
    }

    public function test_rerun_same_period_updates_not_duplicates(): void
    {
        $accounts = Account::factory()->count(3)->create(['is_active' => true, 'is_header' => false]);

        $asset = FixedAsset::query()->create([
            'asset_code' => 'FA-2026-0002',
            'name' => 'Mesin B',
            'acquisition_date' => now()->subMonths(2)->startOfMonth()->toDateString(),
            'acquisition_cost' => 2400000,
            'useful_life_months' => 24,
            'residual_value' => 0,
            'status' => 'active',
            'asset_account_id' => $accounts[0]->id,
            'accumulated_depreciation_account_id' => $accounts[1]->id,
            'depreciation_expense_account_id' => $accounts[2]->id,
            'created_by' => $this->user->id,
        ]);

        $period = now()->format('Y-m');

        $this->get(route('asset-depreciations.index', ['period' => $period, 'run' => 1]))->assertOk();
        $this->get(route('asset-depreciations.index', ['period' => $period, 'run' => 1]))->assertOk();

        $this->assertEquals(1, AssetDepreciation::query()->where('fixed_asset_id', $asset->id)->where('period', $period)->count());
    }
}

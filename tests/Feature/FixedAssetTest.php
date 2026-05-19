<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\FixedAsset;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FixedAssetTest extends TestCase
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
        $this->get(route('fixed-assets.index'))->assertOk();
    }

    public function test_user_can_create_fixed_asset(): void
    {
        $assetAccount = Account::factory()->create(['is_active' => true, 'is_header' => false]);
        $accDepAccount = Account::factory()->create(['is_active' => true, 'is_header' => false]);
        $expAccount = Account::factory()->create(['is_active' => true, 'is_header' => false]);

        $response = $this->post(route('fixed-assets.store'), [
            'asset_code' => 'FA-TEST-001',
            'name' => 'Laptop Operasional',
            'category' => 'electronics',
            'acquisition_date' => now()->toDateString(),
            'acquisition_cost' => 12000000,
            'useful_life_months' => 48,
            'residual_value' => 1000000,
            'status' => 'active',
            'asset_account_id' => $assetAccount->id,
            'accumulated_depreciation_account_id' => $accDepAccount->id,
            'depreciation_expense_account_id' => $expAccount->id,
            'notes' => 'Registered from procurement',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('fixed_assets', [
            'asset_code' => 'FA-TEST-001',
            'name' => 'Laptop Operasional',
            'status' => 'active',
            'created_by' => $this->user->id,
        ]);
    }

    public function test_asset_code_must_be_unique(): void
    {
        $assetAccount = Account::factory()->create(['is_active' => true, 'is_header' => false]);
        $accDepAccount = Account::factory()->create(['is_active' => true, 'is_header' => false]);
        $expAccount = Account::factory()->create(['is_active' => true, 'is_header' => false]);

        FixedAsset::query()->create([
            'asset_code' => 'FA-DUP-001',
            'name' => 'Asset A',
            'acquisition_date' => now()->toDateString(),
            'acquisition_cost' => 1000000,
            'useful_life_months' => 12,
            'residual_value' => 0,
            'status' => 'active',
            'asset_account_id' => $assetAccount->id,
            'accumulated_depreciation_account_id' => $accDepAccount->id,
            'depreciation_expense_account_id' => $expAccount->id,
            'created_by' => $this->user->id,
        ]);

        $response = $this->post(route('fixed-assets.store'), [
            'asset_code' => 'FA-DUP-001',
            'name' => 'Asset B',
            'acquisition_date' => now()->toDateString(),
            'acquisition_cost' => 2000000,
            'useful_life_months' => 24,
            'residual_value' => 0,
            'status' => 'active',
            'asset_account_id' => $assetAccount->id,
            'accumulated_depreciation_account_id' => $accDepAccount->id,
            'depreciation_expense_account_id' => $expAccount->id,
        ]);

        $response->assertSessionHasErrors(['asset_code']);
    }

    public function test_user_can_update_asset_status(): void
    {
        $asset = FixedAsset::factory()->create(['created_by' => $this->user->id]);

        $response = $this->put(route('fixed-assets.update', $asset), [
            'asset_code' => $asset->asset_code,
            'name' => $asset->name,
            'category' => $asset->category,
            'acquisition_date' => $asset->acquisition_date->toDateString(),
            'acquisition_cost' => $asset->acquisition_cost,
            'useful_life_months' => $asset->useful_life_months,
            'residual_value' => $asset->residual_value,
            'status' => 'inactive',
            'asset_account_id' => $asset->asset_account_id,
            'accumulated_depreciation_account_id' => $asset->accumulated_depreciation_account_id,
            'depreciation_expense_account_id' => $asset->depreciation_expense_account_id,
            'notes' => $asset->notes,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('fixed_assets', [
            'id' => $asset->id,
            'status' => 'inactive',
            'updated_by' => $this->user->id,
        ]);
    }
}

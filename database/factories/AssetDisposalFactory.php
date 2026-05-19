<?php

namespace Database\Factories;

use App\Models\Account;
use App\Models\AssetDisposal;
use App\Models\FixedAsset;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class AssetDisposalFactory extends Factory
{
    protected $model = AssetDisposal::class;

    public function definition(): array
    {
        return [
            'disposal_number' => 'AD-' . now()->year . '-' . $this->faker->unique()->numberBetween(1000, 9999),
            'disposal_date' => now()->toDateString(),
            'fixed_asset_id' => FixedAsset::factory(),
            'acquisition_cost' => 10000000,
            'accumulated_depreciation' => 2000000,
            'book_value' => 8000000,
            'proceeds_amount' => 0,
            'proceeds_account_id' => null,
            'gain_loss_account_id' => Account::factory(),
            'status' => 'draft',
            'journal_entry_id' => null,
            'notes' => null,
            'created_by' => User::factory(),
        ];
    }
}

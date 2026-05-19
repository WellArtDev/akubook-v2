<?php

namespace Database\Factories;

use App\Models\Account;
use App\Models\FixedAsset;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class FixedAssetFactory extends Factory
{
    protected $model = FixedAsset::class;

    public function definition(): array
    {
        $year = now()->year;

        return [
            'asset_code' => "FA-{$year}-" . str_pad((string) fake()->numberBetween(1, 9999), 4, '0', STR_PAD_LEFT),
            'name' => fake()->words(3, true),
            'category' => 'equipment',
            'acquisition_date' => now()->subMonths(2)->toDateString(),
            'acquisition_cost' => 10000000,
            'useful_life_months' => 60,
            'residual_value' => 500000,
            'status' => 'active',
            'asset_account_id' => Account::factory(),
            'accumulated_depreciation_account_id' => Account::factory(),
            'depreciation_expense_account_id' => Account::factory(),
            'created_by' => User::factory(),
        ];
    }
}

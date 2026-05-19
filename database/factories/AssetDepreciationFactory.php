<?php

namespace Database\Factories;

use App\Models\AssetDepreciation;
use App\Models\FixedAsset;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class AssetDepreciationFactory extends Factory
{
    protected $model = AssetDepreciation::class;

    public function definition(): array
    {
        return [
            'fixed_asset_id' => FixedAsset::factory(),
            'period' => now()->format('Y-m'),
            'monthly_depreciation' => 100000,
            'accumulated_depreciation' => 100000,
            'book_value_end' => 900000,
            'created_by' => User::factory(),
        ];
    }
}

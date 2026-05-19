<?php

namespace Database\Factories;

use App\Models\TaxCalculation;
use App\Models\TaxConfiguration;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class TaxCalculationFactory extends Factory
{
    protected $model = TaxCalculation::class;

    public function definition(): array
    {
        return [
            'tax_configuration_id' => TaxConfiguration::factory(),
            'tax_type' => 'ppn_out',
            'taxable_amount' => 100000,
            'is_inclusive' => false,
            'rate' => 11,
            'dpp' => 100000,
            'tax_amount' => 11000,
            'grand_total' => 111000,
            'created_by' => User::factory(),
        ];
    }
}

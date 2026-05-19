<?php

namespace Database\Factories;

use App\Models\Account;
use App\Models\TaxConfiguration;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class TaxConfigurationFactory extends Factory
{
    protected $model = TaxConfiguration::class;

    public function definition(): array
    {
        $type = $this->faker->randomElement(['ppn_out', 'ppn_in', 'withholding']);

        return [
            'code' => strtoupper($type).'-'.$this->faker->unique()->numerify('###'),
            'name' => strtoupper(str_replace('_', ' ', $type)).' '.$this->faker->numerify('##'),
            'tax_type' => $type,
            'rate' => $type === 'withholding' ? 2.0000 : 11.0000,
            'account_id' => Account::factory(),
            'is_default' => false,
            'is_active' => true,
            'description' => null,
            'created_by' => User::factory(),
        ];
    }
}

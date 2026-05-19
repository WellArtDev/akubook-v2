<?php

namespace Database\Factories;

use App\Models\Account;
use App\Models\SalaryComponent;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class SalaryComponentFactory extends Factory
{
    protected $model = SalaryComponent::class;

    public function definition(): array
    {
        $type = $this->faker->randomElement(SalaryComponent::COMPONENT_TYPES);
        $method = $this->faker->randomElement(SalaryComponent::CALCULATION_METHODS);

        return [
            'code' => 'SC-' . $this->faker->unique()->numerify('####'),
            'name' => ucfirst($type) . ' ' . $this->faker->words(2, true),
            'component_type' => $type,
            'calculation_method' => $method,
            'default_amount' => $method === 'fixed' ? $this->faker->numberBetween(100000, 5000000) : 0,
            'default_percentage' => $method === 'percentage' ? $this->faker->randomFloat(2, 0.5, 50) : 0,
            'is_taxable' => $this->faker->boolean(),
            'is_active' => true,
            'account_id' => Account::factory(),
            'description' => $this->faker->optional()->sentence(),
            'created_by' => User::factory(),
        ];
    }
}

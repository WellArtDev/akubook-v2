<?php

namespace Database\Factories;

use App\Models\Account;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Account>
 */
class AccountFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $type = fake()->randomElement(['asset', 'liability', 'equity', 'revenue', 'expense']);
        
        // Determine category based on type
        $categoryMap = [
            'asset' => ['current_asset', 'fixed_asset'],
            'liability' => ['current_liability', 'long_term_liability'],
            'equity' => ['equity'],
            'revenue' => ['operating_revenue', 'other_revenue'],
            'expense' => ['operating_expense', 'other_expense'],
        ];
        
        return [
            'code' => fake()->unique()->numerify('####'),
            'name' => fake()->words(3, true),
            'type' => $type,
            'category' => fake()->randomElement($categoryMap[$type]),
            'level' => fake()->numberBetween(1, 4),
            'is_header' => fake()->boolean(30), // 30% chance of being header
            'description' => fake()->optional()->sentence(),
            'is_active' => true,
            'balance' => 0,
        ];
    }
}

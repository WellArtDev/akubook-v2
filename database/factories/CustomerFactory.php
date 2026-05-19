<?php

namespace Database\Factories;

use App\Models\Customer;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Customer>
 */
class CustomerFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'code' => 'CUST' . fake()->unique()->numberBetween(1, 9999),
            'name' => fake()->company(),
            'category' => fake()->randomElement(['retail', 'wholesale', 'distributor']),
            'tax_id' => fake()->numerify('##.###.###.#-###.###'),
            'tax_type' => fake()->randomElement(['pkp', 'non_pkp']),
            'phone' => fake()->optional()->phoneNumber(),
            'email' => fake()->optional()->companyEmail(),
            'website' => fake()->optional()->url(),
            'credit_limit' => fake()->randomFloat(2, 0, 100000),
            'payment_terms' => fake()->randomElement([0, 7, 14, 30, 45, 60]),
            'outstanding_balance' => 0,
            'is_active' => true,
            'notes' => fake()->optional()->sentence(),
        ];
    }
}

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
            'customer_type' => fake()->randomElement(['individual', 'company']),
            'contact_person' => fake()->name(),
            'email' => fake()->companyEmail(),
            'phone' => fake()->phoneNumber(),
            'address' => fake()->address(),
            'city' => fake()->city(),
            'tax_id' => fake()->numerify('##.###.###.#-###.###'),
            'credit_limit' => fake()->randomFloat(2, 0, 100000),
            'payment_terms_days' => fake()->randomElement([0, 7, 14, 30, 45, 60]),
            'is_active' => true,
            'notes' => fake()->optional()->sentence(),
        ];
    }
}

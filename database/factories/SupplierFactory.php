<?php

namespace Database\Factories;

use App\Models\Supplier;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Supplier>
 */
class SupplierFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->company(),
            'category' => fake()->randomElement(['Raw Material', 'Packaging', 'Service', 'Equipment']),
            'tax_id' => fake()->numerify('##.###.###.#-###.###'),
            'tax_type' => fake()->randomElement(['pkp', 'non_pkp']),
            'payment_terms' => fake()->randomElement(['Net 0', 'Net 7', 'Net 14', 'Net 30', 'Net 45', 'Net 60']),
            'phone' => fake()->phoneNumber(),
            'email' => fake()->companyEmail(),
            'website' => fake()->optional()->url(),
            'notes' => fake()->optional()->sentence(),
            'delivery_rating' => fake()->randomFloat(2, 0, 5),
            'quality_rating' => fake()->randomFloat(2, 0, 5),
            'total_purchase_amount' => fake()->randomFloat(2, 0, 1000000),
            'last_purchase_date' => fake()->optional()->date(),
            'created_by' => User::factory(),
        ];
    }
}

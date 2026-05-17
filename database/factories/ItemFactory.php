<?php

namespace Database\Factories;

use App\Models\Item;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Item>
 */
class ItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'code' => 'ITEM' . fake()->unique()->numberBetween(1, 9999),
            'name' => fake()->words(3, true),
            'description' => fake()->optional()->sentence(),
            'item_type' => fake()->randomElement(['goods', 'service']),
            'unit' => fake()->randomElement(['pcs', 'box', 'kg', 'liter']),
            'purchase_price' => fake()->randomFloat(2, 100, 10000),
            'selling_price' => fake()->randomFloat(2, 150, 15000),
            'is_active' => true,
        ];
    }
}

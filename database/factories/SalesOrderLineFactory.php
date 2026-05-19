<?php

namespace Database\Factories;

use App\Models\SalesOrderLine;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SalesOrderLine>
 */
class SalesOrderLineFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $quantity = fake()->randomFloat(2, 1, 100);
        $unitPrice = fake()->randomFloat(2, 10, 1000);
        $discountAmount = fake()->randomFloat(2, 0, 50);
        $taxAmount = ($quantity * $unitPrice - $discountAmount) * 0.11; // 11% tax
        $lineTotal = ($quantity * $unitPrice) - $discountAmount + $taxAmount;
        
        return [
            'line_number' => fake()->numberBetween(1, 20),
            'description' => fake()->optional()->sentence(),
            'quantity' => $quantity,
            'unit' => 'pcs',
            'unit_price' => $unitPrice,
            'discount_amount' => $discountAmount,
            'tax_amount' => $taxAmount,
            'line_total' => $lineTotal,
            'item_id' => \App\Models\Item::factory(),
        ];
    }
}

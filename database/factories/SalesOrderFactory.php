<?php

namespace Database\Factories;

use App\Models\SalesOrder;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SalesOrder>
 */
class SalesOrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $subtotal = fake()->randomFloat(2, 100, 10000);
        $taxAmount = fake()->randomFloat(2, 10, 1000);
        $discountAmount = fake()->randomFloat(2, 0, 500);
        $grandTotal = $subtotal + $taxAmount - $discountAmount;

        return [
            'so_number' => 'SO-' . fake()->unique()->numberBetween(1000, 9999),
            'so_date' => fake()->dateTimeBetween('-1 month', 'now'),
            'customer_id' => \App\Models\Customer::factory(),
            'sales_person_id' => \App\Models\User::factory(),
            'created_by' => \App\Models\User::factory(),
            'status' => fake()->randomElement(['draft', 'pending_approval', 'approved', 'in_progress', 'completed', 'cancelled']),
            'subtotal' => $subtotal,
            'tax_amount' => $taxAmount,
            'discount_amount' => $discountAmount,
            'grand_total' => $grandTotal,
            'total_amount' => $grandTotal,
            'notes' => fake()->optional()->sentence(),
        ];
    }
}

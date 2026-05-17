<?php

namespace Database\Factories;

use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Models\Branch;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PurchaseOrder>
 */
class PurchaseOrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $year = date('Y');
        $number = fake()->unique()->numberBetween(1, 9999);
        
        return [
            'po_number' => sprintf('PO-%s-%04d', $year, $number),
            'po_date' => fake()->date(),
            'supplier_id' => Supplier::factory(),
            'delivery_address_id' => Branch::factory(),
            'payment_terms' => fake()->randomElement(['Net 30', 'Net 45', 'Net 60', 'COD']),
            'delivery_terms' => fake()->randomElement(['FOB', 'CIF', 'EXW']),
            'expected_delivery_date' => fake()->dateTimeBetween('now', '+30 days')->format('Y-m-d'),
            'notes' => fake()->optional()->sentence(),
            'status' => 'draft',
            'subtotal' => 0,
            'tax_amount' => 0,
            'grand_total' => 0,
            'approval_required' => false,
            'created_by' => User::factory(),
        ];
    }
}

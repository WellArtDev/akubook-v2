<?php

namespace Database\Factories;

use App\Models\Supplier;
use App\Models\SupplierPayment;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class SupplierPaymentFactory extends Factory
{
    protected $model = SupplierPayment::class;

    public function definition(): array
    {
        return [
            'payment_number' => 'SPAY-' . now()->year . '-' . $this->faker->unique()->numerify('####'),
            'payment_date' => now()->toDateString(),
            'supplier_id' => Supplier::factory(),
            'payment_method' => 'bank_transfer',
            'reference_number' => $this->faker->optional()->bothify('REF-####'),
            'total_amount' => 1000000,
            'allocated_amount' => 0,
            'unapplied_amount' => 1000000,
            'status' => 'draft',
            'notes' => null,
            'created_by' => User::factory(),
        ];
    }
}

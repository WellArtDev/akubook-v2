<?php

namespace Database\Factories;

use App\Models\Branch;
use App\Models\StockTransfer;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StockTransfer>
 */
class StockTransferFactory extends Factory
{
    public function definition(): array
    {
        return [
            'transfer_number' => 'TRF-'.now()->format('Y').'-'.$this->faker->unique()->numberBetween(1000, 9999),
            'transfer_date' => now()->toDateString(),
            'from_branch_id' => Branch::factory(),
            'to_branch_id' => Branch::factory(),
            'status' => 'draft',
            'notes' => $this->faker->optional()->sentence(),
            'created_by' => User::factory(),
            'confirmed_by' => null,
            'confirmed_at' => null,
        ];
    }
}

<?php

namespace Database\Factories;

use App\Models\Item;
use App\Models\StockTransaction;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class StockTransactionFactory extends Factory
{
    protected $model = StockTransaction::class;

    public function definition(): array
    {
        return [
            'item_id' => Item::factory(),
            'movement_type' => $this->faker->randomElement(StockTransaction::MOVEMENT_TYPES),
            'quantity_in' => 10,
            'quantity_out' => 0,
            'movement_date' => now()->toDateString(),
            'reference_type' => 'manual',
            'reference_id' => null,
            'notes' => $this->faker->sentence(),
            'created_by' => User::factory(),
        ];
    }
}

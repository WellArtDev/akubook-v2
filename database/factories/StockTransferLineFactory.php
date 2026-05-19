<?php

namespace Database\Factories;

use App\Models\Item;
use App\Models\StockTransfer;
use App\Models\StockTransferLine;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StockTransferLine>
 */
class StockTransferLineFactory extends Factory
{
    public function definition(): array
    {
        return [
            'stock_transfer_id' => StockTransfer::factory(),
            'item_id' => Item::factory(),
            'line_number' => 1,
            'quantity' => $this->faker->randomFloat(3, 1, 50),
            'unit' => 'pcs',
            'notes' => $this->faker->optional()->sentence(),
        ];
    }
}

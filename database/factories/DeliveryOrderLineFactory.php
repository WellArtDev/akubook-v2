<?php

namespace Database\Factories;

use App\Models\DeliveryOrder;
use App\Models\DeliveryOrderLine;
use App\Models\Item;
use App\Models\SalesOrderLine;
use Illuminate\Database\Eloquent\Factories\Factory;

class DeliveryOrderLineFactory extends Factory
{
    protected $model = DeliveryOrderLine::class;

    public function definition(): array
    {
        return [
            'delivery_order_id' => DeliveryOrder::factory(),
            'sales_order_line_id' => SalesOrderLine::factory(),
            'line_number' => 1,
            'item_id' => Item::factory(),
            'description' => fake()->sentence(),
            'so_quantity' => 10,
            'previously_delivered_quantity' => 0,
            'remaining_quantity' => 10,
            'delivery_quantity' => 5,
            'unit' => 'pcs',
            'notes' => fake()->optional()->sentence(),
        ];
    }
}

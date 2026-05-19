<?php

namespace Database\Factories;

use App\Models\DeliveryOrder;
use App\Models\SalesOrder;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class DeliveryOrderFactory extends Factory
{
    protected $model = DeliveryOrder::class;

    public function definition(): array
    {
        return [
            'do_number' => 'DO-' . now()->year . '-' . fake()->unique()->numberBetween(1000, 9999),
            'do_date' => now()->toDateString(),
            'sales_order_id' => SalesOrder::factory(),
            'customer_id' => \App\Models\Customer::factory(),
            'delivery_address_id' => \App\Models\Branch::factory(),
            'delivery_date' => now()->addDays(1)->toDateString(),
            'driver_name' => fake()->name(),
            'vehicle_number' => strtoupper(fake()->bothify('B #### ???')),
            'notes' => fake()->sentence(),
            'status' => 'draft',
            'created_by' => User::factory(),
            'updated_by' => null,
        ];
    }
}

<?php

namespace Database\Factories;

use App\Models\ApprovalWorkflow;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ApprovalWorkflowFactory extends Factory
{
    protected $model = ApprovalWorkflow::class;

    public function definition(): array
    {
        return [
            'workflow_key' => 'WF-'.$this->faker->unique()->bothify('??###'),
            'entity_type' => $this->faker->randomElement(['purchase_order', 'sales_order', 'voucher']),
            'min_amount' => 0,
            'max_amount' => 10000000,
            'required_level' => $this->faker->numberBetween(1, 3),
            'is_active' => true,
            'description' => $this->faker->sentence(),
            'created_by' => User::factory(),
        ];
    }
}

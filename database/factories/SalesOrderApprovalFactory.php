<?php

namespace Database\Factories;

use App\Models\SalesOrder;
use App\Models\SalesOrderApproval;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class SalesOrderApprovalFactory extends Factory
{
    protected $model = SalesOrderApproval::class;

    public function definition(): array
    {
        return [
            'sales_order_id' => SalesOrder::factory(),
            'submitted_by' => User::factory(),
            'submitted_at' => now(),
            'approval_reasons' => [['type' => 'high_value', 'message' => 'Order total exceeds threshold']],
            'status' => 'pending',
            'reviewed_by' => null,
            'reviewed_at' => null,
            'comments' => null,
            'rejection_reason' => null,
        ];
    }
}

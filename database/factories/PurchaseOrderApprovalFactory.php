<?php

namespace Database\Factories;

use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderApproval;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class PurchaseOrderApprovalFactory extends Factory
{
    protected $model = PurchaseOrderApproval::class;

    public function definition(): array
    {
        return [
            'purchase_order_id' => PurchaseOrder::factory(),
            'submitted_by' => User::factory(),
            'submitted_at' => now(),
            'approval_reasons' => [['type' => 'high_value', 'message' => 'Total PO melebihi threshold approval']],
            'status' => 'pending',
            'reviewed_by' => null,
            'reviewed_at' => null,
            'comments' => null,
            'rejection_reason' => null,
        ];
    }
}

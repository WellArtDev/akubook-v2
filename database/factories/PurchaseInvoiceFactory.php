<?php

namespace Database\Factories;

use App\Models\GoodsReceipt;
use App\Models\PurchaseInvoice;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class PurchaseInvoiceFactory extends Factory
{
    protected $model = PurchaseInvoice::class;

    public function definition(): array
    {
        return [
            'invoice_number' => 'PINV-' . now()->year . '-' . $this->faker->unique()->numerify('####'),
            'invoice_date' => now()->toDateString(),
            'due_date' => now()->addDays(30)->toDateString(),
            'goods_receipt_id' => GoodsReceipt::factory(),
            'purchase_order_id' => PurchaseOrder::factory(),
            'supplier_id' => Supplier::factory(),
            'status' => 'draft',
            'subtotal' => 0,
            'tax_amount' => 0,
            'total_amount' => 0,
            'paid_amount' => 0,
            'outstanding_amount' => 0,
            'created_by' => User::factory(),
        ];
    }
}

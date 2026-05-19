<?php

namespace Database\Factories;

use App\Models\PurchaseInvoice;
use App\Models\PurchaseOrder;
use App\Models\PurchaseReturn;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class PurchaseReturnFactory extends Factory
{
    protected $model = PurchaseReturn::class;

    public function definition(): array
    {
        return [
            'return_number' => 'PRET-' . now()->year . '-' . $this->faker->unique()->numberBetween(1000, 9999),
            'return_date' => now()->toDateString(),
            'purchase_invoice_id' => PurchaseInvoice::factory(),
            'purchase_order_id' => PurchaseOrder::factory(),
            'supplier_id' => Supplier::factory(),
            'return_reason' => 'Wrong item',
            'status' => 'draft',
            'subtotal' => 0,
            'tax_amount' => 0,
            'total_amount' => 0,
            'created_by' => User::factory(),
        ];
    }
}

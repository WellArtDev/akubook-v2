<?php

namespace Database\Factories;

use App\Models\PurchaseInvoiceLine;
use App\Models\PurchaseReturn;
use App\Models\PurchaseReturnLine;
use Illuminate\Database\Eloquent\Factories\Factory;

class PurchaseReturnLineFactory extends Factory
{
    protected $model = PurchaseReturnLine::class;

    public function definition(): array
    {
        return [
            'purchase_return_id' => PurchaseReturn::factory(),
            'purchase_invoice_line_id' => PurchaseInvoiceLine::factory(),
            'line_number' => 1,
            'product_id' => null,
            'product_name' => 'Returned Item',
            'return_quantity' => 1,
            'accepted_quantity' => 0,
            'rejected_quantity' => 0,
            'unit' => 'pcs',
            'unit_price' => 1000,
            'tax_percentage' => 11,
            'tax_amount' => 110,
            'line_total' => 1000,
        ];
    }
}

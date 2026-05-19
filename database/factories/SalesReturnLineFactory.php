<?php

namespace Database\Factories;

use App\Models\SalesInvoiceLine;
use App\Models\SalesReturn;
use App\Models\SalesReturnLine;
use Illuminate\Database\Eloquent\Factories\Factory;

class SalesReturnLineFactory extends Factory
{
    protected $model = SalesReturnLine::class;

    public function definition(): array
    {
        $quantity = 1;
        $price = 1000;

        return [
            'sales_return_id' => SalesReturn::factory(),
            'sales_invoice_line_id' => SalesInvoiceLine::factory(),
            'product_id' => null,
            'product_name' => 'Returned Product',
            'return_quantity' => $quantity,
            'accepted_quantity' => 0,
            'rejected_quantity' => 0,
            'unit_price' => $price,
            'tax_amount' => 110,
            'line_total' => $price,
        ];
    }
}

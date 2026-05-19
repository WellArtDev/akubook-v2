<?php

namespace Database\Factories;

use App\Models\Item;
use App\Models\SalesQuotation;
use App\Models\SalesQuotationLine;
use Illuminate\Database\Eloquent\Factories\Factory;

class SalesQuotationLineFactory extends Factory
{
    protected $model = SalesQuotationLine::class;

    public function definition(): array
    {
        $quantity = fake()->randomFloat(3, 1, 10);
        $unitPrice = fake()->randomFloat(2, 1000, 10000);
        $lineTotal = round($quantity * $unitPrice, 2);

        return [
            'sales_quotation_id' => SalesQuotation::factory(),
            'line_number' => 1,
            'item_id' => Item::factory(),
            'description' => fake()->sentence(),
            'quantity' => $quantity,
            'unit' => 'pcs',
            'unit_price' => $unitPrice,
            'discount_percentage' => 0,
            'discount_amount' => 0,
            'tax_percentage' => 11,
            'tax_amount' => round($lineTotal * 0.11, 2),
            'line_total' => $lineTotal,
        ];
    }
}

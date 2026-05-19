<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\SalesQuotation;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class SalesQuotationFactory extends Factory
{
    protected $model = SalesQuotation::class;

    public function definition(): array
    {
        $subtotal = fake()->randomFloat(2, 1000, 100000);
        $taxAmount = round($subtotal * 0.11, 2);

        return [
            'quotation_number' => 'QT-' . fake()->unique()->numberBetween(1000, 9999),
            'quotation_date' => now()->toDateString(),
            'valid_until' => now()->addDays(30)->toDateString(),
            'customer_id' => Customer::factory(),
            'sales_person_id' => User::factory(),
            'payment_terms' => 'Net 30',
            'delivery_terms' => 'FOB',
            'status' => 'draft',
            'discount_type' => 'percentage',
            'discount_value' => 0,
            'subtotal' => $subtotal,
            'discount_amount' => 0,
            'subtotal_after_discount' => $subtotal,
            'tax_amount' => $taxAmount,
            'grand_total' => $subtotal + $taxAmount,
            'created_by' => User::factory(),
        ];
    }
}

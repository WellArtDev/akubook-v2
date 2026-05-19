<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\SalesInvoice;
use App\Models\SalesReturn;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class SalesReturnFactory extends Factory
{
    protected $model = SalesReturn::class;

    public function definition(): array
    {
        return [
            'rma_number' => 'RMA-' . now()->year . '-' . $this->faker->unique()->numerify('####'),
            'return_date' => now()->toDateString(),
            'sales_invoice_id' => SalesInvoice::factory(),
            'customer_id' => Customer::factory(),
            'return_reason' => 'Defective item',
            'status' => 'pending',
            'subtotal' => 0,
            'tax_amount' => 0,
            'total_amount' => 0,
            'created_by' => User::factory(),
        ];
    }
}

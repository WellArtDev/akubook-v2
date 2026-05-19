<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\FakturPajak;
use App\Models\SalesInvoice;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class FakturPajakFactory extends Factory
{
    protected $model = FakturPajak::class;

    public function definition(): array
    {
        return [
            'faktur_number' => 'FP-' . now()->year . '-' . $this->faker->unique()->numerify('####'),
            'faktur_date' => now()->toDateString(),
            'sales_invoice_id' => SalesInvoice::factory(),
            'customer_id' => Customer::factory(),
            'dpp' => 100000,
            'ppn_amount' => 11000,
            'grand_total' => 111000,
            'status' => 'draft',
            'notes' => null,
            'created_by' => User::factory(),
            'issued_by' => null,
            'issued_at' => null,
            'cancelled_by' => null,
            'cancelled_at' => null,
        ];
    }
}

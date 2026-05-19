<?php

namespace Database\Factories;

use App\Models\BankReconciliation;
use App\Models\BankReconciliationLine;
use Illuminate\Database\Eloquent\Factories\Factory;

class BankReconciliationLineFactory extends Factory
{
    protected $model = BankReconciliationLine::class;

    public function definition(): array
    {
        return [
            'bank_reconciliation_id' => BankReconciliation::factory(),
            'line_number' => 1,
            'transaction_date' => now()->toDateString(),
            'description' => $this->faker->sentence(4),
            'debit' => 10000,
            'credit' => 0,
            'reference_number' => $this->faker->numerify('REF-#####'),
            'is_matched' => false,
        ];
    }
}

<?php

namespace Database\Factories;

use App\Models\BankAccount;
use App\Models\BankReconciliation;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class BankReconciliationFactory extends Factory
{
    protected $model = BankReconciliation::class;

    public function definition(): array
    {
        return [
            'reconciliation_number' => 'BRC-'.now()->year.'-'.$this->faker->unique()->numerify('####'),
            'bank_account_id' => BankAccount::factory(),
            'statement_start_date' => now()->startOfMonth()->toDateString(),
            'statement_end_date' => now()->endOfMonth()->toDateString(),
            'reconciliation_date' => now()->toDateString(),
            'statement_opening_balance' => 100000,
            'statement_closing_balance' => 100000,
            'matched_debit_total' => 0,
            'matched_credit_total' => 0,
            'system_balance' => 100000,
            'difference' => 0,
            'status' => 'draft',
            'created_by' => User::factory(),
        ];
    }
}

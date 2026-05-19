<?php

namespace Database\Factories;

use App\Models\Account;
use App\Models\CashAccount;
use App\Models\User;
use App\Models\Voucher;
use Illuminate\Database\Eloquent\Factories\Factory;

class VoucherFactory extends Factory
{
    protected $model = Voucher::class;

    public function definition(): array
    {
        $type = $this->faker->randomElement(['payment', 'receipt']);

        return [
            'voucher_number' => ($type === 'payment' ? 'PV-' : 'RV-').now()->format('Y').'-'.$this->faker->unique()->numberBetween(1000, 9999),
            'voucher_type' => $type,
            'voucher_date' => today(),
            'cash_bank_type' => 'cash',
            'cash_bank_account_id' => CashAccount::factory(),
            'counterpart_account_id' => Account::factory(),
            'amount' => 100000,
            'reference_number' => null,
            'notes' => null,
            'status' => 'draft',
            'created_by' => User::factory(),
        ];
    }
}

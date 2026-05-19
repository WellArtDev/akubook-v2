<?php

namespace Database\Factories;

use App\Models\Account;
use App\Models\CashAccount;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class CashAccountFactory extends Factory
{
    protected $model = CashAccount::class;

    public function definition(): array
    {
        return [
            'code' => 'CASH-' . $this->faker->unique()->numerify('###'),
            'name' => $this->faker->company . ' Cash',
            'account_id' => Account::factory(),
            'opening_balance' => $this->faker->randomFloat(2, 0, 1000000),
            'is_active' => true,
            'description' => $this->faker->optional()->sentence(),
            'created_by' => User::factory(),
            'updated_by' => null,
        ];
    }
}

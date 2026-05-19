<?php

namespace Database\Factories;

use App\Models\Account;
use App\Models\BankAccount;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class BankAccountFactory extends Factory
{
    protected $model = BankAccount::class;

    public function definition(): array
    {
        return [
            'code' => 'BANK-' . $this->faker->unique()->numerify('###'),
            'name' => $this->faker->company . ' Bank Account',
            'bank_name' => $this->faker->randomElement(['BCA', 'Mandiri', 'BRI', 'BNI']),
            'account_number' => $this->faker->unique()->numerify('##########'),
            'account_holder' => $this->faker->company,
            'account_id' => Account::factory(),
            'opening_balance' => $this->faker->randomFloat(2, 0, 5000000),
            'is_active' => true,
            'description' => $this->faker->optional()->sentence(),
            'created_by' => User::factory(),
            'updated_by' => null,
        ];
    }
}

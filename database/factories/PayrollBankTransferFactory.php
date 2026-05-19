<?php

namespace Database\Factories;

use App\Models\PayrollBankTransfer;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class PayrollBankTransferFactory extends Factory
{
    protected $model = PayrollBankTransfer::class;

    public function definition(): array
    {
        return [
            'transfer_number' => 'BT-' . now()->format('Y') . '-' . $this->faker->unique()->numerify('####'),
            'period' => now()->format('Y-m'),
            'status' => 'generated',
            'row_count' => 1,
            'success_count' => 1,
            'failed_count' => 0,
            'total_amount' => 1000000,
            'csv_content' => "employee_code,employee_name,bank_name,bank_account_number,amount\nEMP-0001,John Doe,BCA,1234567890,1000000",
            'metadata' => [],
            'created_by' => User::factory(),
            'generated_at' => now(),
        ];
    }
}

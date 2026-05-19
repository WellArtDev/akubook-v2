<?php

namespace Database\Factories;

use App\Models\Employee;
use App\Models\PayrollBankTransfer;
use App\Models\PayrollBankTransferLine;
use Illuminate\Database\Eloquent\Factories\Factory;

class PayrollBankTransferLineFactory extends Factory
{
    protected $model = PayrollBankTransferLine::class;

    public function definition(): array
    {
        return [
            'payroll_bank_transfer_id' => PayrollBankTransfer::factory(),
            'employee_id' => Employee::factory(),
            'line_number' => 1,
            'employee_code' => 'EMP-0001',
            'employee_name' => $this->faker->name(),
            'bank_name' => 'BCA',
            'bank_account_number' => '1234567890',
            'bank_account_holder' => $this->faker->name(),
            'amount' => 1000000,
            'status' => 'success',
            'failure_reason' => null,
        ];
    }
}

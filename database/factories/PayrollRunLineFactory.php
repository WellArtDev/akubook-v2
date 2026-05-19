<?php

namespace Database\Factories;

use App\Models\Employee;
use App\Models\PayrollRun;
use App\Models\PayrollRunLine;
use Illuminate\Database\Eloquent\Factories\Factory;

class PayrollRunLineFactory extends Factory
{
    protected $model = PayrollRunLine::class;

    public function definition(): array
    {
        return [
            'payroll_run_id' => PayrollRun::factory(),
            'employee_id' => Employee::factory(),
            'earning_total' => 5000000,
            'deduction_total' => 500000,
            'gross_pay' => 5000000,
            'net_pay' => 4500000,
            'component_snapshot' => [
                ['code' => 'SC-BASIC', 'type' => 'earning', 'amount' => 5000000],
                ['code' => 'SC-BPJS', 'type' => 'deduction', 'amount' => 500000],
            ],
            'status' => 'calculated',
        ];
    }
}

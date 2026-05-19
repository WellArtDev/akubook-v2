<?php

namespace Database\Factories;

use App\Models\PayrollRun;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class PayrollRunFactory extends Factory
{
    protected $model = PayrollRun::class;

    public function definition(): array
    {
        return [
            'run_number' => 'PR-' . now()->format('Y') . '-' . $this->faker->unique()->numerify('####'),
            'period' => now()->format('Y-m'),
            'status' => 'calculated',
            'total_earnings' => 10000000,
            'total_deductions' => 500000,
            'total_gross_pay' => 10000000,
            'total_net_pay' => 9500000,
            'created_by' => User::factory(),
        ];
    }
}

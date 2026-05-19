<?php

namespace Database\Factories;

use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class LeaveRequestFactory extends Factory
{
    protected $model = LeaveRequest::class;

    public function definition(): array
    {
        $startDate = $this->faker->dateTimeBetween('now', '+1 month');
        $endDate = (clone $startDate)->modify('+2 days');

        return [
            'employee_id' => Employee::factory(),
            'leave_type' => 'annual',
            'start_date' => $startDate->format('Y-m-d'),
            'end_date' => $endDate->format('Y-m-d'),
            'total_days' => 3,
            'reason' => $this->faker->sentence(),
            'status' => 'pending',
            'created_by' => User::factory(),
        ];
    }
}

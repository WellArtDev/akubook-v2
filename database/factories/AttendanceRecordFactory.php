<?php

namespace Database\Factories;

use App\Models\AttendanceRecord;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class AttendanceRecordFactory extends Factory
{
    protected $model = AttendanceRecord::class;

    public function definition(): array
    {
        $date = now()->toDateString();

        return [
            'employee_id' => Employee::factory(),
            'attendance_date' => $date,
            'check_in_at' => "{$date} 08:00:00",
            'check_out_at' => null,
            'work_hours' => 0,
            'status' => 'incomplete',
            'notes' => $this->faker->sentence(),
            'created_by' => User::factory(),
        ];
    }
}

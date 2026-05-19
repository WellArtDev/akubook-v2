<?php

namespace Database\Factories;

use App\Models\AttendanceRecord;
use App\Models\Employee;
use App\Models\OvertimeRecord;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OvertimeRecord>
 */
class OvertimeRecordFactory extends Factory
{
    protected $model = OvertimeRecord::class;

    public function definition(): array
    {
        $date = now()->toDateString();

        return [
            'employee_id' => Employee::factory(),
            'attendance_record_id' => AttendanceRecord::factory(),
            'overtime_date' => $date,
            'start_at' => "$date 18:00:00",
            'end_at' => "$date 20:00:00",
            'hours' => 2,
            'status' => 'pending',
            'reason' => fake()->sentence(),
            'created_by' => User::factory(),
        ];
    }
}

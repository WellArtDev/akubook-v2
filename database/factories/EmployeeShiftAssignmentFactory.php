<?php

namespace Database\Factories;

use App\Models\Employee;
use App\Models\EmployeeShiftAssignment;
use App\Models\User;
use App\Models\WorkShift;
use Illuminate\Database\Eloquent\Factories\Factory;

class EmployeeShiftAssignmentFactory extends Factory
{
    protected $model = EmployeeShiftAssignment::class;

    public function definition(): array
    {
        return [
            'employee_id' => Employee::factory(),
            'work_shift_id' => WorkShift::factory(),
            'effective_date' => now()->toDateString(),
            'status' => 'active',
            'notes' => null,
            'created_by' => User::factory(),
            'updated_by' => null,
        ];
    }
}

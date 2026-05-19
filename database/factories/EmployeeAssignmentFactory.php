<?php

namespace Database\Factories;

use App\Models\Branch;
use App\Models\Employee;
use App\Models\EmployeeAssignment;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class EmployeeAssignmentFactory extends Factory
{
    protected $model = EmployeeAssignment::class;

    public function definition(): array
    {
        return [
            'employee_id' => Employee::factory(),
            'branch_id' => Branch::factory(),
            'department' => fake()->randomElement(['Finance', 'Sales', 'Warehouse', 'HR']),
            'position' => fake()->jobTitle(),
            'effective_date' => fake()->date(),
            'status' => 'active',
            'notes' => null,
            'created_by' => User::factory(),
        ];
    }
}

<?php

namespace Database\Factories;

use App\Models\Employee;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class EmployeeFactory extends Factory
{
    protected $model = Employee::class;

    public function definition(): array
    {
        return [
            'employee_id' => 'EMP-' . $this->faker->unique()->numerify('####'),
            'full_name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'phone' => $this->faker->phoneNumber(),
            'join_date' => $this->faker->date(),
            'employment_status' => 'active',
            'department' => $this->faker->randomElement(['Finance', 'Sales', 'Warehouse', 'HR']),
            'position' => $this->faker->jobTitle(),
            'notes' => null,
            'created_by' => User::factory(),
        ];
    }
}

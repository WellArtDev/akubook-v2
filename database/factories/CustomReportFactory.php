<?php

namespace Database\Factories;

use App\Models\CustomReport;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class CustomReportFactory extends Factory
{
    protected $model = CustomReport::class;

    public function definition(): array
    {
        return [
            'code' => 'CR-' . strtoupper($this->faker->unique()->bothify('??##')),
            'name' => $this->faker->words(3, true),
            'source_key' => 'employees',
            'selected_columns' => ['employee_id', 'full_name', 'employment_status'],
            'default_filters' => ['status' => 'active'],
            'is_active' => true,
            'description' => $this->faker->sentence(),
            'created_by' => User::factory(),
        ];
    }
}

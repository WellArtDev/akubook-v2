<?php

namespace Database\Factories;

use App\Models\Employee;
use App\Models\EmployeeDocument;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class EmployeeDocumentFactory extends Factory
{
    protected $model = EmployeeDocument::class;

    public function definition(): array
    {
        return [
            'employee_id' => Employee::factory(),
            'document_type' => 'id_card',
            'document_number' => strtoupper($this->faker->bothify('DOC-####-??')),
            'issue_date' => $this->faker->date(),
            'expiry_date' => $this->faker->dateTimeBetween('+1 month', '+3 years')?->format('Y-m-d'),
            'status' => 'active',
            'notes' => $this->faker->sentence(),
            'created_by' => User::factory(),
        ];
    }
}

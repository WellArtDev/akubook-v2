<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\WorkShift;
use Illuminate\Database\Eloquent\Factories\Factory;

class WorkShiftFactory extends Factory
{
    protected $model = WorkShift::class;

    public function definition(): array
    {
        return [
            'shift_code' => 'SHF-' . fake()->unique()->numerify('###'),
            'name' => fake()->randomElement(['Shift Pagi', 'Shift Siang', 'Shift Malam']),
            'check_in_time' => '08:00',
            'check_out_time' => '17:00',
            'tolerance_minutes' => 15,
            'is_overnight' => false,
            'is_active' => true,
            'notes' => null,
            'created_by' => User::factory(),
            'updated_by' => null,
        ];
    }
}

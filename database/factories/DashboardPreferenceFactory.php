<?php

namespace Database\Factories;

use App\Models\DashboardPreference;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class DashboardPreferenceFactory extends Factory
{
    protected $model = DashboardPreference::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'refresh_seconds' => $this->faker->randomElement(DashboardPreference::REFRESH_INTERVALS),
            'auto_refresh_enabled' => true,
        ];
    }
}

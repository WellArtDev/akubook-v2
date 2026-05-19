<?php

namespace Database\Factories;

use App\Models\DataRetentionPolicy;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class DataRetentionPolicyFactory extends Factory
{
    protected $model = DataRetentionPolicy::class;

    public function definition(): array
    {
        return [
            'policy_key' => 'RET-'.$this->faker->unique()->bothify('??###'),
            'entity_type' => $this->faker->randomElement(['audit_log', 'offline_sync_event', 'attendance_record', 'employee_document']),
            'retention_days' => $this->faker->numberBetween(30, 3650),
            'action' => $this->faker->randomElement(DataRetentionPolicy::ACTIONS),
            'is_active' => true,
            'description' => $this->faker->sentence(),
            'created_by' => User::factory(),
        ];
    }
}

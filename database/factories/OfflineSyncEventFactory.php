<?php

namespace Database\Factories;

use App\Models\OfflineSyncEvent;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class OfflineSyncEventFactory extends Factory
{
    protected $model = OfflineSyncEvent::class;

    public function definition(): array
    {
        return [
            'client_event_id' => $this->faker->uuid(),
            'entity' => 'attendance',
            'action' => $this->faker->randomElement(['check_in', 'check_out']),
            'payload' => [
                'employee_identifier' => 'EMP-' . $this->faker->numerify('####'),
                'clock_at' => now()->toDateTimeString(),
                'clock_type' => 'check_in',
            ],
            'status' => 'synced',
            'created_by' => User::factory(),
        ];
    }
}

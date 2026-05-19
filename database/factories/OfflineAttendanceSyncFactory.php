<?php

namespace Database\Factories;

use App\Models\OfflineAttendanceSync;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OfflineAttendanceSync>
 */
class OfflineAttendanceSyncFactory extends Factory
{
    protected $model = OfflineAttendanceSync::class;

    public function definition(): array
    {
        $clockAt = $this->faker->dateTimeBetween('-1 day', 'now');

        return [
            'sync_key' => hash('sha256', $this->faker->uuid),
            'employee_identifier' => 'EMP-' . $this->faker->numerify('####'),
            'clock_type' => $this->faker->randomElement(OfflineAttendanceSync::CLOCK_TYPES),
            'clock_at' => $clockAt,
            'status' => 'synced',
            'created_by' => User::factory(),
        ];
    }
}

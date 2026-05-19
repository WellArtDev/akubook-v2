<?php

namespace Database\Factories;

use App\Models\Employee;
use App\Models\User;
use App\Models\ZktecoAttendanceLog;
use App\Models\ZktecoDevice;
use Illuminate\Database\Eloquent\Factories\Factory;

class ZktecoAttendanceLogFactory extends Factory
{
    protected $model = ZktecoAttendanceLog::class;

    public function definition(): array
    {
        $punchAt = fake()->dateTimeBetween('-1 week');
        $identifier = 'EMP-' . fake()->unique()->numerify('####');
        $type = fake()->randomElement(ZktecoAttendanceLog::PUNCH_TYPES);

        return [
            'zkteco_device_id' => ZktecoDevice::factory(),
            'employee_identifier' => $identifier,
            'punch_at' => $punchAt,
            'punch_type' => $type,
            'employee_id' => null,
            'attendance_record_id' => null,
            'is_mapped' => false,
            'source_key' => hash('sha256', implode('|', [1, $identifier, $punchAt->format('Y-m-d H:i:s'), $type, fake()->uuid()])),
            'notes' => null,
            'created_by' => User::factory(),
        ];
    }

    public function mapped(): static
    {
        return $this->state(fn () => ['employee_id' => Employee::factory(), 'is_mapped' => true]);
    }
}

<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\ZktecoDevice;
use Illuminate\Database\Eloquent\Factories\Factory;

class ZktecoDeviceFactory extends Factory
{
    protected $model = ZktecoDevice::class;

    public function definition(): array
    {
        return [
            'device_code' => 'ZK-' . strtoupper(fake()->bothify('??##')),
            'name' => fake()->company() . ' Device',
            'ip_address' => fake()->ipv4(),
            'port' => 4370,
            'is_active' => true,
            'notes' => fake()->optional()->sentence(),
            'created_by' => User::factory(),
            'updated_by' => null,
        ];
    }
}

<?php

namespace Database\Factories;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class AuditLogFactory extends Factory
{
    protected $model = AuditLog::class;

    public function definition(): array
    {
        $entityId = $this->faker->numberBetween(1, 9999);
        $action = $this->faker->randomElement(['create', 'update', 'delete']);

        return [
            'user_id' => User::factory(),
            'auditable_type' => 'salary_component',
            'auditable_id' => $entityId,
            'event' => $action,
            'event_key' => $this->faker->randomElement(['salary_component.created', 'salary_component.updated', 'salary_component.deleted']),
            'entity_type' => 'salary_component',
            'entity_id' => $entityId,
            'action' => $action,
            'actor_user_id' => User::factory(),
            'is_sensitive' => false,
            'sensitivity_level' => null,
            'sensitivity_reason' => null,
            'occurred_at' => now(),
            'ip_address' => '127.0.0.1',
            'user_agent' => 'phpunit',
            'old_values' => ['name' => 'Old Name'],
            'new_values' => ['name' => 'New Name'],
            'metadata' => ['source' => 'test'],
        ];
    }
}

<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Http\Request;

class AuditLogger
{
    public function log(
        string $eventKey,
        string $entityType,
        int $entityId,
        string $action,
        ?int $actorUserId,
        ?array $oldValues = null,
        ?array $newValues = null,
        ?array $metadata = null,
        ?Request $request = null,
        bool $isSensitive = false,
        ?string $sensitivityLevel = null,
        ?string $sensitivityReason = null
    ): AuditLog {
        return AuditLog::create([
            'user_id' => $actorUserId,
            'auditable_type' => $entityType,
            'auditable_id' => $entityId,
            'event' => $action,
            'event_key' => $eventKey,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'action' => $action,
            'actor_user_id' => $actorUserId,
            'is_sensitive' => $isSensitive,
            'sensitivity_level' => $isSensitive ? ($sensitivityLevel ?? 'medium') : null,
            'sensitivity_reason' => $isSensitive ? $sensitivityReason : null,
            'occurred_at' => now(),
            'ip_address' => $request?->ip(),
            'user_agent' => $request?->userAgent(),
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'metadata' => $metadata,
        ]);
    }
}

<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class AuditService
{
    /**
     * Log a model event (created, updated, deleted)
     *
     * @param Model $auditable The model being audited
     * @param string $event The event type (created, updated, deleted)
     * @param array|null $oldValues The old values (for update/delete)
     * @param array|null $newValues The new values (for create/update)
     * @return AuditLog|null
     */
    public static function log(
        Model $auditable,
        string $event,
        ?array $oldValues = null,
        ?array $newValues = null
    ): ?AuditLog {
        try {
            // Get new values if not provided
            if ($newValues === null && in_array($event, ['created', 'updated'])) {
                $newValues = $auditable->getAttributes();
            }

            // Remove sensitive fields
            $newValues = static::removeSensitiveFields($newValues);
            $oldValues = static::removeSensitiveFields($oldValues);

            return AuditLog::create([
                'user_id' => Auth::id(),
                'auditable_type' => get_class($auditable),
                'auditable_id' => $auditable->id ?? null,
                'event' => $event,
                'old_values' => $oldValues,
                'new_values' => $newValues,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);
        } catch (\Exception $e) {
            // Log error but don't fail the main operation
            \Log::error('Audit logging failed: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Log an authentication event
     *
     * @param string $event The event type (login, logout, login_failed, etc.)
     * @param User|null $user The user (null for failed attempts)
     * @param string|null $reason Additional reason/description
     * @param array $additionalData Additional data to log
     * @return AuditLog|null
     */
    public static function logAuth(
        string $event,
        ?User $user = null,
        ?string $reason = null,
        array $additionalData = []
    ): ?AuditLog {
        try {
            $data = array_merge([
                'message' => $reason ?? ucfirst(str_replace('_', ' ', $event)),
            ], $additionalData);

            return AuditLog::create([
                'user_id' => $user?->id,
                'auditable_type' => 'App\Models\User',
                'auditable_id' => $user?->id,
                'event' => $event,
                'old_values' => null,
                'new_values' => $data,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);
        } catch (\Exception $e) {
            \Log::error('Auth audit logging failed: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Log a custom event
     *
     * @param string $event The event type
     * @param string $description Event description
     * @param array $data Additional data
     * @param Model|null $auditable Related model (optional)
     * @return AuditLog|null
     */
    public static function logCustom(
        string $event,
        string $description,
        array $data = [],
        ?Model $auditable = null
    ): ?AuditLog {
        try {
            return AuditLog::create([
                'user_id' => Auth::id(),
                'auditable_type' => $auditable ? get_class($auditable) : null,
                'auditable_id' => $auditable?->id,
                'event' => $event,
                'old_values' => null,
                'new_values' => array_merge(['description' => $description], $data),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);
        } catch (\Exception $e) {
            \Log::error('Custom audit logging failed: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Log a business operation (journal posting, report generation, etc.)
     *
     * @param string $operation The operation name
     * @param string $description Operation description
     * @param array $data Operation data
     * @param Model|null $relatedModel Related model
     * @return AuditLog|null
     */
    public static function logOperation(
        string $operation,
        string $description,
        array $data = [],
        ?Model $relatedModel = null
    ): ?AuditLog {
        return static::logCustom(
            'operation_' . $operation,
            $description,
            $data,
            $relatedModel
        );
    }

    /**
     * Remove sensitive fields from data before logging
     *
     * @param array|null $data
     * @return array|null
     */
    protected static function removeSensitiveFields(?array $data): ?array
    {
        if ($data === null) {
            return null;
        }

        $sensitiveFields = [
            'password',
            'password_confirmation',
            'remember_token',
            'api_token',
            'secret',
            'private_key',
            'access_token',
            'refresh_token',
        ];

        foreach ($sensitiveFields as $field) {
            if (isset($data[$field])) {
                $data[$field] = '[REDACTED]';
            }
        }

        return $data;
    }

    /**
     * Get audit logs for a specific model
     *
     * @param Model $model
     * @param int $limit
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getLogsFor(Model $model, int $limit = 50)
    {
        return AuditLog::where('auditable_type', get_class($model))
            ->where('auditable_id', $model->id)
            ->with('user')
            ->latest()
            ->limit($limit)
            ->get();
    }

    /**
     * Get recent audit logs
     *
     * @param int $limit
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getRecent(int $limit = 100)
    {
        return AuditLog::with('user')
            ->latest()
            ->limit($limit)
            ->get();
    }
}

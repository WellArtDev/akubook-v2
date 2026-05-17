<?php

namespace App\Traits;

use App\Models\AuditLog;
use App\Services\AuditService;

trait Auditable
{
    /**
     * Boot the Auditable trait for a model.
     */
    protected static function bootAuditable(): void
    {
        // Log when model is created
        static::created(function ($model) {
            if ($model->shouldAudit()) {
                AuditService::log($model, 'created');
            }
        });

        // Log when model is updated
        static::updated(function ($model) {
            if ($model->shouldAudit() && $model->wasChanged()) {
                // Get original values before update
                $oldValues = $model->getOriginal();
                AuditService::log($model, 'updated', $oldValues);
            }
        });

        // Log when model is deleted
        static::deleted(function ($model) {
            if ($model->shouldAudit()) {
                // Capture all attributes before deletion
                $oldValues = $model->getAttributes();
                AuditService::log($model, 'deleted', $oldValues);
            }
        });

        // Log when model is restored (soft delete)
        if (method_exists(static::class, 'restored')) {
            static::restored(function ($model) {
                if ($model->shouldAudit()) {
                    AuditService::log($model, 'restored');
                }
            });
        }
    }

    /**
     * Get audit logs for this model instance (relationship)
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function auditLogs()
    {
        return $this->morphMany(AuditLog::class, 'auditable');
    }

    /**
     * Get recent audit logs for this model instance
     *
     * @param int $limit
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAuditLogs(int $limit = 50)
    {
        return AuditService::getLogsFor($this, $limit);
    }

    /**
     * Check if this model should be audited
     * Override this method in your model to conditionally audit
     *
     * @return bool
     */
    public function shouldAudit(): bool
    {
        return true;
    }
}

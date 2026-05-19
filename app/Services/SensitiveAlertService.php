<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\SensitiveAlert;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class SensitiveAlertService
{
    public function __construct(private readonly AuditLogger $auditLogger)
    {
    }

    public function generate(?Request $request = null, int $threshold = 3, int $windowMinutes = 60): ?SensitiveAlert
    {
        $windowEnd = now();
        $windowStart = (clone $windowEnd)->subMinutes($windowMinutes);
        $window = sprintf('%d_minutes', $windowMinutes);
        $idempotencyKey = sprintf('high:%s:%s:%d', $windowStart->format('YmdHi'), $windowEnd->format('YmdHi'), $threshold);

        $existing = SensitiveAlert::query()->where('idempotency_key', $idempotencyKey)->first();

        if ($existing) {
            return $existing;
        }

        $highQuery = AuditLog::query()
            ->where('is_sensitive', true)
            ->where('sensitivity_level', 'high')
            ->whereBetween('occurred_at', [$windowStart, $windowEnd]);

        $highCount = (clone $highQuery)->count();

        if ($highCount < $threshold) {
            return null;
        }

        $topEntities = (clone $highQuery)
            ->selectRaw('entity_type, COUNT(*) as total')
            ->groupBy('entity_type')
            ->orderByDesc('total')
            ->limit(3)
            ->get()
            ->map(fn ($row) => ['entity_type' => $row->entity_type, 'count' => (int) $row->total])
            ->values()
            ->all();

        $alert = SensitiveAlert::create([
            'idempotency_key' => $idempotencyKey,
            'window' => $window,
            'window_start' => $windowStart,
            'window_end' => $windowEnd,
            'high_count' => $highCount,
            'threshold' => $threshold,
            'top_entities' => $topEntities,
            'status' => 'triggered',
            'generated_at' => now(),
            'generated_by' => Auth::id(),
        ]);

        $this->auditLogger->log(
            'sensitive_alert.generated',
            'sensitive_alert',
            $alert->id,
            'generate',
            Auth::id(),
            null,
            [
                'window' => $window,
                'window_start' => $windowStart->toDateTimeString(),
                'window_end' => $windowEnd->toDateTimeString(),
                'high_count' => $highCount,
                'threshold' => $threshold,
                'status' => $alert->status,
            ],
            [
                'top_entities' => $topEntities,
                'idempotency_key' => $idempotencyKey,
            ],
            $request,
            true,
            'high',
            'Sensitive alert generated for high-risk activity surge'
        );

        return $alert;
    }
}

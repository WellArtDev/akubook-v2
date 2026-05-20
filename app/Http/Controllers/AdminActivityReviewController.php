<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;
use Inertia\Inertia;

class AdminActivityReviewController extends Controller
{
    public function index(Request $request)
    {
        $query = AuditLog::query()
            ->with('actor:id,name,email')
            ->where('is_sensitive', true)
            ->when($request->filled('event_key'), fn ($builder) => $builder->where('event_key', $request->string('event_key')))
            ->when($request->filled('entity_type'), fn ($builder) => $builder->where('entity_type', $request->string('entity_type')))
            ->when($request->filled('sensitivity_level'), fn ($builder) => $builder->where('sensitivity_level', $request->string('sensitivity_level')))
            ->when($request->filled('actor_user_id'), fn ($builder) => $builder->where('actor_user_id', (int) $request->input('actor_user_id')))
            ->when($request->filled('date_from'), fn ($builder) => $builder->whereDate('occurred_at', '>=', $request->string('date_from')))
            ->when($request->filled('date_to'), fn ($builder) => $builder->whereDate('occurred_at', '<=', $request->string('date_to')))
            ->latest('occurred_at');

        /** @var LengthAwarePaginator $activities */
        $activities = $query->paginate(50)->withQueryString();
        $activities->through(fn (AuditLog $log) => [
            'id' => $log->id,
            'occurred_at' => optional($log->occurred_at)->toIso8601String(),
            'event_key' => $log->event_key,
            'entity_type' => $log->entity_type,
            'entity_id' => $log->entity_id,
            'action' => $log->action,
            'sensitivity_level' => $log->sensitivity_level,
            'sensitivity_reason' => $log->sensitivity_reason,
            'actor' => $log->actor ? [
                'id' => $log->actor->id,
                'name' => $log->actor->name,
                'email' => $log->actor->email,
            ] : null,
            'metadata' => $this->sanitize($log->metadata),
        ]);

        $kpis = [
            'sensitive_total' => (clone $query)->count(),
            'high_severity_total' => (clone $query)->where('sensitivity_level', 'high')->count(),
            'failed_or_blocked_total' => (clone $query)->whereIn('action', ['failed', 'blocked', 'rejected'])->count(),
        ];

        return Inertia::render('AdminActivityReview/Index', [
            'activities' => $activities,
            'kpis' => $kpis,
            'filters' => $request->only(['event_key', 'entity_type', 'sensitivity_level', 'actor_user_id', 'date_from', 'date_to']),
            'users' => User::query()->orderBy('name')->get(['id', 'name', 'email']),
            'eventKeys' => AuditLog::query()->where('is_sensitive', true)->select('event_key')->distinct()->orderBy('event_key')->pluck('event_key'),
            'entityTypes' => AuditLog::query()->where('is_sensitive', true)->select('entity_type')->distinct()->orderBy('entity_type')->pluck('entity_type'),
            'levels' => AuditLog::query()->where('is_sensitive', true)->whereNotNull('sensitivity_level')->select('sensitivity_level')->distinct()->orderBy('sensitivity_level')->pluck('sensitivity_level'),
        ]);
    }

    private function sanitize(mixed $value): mixed
    {
        if (! is_array($value)) {
            return $value;
        }

        $masked = [];
        foreach ($value as $key => $item) {
            if (is_string($key) && $this->isSensitiveKey($key)) {
                $masked[$key] = '***';
                continue;
            }

            $masked[$key] = is_array($item) ? $this->sanitize($item) : $item;
        }

        return $masked;
    }

    private function isSensitiveKey(string $key): bool
    {
        $normalized = Str::lower($key);

        return Str::contains($normalized, ['password', 'secret', 'token', 'api_key', 'apikey', 'authorization']);
    }
}

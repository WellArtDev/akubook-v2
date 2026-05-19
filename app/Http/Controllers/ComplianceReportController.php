<?php

namespace App\Http\Controllers;

use App\Models\ApprovalWorkflow;
use App\Models\AuditLog;
use App\Models\DataRetentionPolicy;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ComplianceReportController extends Controller
{
    public function index(Request $request)
    {
        $validated = $request->validate([
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
        ]);

        $dateFrom = $validated['date_from'] ?? now()->startOfMonth()->toDateString();
        $dateTo = $validated['date_to'] ?? now()->toDateString();

        $auditQuery = AuditLog::query()
            ->whereDate('occurred_at', '>=', $dateFrom)
            ->whereDate('occurred_at', '<=', $dateTo);

        $sensitiveQuery = (clone $auditQuery)->where('is_sensitive', true);

        $auditByEntity = (clone $auditQuery)
            ->selectRaw('entity_type, COUNT(*) as total')
            ->groupBy('entity_type')
            ->orderBy('entity_type')
            ->get()
            ->map(fn ($row) => [
                'entity_type' => $row->entity_type ?: 'unknown',
                'total' => (int) $row->total,
            ]);

        $sensitiveByLevel = (clone $sensitiveQuery)
            ->selectRaw('sensitivity_level, COUNT(*) as total')
            ->groupBy('sensitivity_level')
            ->orderBy('sensitivity_level')
            ->get()
            ->map(fn ($row) => [
                'sensitivity_level' => $row->sensitivity_level ?: 'unknown',
                'total' => (int) $row->total,
            ]);

        $retentionByAction = DataRetentionPolicy::query()
            ->where('is_active', true)
            ->selectRaw('action, COUNT(*) as total')
            ->groupBy('action')
            ->orderBy('action')
            ->get()
            ->map(fn ($row) => [
                'action' => $row->action,
                'total' => (int) $row->total,
            ]);

        return Inertia::render('ComplianceReports/Index', [
            'filters' => [
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
            ],
            'summary' => [
                'audit_logs' => (clone $auditQuery)->count(),
                'sensitive_actions' => (clone $sensitiveQuery)->count(),
                'active_retention_policies' => DataRetentionPolicy::query()->where('is_active', true)->count(),
                'active_approval_workflows' => ApprovalWorkflow::query()->where('is_active', true)->count(),
            ],
            'auditByEntity' => $auditByEntity,
            'sensitiveByLevel' => $sensitiveByLevel,
            'retentionByAction' => $retentionByAction,
            'generated_at' => now()->toDateTimeString(),
        ]);
    }
}

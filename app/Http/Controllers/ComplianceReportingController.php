<?php

namespace App\Http\Controllers;

use App\Models\ApprovalWorkflow;
use App\Models\AuditLog;
use App\Models\DataRetentionPolicy;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ComplianceReportingController extends Controller
{
    public function index(Request $request)
    {
        $validated = $request->validate([
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
            'entity_type' => ['nullable', 'string', 'max:100'],
            'sensitivity_level' => ['nullable', 'string', 'max:50'],
        ]);

        $dateFrom = $validated['date_from'] ?? now()->startOfMonth()->toDateString();
        $dateTo = $validated['date_to'] ?? now()->toDateString();

        $auditBase = AuditLog::query()
            ->whereDate('occurred_at', '>=', $dateFrom)
            ->whereDate('occurred_at', '<=', $dateTo)
            ->when($request->filled('entity_type'), fn ($query) => $query->where('entity_type', $request->entity_type));

        $sensitiveBase = (clone $auditBase)
            ->where('is_sensitive', true)
            ->when($request->filled('sensitivity_level'), fn ($query) => $query->where('sensitivity_level', $request->sensitivity_level));

        $summary = [
            'audit_logs_total' => (clone $auditBase)->count(),
            'sensitive_actions_total' => (clone $sensitiveBase)->count(),
            'active_retention_policies' => DataRetentionPolicy::query()->where('is_active', true)->count(),
            'active_approval_workflows' => ApprovalWorkflow::query()->where('is_active', true)->count(),
        ];

        $sensitiveByLevel = (clone $sensitiveBase)
            ->selectRaw('COALESCE(sensitivity_level, ?) as level, COUNT(*) as total', ['unknown'])
            ->groupBy('level')
            ->orderBy('level')
            ->get()
            ->map(fn ($row) => [
                'level' => $row->level,
                'total' => (int) $row->total,
            ]);

        $auditByEntity = (clone $auditBase)
            ->selectRaw('COALESCE(entity_type, ?) as entity_type, COUNT(*) as total', ['unknown'])
            ->groupBy('entity_type')
            ->orderBy('entity_type')
            ->get()
            ->map(fn ($row) => [
                'entity_type' => $row->entity_type,
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

        return Inertia::render('ComplianceReporting/Index', [
            'filters' => [
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
                'entity_type' => $request->input('entity_type', ''),
                'sensitivity_level' => $request->input('sensitivity_level', ''),
            ],
            'summary' => $summary,
            'breakdown' => [
                'sensitive_by_level' => $sensitiveByLevel,
                'audit_by_entity' => $auditByEntity,
                'retention_by_action' => $retentionByAction,
            ],
            'entityTypes' => AuditLog::query()->select('entity_type')->whereNotNull('entity_type')->distinct()->orderBy('entity_type')->pluck('entity_type'),
            'sensitivityLevels' => AuditLog::query()->select('sensitivity_level')->whereNotNull('sensitivity_level')->distinct()->orderBy('sensitivity_level')->pluck('sensitivity_level'),
            'generated_at' => now()->toDateTimeString(),
        ]);
    }
}

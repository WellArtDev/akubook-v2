<?php

namespace App\Http\Controllers;

use App\Models\AttendanceRecord;
use App\Models\AuditLog;
use App\Models\DataRetentionExecution;
use App\Models\DataRetentionPolicy;
use App\Models\EmployeeDocument;
use App\Models\OfflineSyncEvent;
use App\Services\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class DataRetentionExecutionController extends Controller
{
    private const ENTITIES = [
        'audit_log' => ['table' => 'audit_logs', 'date_column' => 'occurred_at', 'model' => AuditLog::class],
        'offline_sync_event' => ['table' => 'offline_sync_events', 'date_column' => 'created_at', 'model' => OfflineSyncEvent::class],
        'attendance_record' => ['table' => 'attendance_records', 'date_column' => 'attendance_date', 'model' => AttendanceRecord::class],
        'employee_document' => ['table' => 'employee_documents', 'date_column' => 'expiry_date', 'model' => EmployeeDocument::class],
    ];

    public function __construct(private readonly AuditLogger $auditLogger)
    {
    }

    public function index()
    {
        $executions = DataRetentionExecution::query()
            ->with(['policy:id,policy_key', 'creator:id,name'])
            ->latest()
            ->paginate(50)
            ->withQueryString();

        return Inertia::render('DataRetentionExecutions/Index', [
            'executions' => $executions,
        ]);
    }

    public function show(DataRetentionExecution $dataRetentionExecution)
    {
        $dataRetentionExecution->load(['policy:id,policy_key', 'creator:id,name']);

        return Inertia::render('DataRetentionExecutions/Show', [
            'execution' => $dataRetentionExecution,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'policy_id' => ['required', 'integer', Rule::exists('data_retention_policies', 'id')],
            'mode' => ['required', Rule::in(['dry-run', 'execute'])],
        ]);

        $policy = DataRetentionPolicy::query()->where('is_active', true)->findOrFail($validated['policy_id']);
        $entity = self::ENTITIES[$policy->entity_type] ?? null;

        abort_if(! $entity, 422, 'Unsupported policy entity.');

        $cutoffDate = now()->subDays($policy->retention_days)->toDateString();
        $baseQuery = DB::table($entity['table'])->whereDate($entity['date_column'], '<=', $cutoffDate);
        $candidateCount = (clone $baseQuery)->count();
        $processedCount = 0;

        if ($validated['mode'] === 'execute' && $policy->action === 'delete') {
            $processedCount = $this->executeDelete($policy->entity_type, $entity['model'], $entity['date_column'], $cutoffDate);
        }

        $execution = DataRetentionExecution::create([
            'data_retention_policy_id' => $policy->id,
            'mode' => $validated['mode'],
            'entity_type' => $policy->entity_type,
            'action' => $policy->action,
            'cutoff_date' => $cutoffDate,
            'candidate_count' => $candidateCount,
            'processed_count' => $processedCount,
            'status' => 'completed',
            'summary' => [
                'candidate_count' => $candidateCount,
                'processed_count' => $processedCount,
                'action' => $policy->action,
                'status' => 'completed',
            ],
            'created_by' => Auth::id(),
        ]);

        $this->auditLogger->log(
            'data_retention.execution.run',
            'data_retention_execution',
            $execution->id,
            'run',
            Auth::id(),
            null,
            [
                'policy_id' => $policy->id,
                'mode' => $validated['mode'],
                'action' => $policy->action,
                'candidate_count' => $candidateCount,
                'processed_count' => $processedCount,
            ],
            ['execution_id' => $execution->id],
            $request,
            true,
            'high',
            'Retention execution affects governed records'
        );

        return redirect()->route('data-retention-executions.show', $execution)->with('success', 'Retention execution completed.');
    }

    private function executeDelete(string $entityType, string $modelClass, string $dateColumn, string $cutoffDate): int
    {
        if ($entityType === 'audit_log') {
            return $modelClass::query()->whereDate($dateColumn, '<=', $cutoffDate)->forceDelete();
        }

        return $modelClass::query()->whereDate($dateColumn, '<=', $cutoffDate)->delete();
    }
}

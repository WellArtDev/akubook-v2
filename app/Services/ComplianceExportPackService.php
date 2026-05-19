<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\ComplianceExportPack;
use App\Models\DataRetentionExecution;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ComplianceExportPackService
{
    public function __construct(private readonly AuditLogger $auditLogger)
    {
    }

    public function generate(string $periodStart, string $periodEnd, ?Request $request = null): ComplianceExportPack
    {
        return DB::transaction(function () use ($periodStart, $periodEnd, $request) {
            $sensitiveAuditLogs = AuditLog::query()
                ->where('is_sensitive', true)
                ->whereBetween('occurred_at', [$periodStart . ' 00:00:00', $periodEnd . ' 23:59:59'])
                ->orderBy('occurred_at')
                ->get(['id', 'event_key', 'entity_type', 'entity_id', 'action', 'actor_user_id', 'sensitivity_level', 'occurred_at', 'metadata']);

            $retentionExecutions = DataRetentionExecution::query()
                ->whereBetween('created_at', [$periodStart . ' 00:00:00', $periodEnd . ' 23:59:59'])
                ->orderBy('created_at')
                ->get(['id', 'data_retention_policy_id', 'mode', 'entity_type', 'action', 'candidate_count', 'processed_count', 'status', 'created_by', 'created_at']);

            $workflowDecisions = AuditLog::query()
                ->where('event_key', 'workflow.enforcement.evaluated')
                ->whereBetween('occurred_at', [$periodStart . ' 00:00:00', $periodEnd . ' 23:59:59'])
                ->orderBy('occurred_at')
                ->get(['id', 'entity_type', 'entity_id', 'action', 'actor_user_id', 'metadata', 'occurred_at']);

            $recordCounts = [
                'audit_logs_sensitive' => $sensitiveAuditLogs->count(),
                'data_retention_executions' => $retentionExecutions->count(),
                'workflow_decisions' => $workflowDecisions->count(),
            ];

            $metadata = [
                'generated_at' => now()->toIso8601String(),
                'generated_by' => Auth::id(),
                'period' => [
                    'start' => $periodStart,
                    'end' => $periodEnd,
                ],
                'record_counts' => $recordCounts,
            ];

            $payload = [
                'metadata' => $metadata,
                'audit_logs_sensitive' => $sensitiveAuditLogs->toArray(),
                'data_retention_executions' => $retentionExecutions->toArray(),
                'workflow_decisions' => $workflowDecisions->toArray(),
            ];

            $pack = ComplianceExportPack::query()->create([
                'pack_number' => ComplianceExportPack::generateNumber(),
                'period_start' => $periodStart,
                'period_end' => $periodEnd,
                'status' => 'generated',
                'record_counts' => $recordCounts,
                'metadata' => $metadata,
                'payload_json' => json_encode($payload, JSON_PRETTY_PRINT),
                'generated_by' => Auth::id(),
                'generated_at' => now(),
            ]);

            $this->auditLogger->log(
                'compliance_export_pack.generated',
                'compliance_export_pack',
                $pack->id,
                'generate',
                Auth::id(),
                null,
                [
                    'pack_number' => $pack->pack_number,
                    'period_start' => $periodStart,
                    'period_end' => $periodEnd,
                    'record_counts' => $recordCounts,
                ],
                [
                    'pack_id' => $pack->id,
                ],
                $request,
                true,
                'high',
                'Compliance export pack includes sensitive governance evidence'
            );

            return $pack;
        });
    }
}

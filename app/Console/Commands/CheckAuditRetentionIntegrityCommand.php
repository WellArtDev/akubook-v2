<?php

namespace App\Console\Commands;

use App\Models\AuditLog;
use App\Models\DataRetentionExecution;
use App\Models\DataRetentionPolicy;
use App\Services\AuditLogger;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class CheckAuditRetentionIntegrityCommand extends Command
{
    protected $signature = 'app:check-audit-retention-integrity {--fail-on=high : Failure threshold: low|medium|high} {--simulate-high-anomaly : Simulate high anomaly for test}';

    protected $description = 'Check audit log retention integrity and report anomalies';

    public function __construct(private readonly AuditLogger $auditLogger)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $failOn = strtolower((string) $this->option('fail-on'));
        if (! in_array($failOn, ['low', 'medium', 'high'], true)) {
            $this->error('Invalid --fail-on value. Use: low, medium, or high.');

            return self::FAILURE;
        }

        $anomalies = [];

        $activePolicies = DataRetentionPolicy::query()
            ->where('is_active', true)
            ->where('entity_type', 'audit_log')
            ->get();

        foreach ($activePolicies as $policy) {
            $cutoff = now()->subDays($policy->retention_days)->toDateString();

            $staleCount = AuditLog::query()
                ->whereDate('occurred_at', '<', $cutoff)
                ->count();

            if ($staleCount > 0) {
                $anomalies[] = [
                    'severity' => $staleCount > 100 ? 'high' : 'medium',
                    'type' => 'stale_records',
                    'policy_key' => $policy->policy_key,
                    'message' => "Found {$staleCount} stale audit records older than cutoff {$cutoff}.",
                    'details' => [
                        'stale_count' => $staleCount,
                        'cutoff_date' => $cutoff,
                    ],
                ];
            }

            $lastExecution = DataRetentionExecution::query()
                ->where('data_retention_policy_id', $policy->id)
                ->latest('id')
                ->first();

            if (! $lastExecution) {
                $anomalies[] = [
                    'severity' => 'high',
                    'type' => 'missing_execution',
                    'policy_key' => $policy->policy_key,
                    'message' => 'No retention execution found for active audit policy.',
                    'details' => [
                        'policy_id' => $policy->id,
                    ],
                ];
            } elseif ($lastExecution->candidate_count !== $lastExecution->processed_count && $lastExecution->mode === 'execute') {
                $anomalies[] = [
                    'severity' => 'medium',
                    'type' => 'execution_mismatch',
                    'policy_key' => $policy->policy_key,
                    'message' => 'Retention execution candidate/processed mismatch detected.',
                    'details' => [
                        'execution_id' => $lastExecution->id,
                        'candidate_count' => $lastExecution->candidate_count,
                        'processed_count' => $lastExecution->processed_count,
                    ],
                ];
            }
        }

        if ($this->option('simulate-high-anomaly')) {
            $anomalies[] = [
                'severity' => 'high',
                'type' => 'simulated',
                'policy_key' => 'simulated',
                'message' => 'Simulated high anomaly for guardrail verification.',
                'details' => [],
            ];
        }

        $severityRank = ['low' => 1, 'medium' => 2, 'high' => 3];
        $highestSeverity = 'low';
        foreach ($anomalies as $anomaly) {
            if ($severityRank[$anomaly['severity']] > $severityRank[$highestSeverity]) {
                $highestSeverity = $anomaly['severity'];
            }
        }

        $status = empty($anomalies) ? 'healthy' : ($highestSeverity === 'high' ? 'breach' : 'warning');

        $report = [
            'generated_at' => now()->toIso8601String(),
            'status' => $status,
            'highest_severity' => $highestSeverity,
            'anomaly_count' => count($anomalies),
            'anomalies' => $anomalies,
            'summary' => [
                'active_audit_policies' => $activePolicies->count(),
                'audit_log_count' => AuditLog::query()->count(),
            ],
        ];

        $artifact = base_path('_bmad-output/implementation-artifacts/performance-baselines/audit-retention-integrity-latest.json');
        File::ensureDirectoryExists(dirname($artifact));
        File::put($artifact, json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES).PHP_EOL);

        $this->auditLogger->log(
            'audit_retention.integrity_check',
            'audit_retention_integrity',
            1,
            $status,
            auth()->id(),
            null,
            [
                'status' => $status,
                'highest_severity' => $highestSeverity,
                'anomaly_count' => count($anomalies),
            ],
            [
                'fail_on' => $failOn,
            ],
            null,
            true,
            'high',
            'Audit retention integrity check result'
        );

        if (! empty($anomalies)) {
            $this->warn("Integrity status: {$status}");
            foreach ($anomalies as $anomaly) {
                $this->line("- [{$anomaly['severity']}] {$anomaly['message']}");
            }
        } else {
            $this->info('Integrity status: healthy');
        }

        if ($severityRank[$highestSeverity] >= $severityRank[$failOn] && ! empty($anomalies)) {
            return self::FAILURE;
        }

        return self::SUCCESS;
    }
}

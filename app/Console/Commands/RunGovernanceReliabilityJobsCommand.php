<?php

namespace App\Console\Commands;

use App\Models\ComplianceExportPack;
use App\Services\AuditLogger;
use App\Services\ComplianceExportPackService;
use App\Services\SensitiveAlertService;
use Illuminate\Console\Command;
use Throwable;

class RunGovernanceReliabilityJobsCommand extends Command
{
    protected $signature = 'governance:run-reliability-jobs {--threshold=3} {--window-minutes=60} {--period-start=} {--period-end=}';

    protected $description = 'Run governance reliability jobs for sensitive alerts and compliance export packs';

    public function __construct(
        private readonly SensitiveAlertService $sensitiveAlertService,
        private readonly ComplianceExportPackService $complianceExportPackService,
        private readonly AuditLogger $auditLogger
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $threshold = (int) $this->option('threshold');
        $windowMinutes = (int) $this->option('window-minutes');
        $periodStart = $this->option('period-start') ?: now()->toDateString();
        $periodEnd = $this->option('period-end') ?: now()->toDateString();

        if ($threshold < 1 || $windowMinutes < 1) {
            $this->logFailure($threshold, $windowMinutes, $periodStart, $periodEnd, 'Invalid options: threshold and window-minutes must be >= 1');
            $this->error('Invalid options: threshold and window-minutes must be >= 1');

            return self::FAILURE;
        }

        try {
            $alert = $this->sensitiveAlertService->generate(null, $threshold, $windowMinutes);
            $pack = ComplianceExportPack::query()
                ->whereDate('period_start', $periodStart)
                ->whereDate('period_end', $periodEnd)
                ->first() ?? $this->complianceExportPackService->generate($periodStart, $periodEnd);

            $this->auditLogger->log(
                'governance.reliability_jobs.completed',
                'governance_reliability_job',
                1,
                'completed',
                null,
                null,
                [
                    'threshold' => $threshold,
                    'window_minutes' => $windowMinutes,
                    'period_start' => $periodStart,
                    'period_end' => $periodEnd,
                    'sensitive_alert_id' => $alert?->id,
                    'compliance_export_pack_id' => $pack->id,
                    'alert_status' => $alert ? 'generated_or_existing' : 'below_threshold',
                ],
                [
                    'source' => 'scheduler_or_manual_command',
                ],
                null,
                true,
                'high',
                'Governance reliability job completed for sensitive automation flows'
            );

            $this->info('Governance reliability jobs completed.');

            return self::SUCCESS;
        } catch (Throwable $exception) {
            $this->logFailure($threshold, $windowMinutes, $periodStart, $periodEnd, $exception->getMessage());
            $this->error($exception->getMessage());

            return self::FAILURE;
        }
    }

    private function logFailure(int $threshold, int $windowMinutes, string $periodStart, string $periodEnd, string $error): void
    {
        $this->auditLogger->log(
            'governance.reliability_jobs.failed',
            'governance_reliability_job',
            1,
            'failed',
            null,
            null,
            [
                'threshold' => $threshold,
                'window_minutes' => $windowMinutes,
                'period_start' => $periodStart,
                'period_end' => $periodEnd,
                'error' => $error,
            ],
            [
                'source' => 'scheduler_or_manual_command',
            ],
            null,
            true,
            'high',
            'Governance reliability job failed and needs investigation'
        );
    }
}

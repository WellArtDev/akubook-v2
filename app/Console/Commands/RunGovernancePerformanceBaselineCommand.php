<?php

namespace App\Console\Commands;

use App\Models\AuditLog;
use App\Models\ComplianceExportPack;
use App\Models\DataRetentionExecution;
use App\Models\SensitiveAlert;
use App\Services\ComplianceExportPackService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class RunGovernancePerformanceBaselineCommand extends Command
{
    protected $signature = 'governance:benchmark-baseline {--date-from=} {--date-to=} {--threshold-ms=1500}';

    protected $description = 'Run governance performance baseline benchmark and save artifact';

    public function __construct(private readonly ComplianceExportPackService $complianceExportPackService)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $dateFrom = $this->option('date-from') ?: now()->startOfMonth()->toDateString();
        $dateTo = $this->option('date-to') ?: now()->toDateString();
        $thresholdMs = (int) $this->option('threshold-ms');

        if ($thresholdMs < 1) {
            $this->error('Invalid option: threshold-ms must be >= 1');

            return self::FAILURE;
        }

        $dashboard = $this->benchmarkDashboardQueries($dateFrom, $dateTo);
        $export = $this->benchmarkExportGeneration($dateFrom, $dateTo);
        $recommendations = $this->recommendations($dashboard, $export, $thresholdMs);

        $artifact = [
            'generated_at' => now()->toIso8601String(),
            'period' => [
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
            ],
            'dataset_assumption' => [
                'retention_runs' => DataRetentionExecution::query()->whereDate('created_at', '>=', $dateFrom)->whereDate('created_at', '<=', $dateTo)->count(),
                'enforcement_logs' => AuditLog::query()->where('event_key', 'workflow.enforcement.evaluated')->whereDate('occurred_at', '>=', $dateFrom)->whereDate('occurred_at', '<=', $dateTo)->count(),
                'sensitive_alerts' => SensitiveAlert::query()->whereDate('generated_at', '>=', $dateFrom)->whereDate('generated_at', '<=', $dateTo)->count(),
                'export_packs' => ComplianceExportPack::query()->whereDate('generated_at', '>=', $dateFrom)->whereDate('generated_at', '<=', $dateTo)->count(),
            ],
            'threshold_ms' => $thresholdMs,
            'benchmarks' => [
                'dashboard_queries' => $dashboard,
                'compliance_export_generation' => $export,
            ],
            'recommendations' => $recommendations,
        ];

        $directory = base_path('_bmad-output/implementation-artifacts/performance-baselines');
        File::ensureDirectoryExists($directory);
        $filePath = $directory.'/governance-baseline-'.$dateFrom.'_'.$dateTo.'.json';

        File::put($filePath, json_encode($artifact, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        $this->info('Governance performance baseline saved: '.$filePath);

        return self::SUCCESS;
    }

    private function benchmarkDashboardQueries(string $dateFrom, string $dateTo): array
    {
        $timings = [];

        $start = microtime(true);
        DataRetentionExecution::query()->whereDate('created_at', '>=', $dateFrom)->whereDate('created_at', '<=', $dateTo)->count();
        $timings['retention_runs_count_ms'] = $this->elapsedMs($start);

        $start = microtime(true);
        AuditLog::query()->where('event_key', 'workflow.enforcement.evaluated')->whereDate('occurred_at', '>=', $dateFrom)->whereDate('occurred_at', '<=', $dateTo)->where('action', 'enforced')->count();
        $timings['enforcement_count_ms'] = $this->elapsedMs($start);

        $start = microtime(true);
        SensitiveAlert::query()->whereDate('generated_at', '>=', $dateFrom)->whereDate('generated_at', '<=', $dateTo)->count();
        $timings['sensitive_alerts_count_ms'] = $this->elapsedMs($start);

        $start = microtime(true);
        ComplianceExportPack::query()->whereDate('generated_at', '>=', $dateFrom)->whereDate('generated_at', '<=', $dateTo)->count();
        $timings['export_packs_count_ms'] = $this->elapsedMs($start);

        $timings['total_ms'] = array_sum($timings);

        return $timings;
    }

    private function benchmarkExportGeneration(string $dateFrom, string $dateTo): array
    {
        $start = microtime(true);
        $pack = $this->complianceExportPackService->generate($dateFrom, $dateTo);
        $durationMs = $this->elapsedMs($start);

        return [
            'duration_ms' => $durationMs,
            'pack_id' => $pack->id,
            'pack_number' => $pack->pack_number,
            'record_counts' => $pack->record_counts,
        ];
    }

    private function recommendations(array $dashboard, array $export, int $thresholdMs): array
    {
        $recommendations = [];

        if (($dashboard['total_ms'] ?? 0) > $thresholdMs) {
            $recommendations[] = 'Dashboard KPI query latency exceeds threshold; add targeted index or summary table for governance dashboard.';
        }

        if (($export['duration_ms'] ?? 0) > $thresholdMs) {
            $recommendations[] = 'Compliance export generation exceeds threshold; consider chunked retrieval and pre-aggregated evidence snapshots.';
        }

        if ($recommendations === []) {
            $recommendations[] = 'Baseline within threshold; keep monitoring with same command on larger data windows.';
        }

        return $recommendations;
    }

    private function elapsedMs(float $start): int
    {
        return (int) round((microtime(true) - $start) * 1000);
    }
}

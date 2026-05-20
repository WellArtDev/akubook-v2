<?php

namespace App\Console\Commands;

use App\Models\AuditLog;
use App\Models\ComplianceExportPack;
use App\Models\CustomerPayment;
use App\Models\PurchaseOrder;
use App\Models\SalesOrder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class RunQueryPerformanceBaselineV2Command extends Command
{
    protected $signature = 'app:benchmark-query-baseline-v2 {--date-from=} {--date-to=} {--fast-ms=100} {--slow-ms=500} {--critical-ms=1000}';

    protected $description = 'Run cross-module query performance baseline benchmark and save artifact';

    public function handle(): int
    {
        $dateFrom = $this->option('date-from') ?: now()->startOfMonth()->toDateString();
        $dateTo = $this->option('date-to') ?: now()->toDateString();

        $thresholds = [
            'fast_ms' => (int) $this->option('fast-ms'),
            'slow_ms' => (int) $this->option('slow-ms'),
            'critical_ms' => (int) $this->option('critical-ms'),
        ];

        if ($thresholds['fast_ms'] < 1 || $thresholds['slow_ms'] < $thresholds['fast_ms'] || $thresholds['critical_ms'] < $thresholds['slow_ms']) {
            $this->error('Invalid thresholds. Require: fast >= 1, slow >= fast, critical >= slow.');

            return self::FAILURE;
        }

        $benchmarks = [
            'sales' => $this->benchmarkSalesQueries($dateFrom, $dateTo),
            'purchase' => $this->benchmarkPurchaseQueries($dateFrom, $dateTo),
            'governance' => $this->benchmarkGovernanceQueries($dateFrom, $dateTo),
        ];

        $recommendations = $this->recommendations($benchmarks, $thresholds);

        $artifact = [
            'generated_at' => now()->toIso8601String(),
            'period' => [
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
            ],
            'thresholds' => $thresholds,
            'benchmarks' => $benchmarks,
            'recommendations' => $recommendations,
        ];

        $directory = base_path('_bmad-output/implementation-artifacts/performance-baselines');
        File::ensureDirectoryExists($directory);
        $path = $directory.'/query-baseline-v2-'.$dateFrom.'_'.$dateTo.'.json';
        File::put($path, json_encode($artifact, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        $this->info('Query baseline v2 saved: '.$path);

        return self::SUCCESS;
    }

    private function benchmarkSalesQueries(string $dateFrom, string $dateTo): array
    {
        $timings = [];

        $start = microtime(true);
        SalesOrder::query()->whereDate('so_date', '>=', $dateFrom)->whereDate('so_date', '<=', $dateTo)->sum('grand_total');
        $timings['sales_total_sum_ms'] = $this->elapsedMs($start);

        $start = microtime(true);
        SalesOrder::query()->whereDate('so_date', '>=', $dateFrom)->whereDate('so_date', '<=', $dateTo)->groupBy('status')->selectRaw('status, COUNT(*) as total')->get();
        $timings['sales_status_group_ms'] = $this->elapsedMs($start);

        $timings['total_ms'] = array_sum($timings);

        return $timings;
    }

    private function benchmarkPurchaseQueries(string $dateFrom, string $dateTo): array
    {
        $timings = [];

        $start = microtime(true);
        PurchaseOrder::query()->whereDate('po_date', '>=', $dateFrom)->whereDate('po_date', '<=', $dateTo)->sum('grand_total');
        $timings['purchase_total_sum_ms'] = $this->elapsedMs($start);

        $start = microtime(true);
        PurchaseOrder::query()->whereDate('po_date', '>=', $dateFrom)->whereDate('po_date', '<=', $dateTo)->groupBy('status')->selectRaw('status, COUNT(*) as total')->get();
        $timings['purchase_status_group_ms'] = $this->elapsedMs($start);

        $timings['total_ms'] = array_sum($timings);

        return $timings;
    }

    private function benchmarkGovernanceQueries(string $dateFrom, string $dateTo): array
    {
        $timings = [];

        $start = microtime(true);
        AuditLog::query()->where('event_key', 'workflow.enforcement.evaluated')->whereDate('occurred_at', '>=', $dateFrom)->whereDate('occurred_at', '<=', $dateTo)->count();
        $timings['governance_enforcement_count_ms'] = $this->elapsedMs($start);

        $start = microtime(true);
        ComplianceExportPack::query()->whereDate('generated_at', '>=', $dateFrom)->whereDate('generated_at', '<=', $dateTo)->count();
        $timings['governance_export_count_ms'] = $this->elapsedMs($start);

        $start = microtime(true);
        CustomerPayment::query()->whereDate('payment_date', '>=', $dateFrom)->whereDate('payment_date', '<=', $dateTo)->count();
        $timings['governance_payment_count_ms'] = $this->elapsedMs($start);

        $timings['total_ms'] = array_sum($timings);

        return $timings;
    }

    private function recommendations(array $benchmarks, array $thresholds): array
    {
        $recommendations = [];

        foreach ($benchmarks as $module => $metrics) {
            $total = (int) ($metrics['total_ms'] ?? 0);

            if ($total >= $thresholds['critical_ms']) {
                $recommendations[] = strtoupper($module).': critical latency detected, prioritize index and query plan review.';
                continue;
            }

            if ($total >= $thresholds['slow_ms']) {
                $recommendations[] = strtoupper($module).': slow latency detected, schedule optimization in next sprint.';
                continue;
            }

            if ($total >= $thresholds['fast_ms']) {
                $recommendations[] = strtoupper($module).': acceptable latency, keep monitoring.';
            }
        }

        if ($recommendations === []) {
            $recommendations[] = 'All modules under fast threshold. Keep periodic benchmark runs.';
        }

        return $recommendations;
    }

    private function elapsedMs(float $start): int
    {
        return (int) round((microtime(true) - $start) * 1000);
    }
}

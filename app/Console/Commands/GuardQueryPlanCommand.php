<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class GuardQueryPlanCommand extends Command
{
    protected $signature = 'app:guard-query-plan {--write-baseline : Write or refresh query plan baseline} {--simulate-regression : Simulate plan regression for guardrail test}';

    protected $description = 'Check critical query plans against baseline and fail on regressions';

    public function handle(): int
    {
        $queries = $this->criticalQueries();
        $plans = [];

        $prefix = $this->explainPrefix();

        foreach ($queries as $key => $sql) {
            $rows = DB::select($prefix.' '.$sql);
            $details = array_values(array_map(
                fn ($row) => $this->extractPlanDetail($row),
                $rows
            ));
            $plans[$key] = [
                'sql' => $sql,
                'details' => $details,
                'scan_count' => $this->scanCount($details),
            ];
        }

        if ($this->option('simulate-regression')) {
            $first = array_key_first($plans);
            if ($first !== null) {
                $plans[$first]['details'][] = 'SCAN simulated_regression';
                $plans[$first]['scan_count']++;
            }
        }

        $baselinePath = base_path('_bmad-output/implementation-artifacts/performance-baselines/query-plan-baseline-v1.json');

        if ($this->option('write-baseline') || ! File::exists($baselinePath)) {
            File::ensureDirectoryExists(dirname($baselinePath));
            File::put($baselinePath, json_encode([
                'generated_at' => now()->toIso8601String(),
                'plans' => $plans,
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

            $this->info('Query plan baseline written: '.$baselinePath);

            return self::SUCCESS;
        }

        $baseline = json_decode((string) File::get($baselinePath), true);
        $issues = $this->detectRegressions($baseline['plans'] ?? [], $plans);

        if (! empty($issues)) {
            $this->error('Query plan guardrail failed:');
            foreach ($issues as $issue) {
                $this->line('- '.$issue);
            }

            return self::FAILURE;
        }

        $this->info('Query plan guardrail passed.');

        return self::SUCCESS;
    }

    private function explainPrefix(): string
    {
        return DB::connection()->getDriverName() === 'sqlite' ? 'EXPLAIN QUERY PLAN' : 'EXPLAIN';
    }

    private function extractPlanDetail(object $row): string
    {
        $values = array_values((array) $row);

        return trim(implode(' ', array_map(fn ($value) => (string) $value, $values)));
    }

    private function criticalQueries(): array
    {
        return [
            'sales_order_status_aggregate' => "SELECT status, COUNT(*) AS total FROM sales_orders GROUP BY status",
            'purchase_order_status_aggregate' => "SELECT status, COUNT(*) AS total FROM purchase_orders GROUP BY status",
            'governance_enforcement_count' => "SELECT COUNT(*) AS total FROM audit_logs WHERE event_key = 'workflow.enforcement.evaluated'",
        ];
    }

    private function scanCount(array $details): int
    {
        return collect($details)
            ->filter(fn ($detail) => str_contains(strtoupper($detail), 'SCAN'))
            ->count();
    }

    private function detectRegressions(array $baselinePlans, array $currentPlans): array
    {
        $issues = [];

        foreach ($currentPlans as $key => $current) {
            $baseline = $baselinePlans[$key] ?? null;

            if ($baseline === null) {
                $issues[] = "Missing baseline for {$key}";
                continue;
            }

            $baselineScan = (int) ($baseline['scan_count'] ?? 0);
            $currentScan = (int) ($current['scan_count'] ?? 0);

            if ($currentScan > $baselineScan) {
                $issues[] = "{$key} scan count regressed ({$baselineScan} -> {$currentScan})";
            }
        }

        return $issues;
    }
}

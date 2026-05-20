<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class GuardSloErrorBudgetCommand extends Command
{
    protected $signature = 'app:guard-slo-error-budget {--results= : Smoke result JSON path} {--simulate-breach : Simulate SLO breach for guardrail test}';

    protected $description = 'Evaluate SLO and error budget from latest smoke results';

    public function handle(): int
    {
        $resultsPath = $this->option('results') ?: config('slo.results_path');
        $absoluteResultsPath = $this->absolutePath($resultsPath);

        if (! File::exists($absoluteResultsPath)) {
            $this->error("SLO results not found: {$resultsPath}");

            return self::FAILURE;
        }

        $payload = json_decode(File::get($absoluteResultsPath), true);
        $checks = $payload['checks'] ?? [];

        if (! is_array($checks) || empty($checks)) {
            $this->error('SLO results do not contain checks.');

            return self::FAILURE;
        }

        $endpointConfig = config('slo.endpoints');
        $evaluations = [];
        $breaches = [];
        $warnings = [];

        foreach ($checks as $check) {
            $path = $check['path'] ?? '';
            $targetMs = (int) ($endpointConfig[$path]['target_ms'] ?? 1500);
            $warningMs = (int) ($endpointConfig[$path]['warning_ms'] ?? max(1, $targetMs - 250));
            $durationMs = (int) ($check['duration_ms'] ?? 0);
            $httpStatus = (int) ($check['status'] ?? 0);
            $consoleErrors = (int) ($check['console_errors'] ?? 0);
            $ok = (bool) ($check['ok'] ?? false);

            $status = 'healthy';
            $reason = 'within_slo';

            if (! $ok || $httpStatus >= 400 || $consoleErrors > 0 || $durationMs > $targetMs) {
                $status = 'breach';
                $reason = 'failed_or_above_target';
                $breaches[] = $path;
            } elseif ($durationMs > $warningMs) {
                $status = 'warning';
                $reason = 'above_warning_threshold';
                $warnings[] = $path;
            }

            $evaluations[] = [
                'path' => $path,
                'status' => $status,
                'reason' => $reason,
                'duration_ms' => $durationMs,
                'target_ms' => $targetMs,
                'warning_ms' => $warningMs,
                'http_status' => $httpStatus,
                'console_errors' => $consoleErrors,
            ];
        }

        if ($this->option('simulate-breach')) {
            $breaches[] = 'simulated-breach';
        }

        $total = count($evaluations);
        $breachCount = count($breaches);
        $errorBudgetRemaining = round(max(0, 100 - (($breachCount / max(1, $total)) * 100)), 2);
        $status = $breachCount > 0 ? 'breach' : (count($warnings) > 0 ? 'warning' : 'healthy');

        $report = [
            'generated_at' => now()->toIso8601String(),
            'status' => $status,
            'error_budget_remaining_percent' => $errorBudgetRemaining,
            'total_checks' => $total,
            'breach_count' => $breachCount,
            'warning_count' => count($warnings),
            'checks' => $evaluations,
            'recommendations' => $this->recommendations($status, $breaches, $warnings),
        ];

        $artifactPath = $this->absolutePath(config('slo.artifact_path'));
        File::ensureDirectoryExists(dirname($artifactPath));
        File::put($artifactPath, json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES).PHP_EOL);

        if ($status === 'breach') {
            $this->error('SLO guardrail breach.');
            $this->line("Error budget remaining: {$errorBudgetRemaining}%");

            return self::FAILURE;
        }

        $this->info("SLO guardrail {$status}.");
        $this->line("Error budget remaining: {$errorBudgetRemaining}%");

        return self::SUCCESS;
    }

    private function absolutePath(string $path): string
    {
        if (str_starts_with($path, '/') || preg_match('/^[A-Za-z]:[\\\\\/]/', $path) === 1) {
            return $path;
        }

        return base_path($path);
    }

    private function recommendations(string $status, array $breaches, array $warnings): array
    {
        if ($status === 'healthy') {
            return ['All critical endpoints are within configured SLO targets.'];
        }

        if ($status === 'warning') {
            return array_map(fn (string $path): string => "Review latency for {$path}; endpoint is above warning threshold.", $warnings);
        }

        return array_map(fn (string $path): string => "Block release or investigate {$path}; endpoint breached SLO target.", $breaches);
    }
}

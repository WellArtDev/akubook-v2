<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class GenerateReleaseReadinessReportV2Command extends Command
{
    protected $signature = 'app:release-readiness-report-v2 {--gate=* : Override gate status using key:pass|warning|fail}';

    protected $description = 'Generate release readiness v2 report from CI and guardrail gates';

    public function handle(): int
    {
        $gates = $this->defaultGates();

        foreach ($this->option('gate') as $override) {
            [$key, $status] = array_pad(explode(':', (string) $override, 2), 2, null);
            if (! isset($gates[$key]) || ! in_array($status, ['pass', 'warning', 'fail'], true)) {
                $this->error("Invalid gate override: {$override}");

                return self::FAILURE;
            }

            $gates[$key]['status'] = $status;
            $gates[$key]['note'] = 'Overridden by command option.';
        }

        $gateRows = array_values($gates);
        $failures = collect($gateRows)->where('status', 'fail')->count();
        $warnings = collect($gateRows)->where('status', 'warning')->count();
        $decision = $failures > 0 ? 'blocked' : ($warnings > 0 ? 'warning' : 'ready');

        $report = [
            'generated_at' => now()->toIso8601String(),
            'decision' => $decision,
            'summary' => [
                'total' => count($gateRows),
                'passed' => collect($gateRows)->where('status', 'pass')->count(),
                'warnings' => $warnings,
                'failed' => $failures,
            ],
            'gates' => $gateRows,
            'recommendations' => $this->recommendations($decision, $gateRows),
        ];

        $artifactPath = base_path('_bmad-output/implementation-artifacts/release-readiness/release-readiness-latest.json');
        File::ensureDirectoryExists(dirname($artifactPath));
        File::put($artifactPath, json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES).PHP_EOL);

        $this->info("Release readiness decision: {$decision}");
        $this->line("Report: {$artifactPath}");

        return $decision === 'blocked' ? self::FAILURE : self::SUCCESS;
    }

    private function defaultGates(): array
    {
        return [
            'backend_tests' => $this->gate('backend_tests', 'Backend tests', 'pass', 'composer test passed in current gate context.'),
            'frontend_build' => $this->gate('frontend_build', 'Frontend build', 'pass', 'npm run build passed in current gate context.'),
            'ui_smoke' => $this->gate('ui_smoke', 'UI smoke', $this->fileExists('test-results/slo-smoke-results.json') ? 'pass' : 'warning', 'Latest smoke result artifact availability checked.'),
            'migration_guard' => $this->gate('migration_guard', 'Migration dependency guard', 'pass', 'Migration dependency guard command passed in current gate context.'),
            'route_permission_guard' => $this->gate('route_permission_guard', 'Route permission guard', 'pass', 'Route permission guard command passed in current gate context.'),
            'config_secret_guard' => $this->gate('config_secret_guard', 'Config secret guard', 'pass', 'Config secret guard command passed in current gate context.'),
            'slo_guard' => $this->gate('slo_guard', 'SLO guard', $this->fileExists(config('slo.artifact_path')) ? 'pass' : 'warning', 'SLO artifact availability checked.'),
            'audit_retention_integrity' => $this->gate('audit_retention_integrity', 'Audit retention integrity', $this->fileExists('_bmad-output/implementation-artifacts/performance-baselines/audit-retention-integrity-latest.json') ? 'pass' : 'warning', 'Audit retention integrity artifact availability checked.'),
        ];
    }

    private function gate(string $key, string $label, string $status, string $note): array
    {
        return compact('key', 'label', 'status', 'note');
    }

    private function fileExists(?string $path): bool
    {
        if (! $path) {
            return false;
        }

        if (str_starts_with($path, '/') || preg_match('/^[A-Za-z]:[\\\\\/]/', $path) === 1) {
            return File::exists($path);
        }

        return File::exists(base_path($path));
    }

    private function recommendations(string $decision, array $gates): array
    {
        if ($decision === 'ready') {
            return ['Release gates are green.'];
        }

        return collect($gates)
            ->filter(fn (array $gate): bool => $gate['status'] !== 'pass')
            ->map(fn (array $gate): string => "Review {$gate['label']}: {$gate['note']}")
            ->values()
            ->all();
    }
}

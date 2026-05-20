<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class GenerateReleaseReadinessReportV2CommandTest extends TestCase
{
    use RefreshDatabase;

    private string $reportPath;

    private array $supportingArtifacts = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->reportPath = base_path('_bmad-output/implementation-artifacts/release-readiness/release-readiness-latest.json');
        $this->supportingArtifacts = [
            base_path('test-results/slo-smoke-results.json'),
            base_path(config('slo.artifact_path')),
            base_path('_bmad-output/implementation-artifacts/performance-baselines/audit-retention-integrity-latest.json'),
        ];

        foreach ($this->supportingArtifacts as $path) {
            File::ensureDirectoryExists(dirname($path));
            File::put($path, json_encode(['ok' => true]).PHP_EOL);
        }
    }

    protected function tearDown(): void
    {
        foreach (array_merge([$this->reportPath], $this->supportingArtifacts) as $path) {
            if (File::exists($path)) {
                File::delete($path);
            }
        }

        parent::tearDown();
    }

    public function test_command_generates_ready_report_when_no_failures(): void
    {
        $this->artisan('app:release-readiness-report-v2')
            ->assertExitCode(0);

        $this->assertTrue(File::exists($this->reportPath));
        $report = json_decode(File::get($this->reportPath), true);

        $this->assertSame('ready', $report['decision']);
        $this->assertArrayHasKey('summary', $report);
        $this->assertArrayHasKey('gates', $report);
    }

    public function test_command_returns_warning_when_gate_warning_exists(): void
    {
        $this->artisan('app:release-readiness-report-v2', [
            '--gate' => ['ui_smoke:warning'],
        ])->assertExitCode(0);

        $report = json_decode(File::get($this->reportPath), true);
        $this->assertSame('warning', $report['decision']);
    }

    public function test_command_fails_when_gate_fails(): void
    {
        $this->artisan('app:release-readiness-report-v2', [
            '--gate' => ['backend_tests:fail'],
        ])->assertExitCode(1);

        $report = json_decode(File::get($this->reportPath), true);
        $this->assertSame('blocked', $report['decision']);
    }
}

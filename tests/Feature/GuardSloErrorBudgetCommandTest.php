<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\File;
use Tests\TestCase;

class GuardSloErrorBudgetCommandTest extends TestCase
{
    private string $resultsPath;

    private string $artifactPath;

    protected function setUp(): void
    {
        parent::setUp();

        $this->resultsPath = storage_path('framework/testing/slo-results.json');
        $this->artifactPath = base_path('_bmad-output/implementation-artifacts/performance-baselines/slo-error-budget-latest.json');
        File::ensureDirectoryExists(dirname($this->resultsPath));
    }

    protected function tearDown(): void
    {
        if (File::exists($this->resultsPath)) {
            File::delete($this->resultsPath);
        }

        if (File::exists($this->artifactPath)) {
            File::delete($this->artifactPath);
        }

        parent::tearDown();
    }

    public function test_guard_reports_healthy_when_checks_are_inside_slo(): void
    {
        File::put($this->resultsPath, json_encode([
            'checks' => [
                ['path' => '/healthz', 'status' => 200, 'duration_ms' => 100, 'console_errors' => 0, 'ok' => true],
                ['path' => '/dashboard', 'status' => 200, 'duration_ms' => 300, 'console_errors' => 0, 'ok' => true],
            ],
        ]));

        $this->artisan('app:guard-slo-error-budget', ['--results' => $this->resultsPath])
            ->expectsOutput('SLO guardrail healthy.')
            ->assertExitCode(0);

        $report = json_decode(File::get($this->artifactPath), true);
        $this->assertSame('healthy', $report['status']);
        $this->assertEquals(100.0, $report['error_budget_remaining_percent']);
    }

    public function test_guard_fails_when_endpoint_breaches_slo(): void
    {
        File::put($this->resultsPath, json_encode([
            'checks' => [
                ['path' => '/healthz', 'status' => 500, 'duration_ms' => 2000, 'console_errors' => 1, 'ok' => false],
            ],
        ]));

        $this->artisan('app:guard-slo-error-budget', ['--results' => $this->resultsPath])
            ->expectsOutput('SLO guardrail breach.')
            ->assertExitCode(1);
    }

    public function test_guard_supports_simulated_breach(): void
    {
        File::put($this->resultsPath, json_encode([
            'checks' => [
                ['path' => '/healthz', 'status' => 200, 'duration_ms' => 100, 'console_errors' => 0, 'ok' => true],
            ],
        ]));

        $this->artisan('app:guard-slo-error-budget', [
            '--results' => $this->resultsPath,
            '--simulate-breach' => true,
        ])->assertExitCode(1);
    }
}

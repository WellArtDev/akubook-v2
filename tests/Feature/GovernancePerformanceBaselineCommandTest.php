<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\ComplianceExportPack;
use App\Models\DataRetentionExecution;
use App\Models\DataRetentionPolicy;
use App\Models\SensitiveAlert;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class GovernancePerformanceBaselineCommandTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    }

    protected function tearDown(): void
    {
        File::deleteDirectory(base_path('_bmad-output/implementation-artifacts/performance-baselines'));

        parent::tearDown();
    }

    public function test_command_saves_baseline_artifact_with_dataset_and_benchmarks(): void
    {
        $dateFrom = now()->subDay()->toDateString();
        $dateTo = now()->toDateString();
        $policy = DataRetentionPolicy::factory()->create(['created_by' => $this->user->id]);

        DataRetentionExecution::query()->create([
            'data_retention_policy_id' => $policy->id,
            'mode' => 'dry-run',
            'entity_type' => 'audit_log',
            'action' => 'delete',
            'cutoff_date' => now()->subMonth()->toDateString(),
            'candidate_count' => 1,
            'processed_count' => 0,
            'status' => 'completed',
            'summary' => ['test' => true],
            'created_by' => $this->user->id,
        ]);

        AuditLog::factory()->create([
            'event_key' => 'workflow.enforcement.evaluated',
            'action' => 'enforced',
            'is_sensitive' => true,
            'sensitivity_level' => 'high',
            'occurred_at' => now(),
        ]);

        SensitiveAlert::query()->create([
            'idempotency_key' => 'baseline:test',
            'window' => '60_minutes',
            'window_start' => now()->subHour(),
            'window_end' => now(),
            'high_count' => 3,
            'threshold' => 3,
            'top_entities' => [['entity_type' => 'sales_order', 'count' => 3]],
            'status' => 'triggered',
            'generated_at' => now(),
            'generated_by' => $this->user->id,
        ]);

        $this->artisan('governance:benchmark-baseline', [
            '--date-from' => $dateFrom,
            '--date-to' => $dateTo,
            '--threshold-ms' => 1500,
        ])->assertExitCode(0);

        $path = base_path("_bmad-output/implementation-artifacts/performance-baselines/governance-baseline-{$dateFrom}_{$dateTo}.json");
        $this->assertFileExists($path);

        $payload = json_decode(File::get($path), true);

        $this->assertSame($dateFrom, $payload['period']['date_from']);
        $this->assertSame($dateTo, $payload['period']['date_to']);
        $this->assertSame(1, $payload['dataset_assumption']['retention_runs']);
        $this->assertSame(1, $payload['dataset_assumption']['enforcement_logs']);
        $this->assertSame(1, $payload['dataset_assumption']['sensitive_alerts']);
        $this->assertArrayHasKey('dashboard_queries', $payload['benchmarks']);
        $this->assertArrayHasKey('compliance_export_generation', $payload['benchmarks']);
        $this->assertNotEmpty($payload['recommendations']);
        $this->assertDatabaseCount('compliance_export_packs', 1);
    }

    public function test_command_can_be_rerun_with_same_options(): void
    {
        $dateFrom = now()->subDay()->toDateString();
        $dateTo = now()->toDateString();

        $options = [
            '--date-from' => $dateFrom,
            '--date-to' => $dateTo,
            '--threshold-ms' => 1,
        ];

        $this->artisan('governance:benchmark-baseline', $options)->assertExitCode(0);
        $this->artisan('governance:benchmark-baseline', $options)->assertExitCode(0);

        $path = base_path("_bmad-output/implementation-artifacts/performance-baselines/governance-baseline-{$dateFrom}_{$dateTo}.json");
        $this->assertFileExists($path);

        $payload = json_decode(File::get($path), true);
        $this->assertSame(1, $payload['threshold_ms']);
        $this->assertDatabaseCount('compliance_export_packs', 2);
    }

    public function test_command_rejects_invalid_threshold(): void
    {
        $this->artisan('governance:benchmark-baseline', [
            '--threshold-ms' => 0,
        ])->assertExitCode(1);
    }
}

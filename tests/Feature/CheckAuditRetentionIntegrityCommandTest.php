<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\DataRetentionExecution;
use App\Models\DataRetentionPolicy;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class CheckAuditRetentionIntegrityCommandTest extends TestCase
{
    use RefreshDatabase;

    private string $artifactPath;

    protected function setUp(): void
    {
        parent::setUp();

        $this->artifactPath = base_path('_bmad-output/implementation-artifacts/performance-baselines/audit-retention-integrity-latest.json');
        $this->actingAs(User::factory()->create());
    }

    protected function tearDown(): void
    {
        if (File::exists($this->artifactPath)) {
            File::delete($this->artifactPath);
        }

        parent::tearDown();
    }

    public function test_integrity_check_is_healthy_when_no_anomaly(): void
    {
        $policy = DataRetentionPolicy::query()->create([
            'policy_key' => 'AUDIT-001',
            'entity_type' => 'audit_log',
            'retention_days' => 30,
            'action' => 'delete',
            'is_active' => true,
            'created_by' => auth()->id(),
            'updated_by' => auth()->id(),
        ]);

        AuditLog::factory()->create([
            'occurred_at' => now()->subDays(5),
            'is_sensitive' => true,
            'sensitivity_level' => 'high',
        ]);

        DataRetentionExecution::query()->create([
            'data_retention_policy_id' => $policy->id,
            'mode' => 'execute',
            'entity_type' => 'audit_log',
            'action' => 'delete',
            'cutoff_date' => now()->subDays(30)->toDateString(),
            'candidate_count' => 1,
            'processed_count' => 1,
            'status' => 'completed',
            'summary' => ['ok' => true],
            'created_by' => auth()->id(),
        ]);

        $this->artisan('app:check-audit-retention-integrity', ['--fail-on' => 'high'])
            ->assertExitCode(0);

        $report = json_decode(File::get($this->artifactPath), true);
        $this->assertSame('healthy', $report['status']);
        $this->assertSame(0, $report['anomaly_count']);
    }

    public function test_integrity_check_fails_on_simulated_high_anomaly(): void
    {
        $this->artisan('app:check-audit-retention-integrity', [
            '--simulate-high-anomaly' => true,
            '--fail-on' => 'high',
        ])->assertExitCode(1);

        $this->assertDatabaseHas('audit_logs', [
            'event_key' => 'audit_retention.integrity_check',
            'entity_type' => 'audit_retention_integrity',
            'action' => 'breach',
            'is_sensitive' => true,
        ]);
    }
}

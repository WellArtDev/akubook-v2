<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\ComplianceExportPack;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GovernanceReliabilityJobsCommandTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    }

    public function test_command_generates_alert_and_export_pack_and_logs_success(): void
    {
        AuditLog::factory()->count(3)->create([
            'is_sensitive' => true,
            'sensitivity_level' => 'high',
            'entity_type' => 'sales_order',
            'occurred_at' => now()->subMinutes(10),
        ]);

        $this->artisan('governance:run-reliability-jobs', [
            '--threshold' => 3,
            '--window-minutes' => 60,
            '--period-start' => now()->toDateString(),
            '--period-end' => now()->toDateString(),
        ])->assertExitCode(0);

        $this->assertDatabaseCount('sensitive_alerts', 1);
        $this->assertDatabaseCount('compliance_export_packs', 1);

        $this->assertDatabaseHas('audit_logs', [
            'event_key' => 'governance.reliability_jobs.completed',
            'entity_type' => 'governance_reliability_job',
            'action' => 'completed',
            'is_sensitive' => true,
            'sensitivity_level' => 'high',
        ]);
    }

    public function test_command_is_idempotent_for_same_window(): void
    {
        AuditLog::factory()->count(3)->create([
            'is_sensitive' => true,
            'sensitivity_level' => 'high',
            'occurred_at' => now()->subMinutes(15),
        ]);

        $options = [
            '--threshold' => 3,
            '--window-minutes' => 60,
            '--period-start' => now()->toDateString(),
            '--period-end' => now()->toDateString(),
        ];

        $this->artisan('governance:run-reliability-jobs', $options)->assertExitCode(0);
        $this->artisan('governance:run-reliability-jobs', $options)->assertExitCode(0);

        $this->assertDatabaseCount('sensitive_alerts', 1);
        $this->assertDatabaseCount('compliance_export_packs', 1);
    }

    public function test_command_logs_failure_when_invalid_options(): void
    {
        $this->artisan('governance:run-reliability-jobs', [
            '--threshold' => 0,
            '--window-minutes' => 0,
        ])->assertExitCode(1);

        $this->assertDatabaseHas('audit_logs', [
            'event_key' => 'governance.reliability_jobs.failed',
            'entity_type' => 'governance_reliability_job',
            'action' => 'failed',
            'is_sensitive' => true,
            'sensitivity_level' => 'high',
        ]);

        $this->assertDatabaseMissing('audit_logs', [
            'event_key' => 'governance.reliability_jobs.completed',
        ]);
    }
}

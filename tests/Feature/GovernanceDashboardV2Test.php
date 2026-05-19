<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\ComplianceExportPack;
use App\Models\DataRetentionExecution;
use App\Models\DataRetentionPolicy;
use App\Models\SensitiveAlert;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class GovernanceDashboardV2Test extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->actingAs($this->user);
        $this->withoutVite();
    }

    public function test_dashboard_page_can_be_opened_with_kpi_shape(): void
    {
        $this->get(route('governance-dashboard-v2.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('GovernanceDashboardV2/Index')
                ->has('filters.date_from')
                ->has('filters.date_to')
                ->has('kpis.retention_runs')
                ->has('kpis.enforcement_count')
                ->has('kpis.sensitive_alerts')
                ->has('kpis.export_packs')
                ->has('trend.retention_runs')
                ->has('latest.retention_run')
                ->has('latest.export_pack')
            );
    }

    public function test_dashboard_counts_governance_data_inside_filter_period(): void
    {
        $policy = DataRetentionPolicy::factory()->create(['created_by' => $this->user->id]);

        DataRetentionExecution::query()->create([
            'data_retention_policy_id' => $policy->id,
            'mode' => 'dry-run',
            'entity_type' => 'audit_log',
            'action' => 'delete',
            'cutoff_date' => now()->subDays(30)->toDateString(),
            'candidate_count' => 5,
            'processed_count' => 0,
            'status' => 'completed',
            'summary' => ['status' => 'completed'],
            'created_by' => $this->user->id,
            'created_at' => now()->subDay(),
            'updated_at' => now()->subDay(),
        ]);

        AuditLog::factory()->create([
            'event_key' => 'workflow.enforcement.evaluated',
            'action' => 'enforced',
            'is_sensitive' => true,
            'sensitivity_level' => 'high',
            'occurred_at' => now()->subDay(),
        ]);

        SensitiveAlert::query()->create([
            'idempotency_key' => 'test-alert',
            'window' => '60_minutes',
            'window_start' => now()->subHours(2),
            'window_end' => now()->subHour(),
            'high_count' => 3,
            'threshold' => 3,
            'top_entities' => [['entity_type' => 'purchase_order', 'count' => 3]],
            'status' => 'triggered',
            'generated_at' => now()->subDay(),
            'generated_by' => $this->user->id,
        ]);

        ComplianceExportPack::query()->create([
            'pack_number' => 'CEP-2026-0001',
            'period_start' => now()->startOfMonth()->toDateString(),
            'period_end' => now()->endOfMonth()->toDateString(),
            'status' => 'generated',
            'record_counts' => ['audit_logs_sensitive' => 1, 'data_retention_executions' => 1, 'workflow_decisions' => 1],
            'metadata' => ['generated_by' => $this->user->id],
            'payload_json' => json_encode(['metadata' => true]),
            'generated_by' => $this->user->id,
            'generated_at' => now()->subDay(),
        ]);

        $this->get(route('governance-dashboard-v2.index', [
            'date_from' => now()->subDays(2)->toDateString(),
            'date_to' => now()->toDateString(),
        ]))->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('kpis.retention_runs', 1)
                ->where('kpis.enforcement_count', 1)
                ->where('kpis.sensitive_alerts', 1)
                ->where('kpis.export_packs', 1)
                ->has('trend.retention_runs', 1)
                ->has('trend.workflow_enforcements', 1)
                ->has('trend.sensitive_alerts', 1)
                ->has('trend.export_packs', 1)
            );
    }

    public function test_dashboard_filter_excludes_out_of_period_data(): void
    {
        $policy = DataRetentionPolicy::factory()->create(['created_by' => $this->user->id]);

        $execution = DataRetentionExecution::query()->create([
            'data_retention_policy_id' => $policy->id,
            'mode' => 'dry-run',
            'entity_type' => 'audit_log',
            'action' => 'delete',
            'cutoff_date' => now()->subDays(30)->toDateString(),
            'candidate_count' => 5,
            'processed_count' => 0,
            'status' => 'completed',
            'summary' => ['status' => 'completed'],
            'created_by' => $this->user->id,
        ]);
        $execution->forceFill([
            'created_at' => now()->subDays(10),
            'updated_at' => now()->subDays(10),
        ])->save();

        $this->get(route('governance-dashboard-v2.index', [
            'date_from' => now()->subDays(2)->toDateString(),
            'date_to' => now()->toDateString(),
        ]))->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('kpis.retention_runs', 0)
                ->where('kpis.enforcement_count', 0)
                ->where('kpis.sensitive_alerts', 0)
                ->where('kpis.export_packs', 0)
            );
    }
}

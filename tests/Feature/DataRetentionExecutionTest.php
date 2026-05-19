<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\DataRetentionPolicy;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class DataRetentionExecutionTest extends TestCase
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

    public function test_dry_run_creates_execution_without_deleting_candidates(): void
    {
        $policy = DataRetentionPolicy::factory()->create([
            'entity_type' => 'audit_log',
            'retention_days' => 30,
            'action' => 'delete',
            'is_active' => true,
            'created_by' => $this->user->id,
        ]);

        $oldLog = $this->auditLog(now()->subDays(45));
        $this->auditLog(now()->subDays(10));

        $this->post(route('data-retention-executions.store'), [
            'policy_id' => $policy->id,
            'mode' => 'dry-run',
        ])->assertRedirect();

        $this->assertDatabaseHas('data_retention_executions', [
            'data_retention_policy_id' => $policy->id,
            'mode' => 'dry-run',
            'candidate_count' => 1,
            'processed_count' => 0,
            'action' => 'delete',
            'status' => 'completed',
        ]);
        $this->assertDatabaseHas('audit_logs', ['id' => $oldLog->id]);
        $this->assertDatabaseHas('audit_logs', [
            'event_key' => 'data_retention.execution.run',
            'is_sensitive' => true,
            'sensitivity_level' => 'high',
        ]);
    }

    public function test_execute_delete_removes_audit_log_candidates(): void
    {
        $policy = DataRetentionPolicy::factory()->create([
            'entity_type' => 'audit_log',
            'retention_days' => 30,
            'action' => 'delete',
            'is_active' => true,
            'created_by' => $this->user->id,
        ]);

        $oldLog = $this->auditLog(now()->subDays(45));
        $recentLog = $this->auditLog(now()->subDays(10));

        $this->post(route('data-retention-executions.store'), [
            'policy_id' => $policy->id,
            'mode' => 'execute',
        ])->assertRedirect();

        $this->assertDatabaseMissing('audit_logs', ['id' => $oldLog->id]);
        $this->assertDatabaseHas('audit_logs', ['id' => $recentLog->id]);
        $this->assertDatabaseHas('data_retention_executions', [
            'data_retention_policy_id' => $policy->id,
            'mode' => 'execute',
            'candidate_count' => 1,
            'processed_count' => 1,
        ]);
    }

    public function test_execution_index_and_show_pages_can_be_opened(): void
    {
        $policy = DataRetentionPolicy::factory()->create([
            'entity_type' => 'audit_log',
            'action' => 'delete',
            'is_active' => true,
            'created_by' => $this->user->id,
        ]);

        $this->post(route('data-retention-executions.store'), [
            'policy_id' => $policy->id,
            'mode' => 'dry-run',
        ]);

        $executionId = DB::table('data_retention_executions')->value('id');

        $this->get(route('data-retention-executions.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('DataRetentionExecutions/Index')
                ->has('executions.data')
            );

        $this->get(route('data-retention-executions.show', $executionId))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('DataRetentionExecutions/Show')
                ->where('execution.id', $executionId)
            );
    }

    private function auditLog($occurredAt): AuditLog
    {
        return AuditLog::create([
            'user_id' => $this->user->id,
            'auditable_type' => 'salary_component',
            'auditable_id' => 10,
            'event' => 'update',
            'old_values' => ['x' => 1],
            'new_values' => ['x' => 2],
            'ip_address' => '127.0.0.1',
            'user_agent' => 'phpunit',
            'event_key' => 'salary_component.updated',
            'entity_type' => 'salary_component',
            'entity_id' => 10,
            'action' => 'update',
            'actor_user_id' => $this->user->id,
            'occurred_at' => $occurredAt,
            'metadata' => ['source' => 'test'],
        ]);
    }
}

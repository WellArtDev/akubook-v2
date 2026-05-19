<?php

namespace Tests\Feature;

use App\Models\ApprovalWorkflow;
use App\Models\AuditLog;
use App\Models\DataRetentionPolicy;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class ComplianceReportTest extends TestCase
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

    public function test_compliance_report_page_can_be_opened(): void
    {
        $this->get(route('compliance-reports.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('ComplianceReports/Index')
                ->has('summary')
                ->has('auditByEntity')
                ->has('sensitiveByLevel')
                ->has('retentionByAction')
            );
    }

    public function test_compliance_report_summarizes_metrics_and_breakdowns(): void
    {
        AuditLog::factory()->create([
            'event_key' => 'salary_component.deleted',
            'entity_type' => 'salary_component',
            'is_sensitive' => true,
            'sensitivity_level' => 'high',
            'occurred_at' => '2026-05-10 10:00:00',
        ]);

        AuditLog::factory()->create([
            'event_key' => 'voucher.cancelled',
            'entity_type' => 'voucher',
            'is_sensitive' => true,
            'sensitivity_level' => 'critical',
            'occurred_at' => '2026-05-11 10:00:00',
        ]);

        AuditLog::factory()->create([
            'event_key' => 'salary_component.updated',
            'entity_type' => 'salary_component',
            'is_sensitive' => false,
            'sensitivity_level' => null,
            'occurred_at' => '2026-05-12 10:00:00',
        ]);

        DataRetentionPolicy::factory()->create([
            'policy_key' => 'ret-audit',
            'action' => 'archive',
            'is_active' => true,
        ]);

        ApprovalWorkflow::factory()->create([
            'workflow_key' => 'wf-voucher',
            'is_active' => true,
        ]);

        $this->get(route('compliance-reports.index', [
            'date_from' => '2026-05-01',
            'date_to' => '2026-05-31',
        ]))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('ComplianceReports/Index')
                ->where('summary.audit_logs', 3)
                ->where('summary.sensitive_actions', 2)
                ->where('summary.active_retention_policies', 1)
                ->where('summary.active_approval_workflows', 1)
                ->where('auditByEntity.0.entity_type', 'salary_component')
                ->where('sensitiveByLevel.0.sensitivity_level', 'critical')
                ->where('retentionByAction.0.action', 'archive')
            );
    }

    public function test_compliance_report_respects_date_filter(): void
    {
        AuditLog::factory()->create([
            'event_key' => 'old.event',
            'occurred_at' => '2026-04-01 10:00:00',
            'is_sensitive' => true,
            'sensitivity_level' => 'high',
        ]);

        $this->get(route('compliance-reports.index', [
            'date_from' => '2026-05-01',
            'date_to' => '2026-05-31',
        ]))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('ComplianceReports/Index')
                ->where('summary.audit_logs', 0)
                ->where('summary.sensitive_actions', 0)
            );
    }
}

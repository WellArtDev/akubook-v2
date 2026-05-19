<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\ComplianceExportPack;
use App\Models\DataRetentionExecution;
use App\Models\DataRetentionPolicy;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class ComplianceExportPackTest extends TestCase
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

    public function test_generate_creates_pack_with_metadata_and_audit_log(): void
    {
        $periodStart = now()->startOfMonth()->toDateString();
        $periodEnd = now()->endOfMonth()->toDateString();

        AuditLog::factory()->count(2)->create([
            'is_sensitive' => true,
            'sensitivity_level' => 'high',
            'occurred_at' => now()->subDays(1),
        ]);

        $policy = DataRetentionPolicy::factory()->create([
            'created_by' => $this->user->id,
        ]);

        DataRetentionExecution::query()->create([
            'data_retention_policy_id' => $policy->id,
            'mode' => 'dry-run',
            'entity_type' => 'audit_log',
            'action' => 'delete',
            'cutoff_date' => now()->subDays(30)->toDateString(),
            'candidate_count' => 3,
            'processed_count' => 0,
            'status' => 'completed',
            'summary' => ['note' => 'test'],
            'created_by' => $this->user->id,
            'created_at' => now()->subHours(2),
            'updated_at' => now()->subHours(2),
        ]);

        AuditLog::factory()->create([
            'event_key' => 'workflow.enforcement.evaluated',
            'entity_type' => 'purchase_order',
            'is_sensitive' => true,
            'sensitivity_level' => 'high',
            'occurred_at' => now()->subHours(3),
        ]);

        $this->post(route('compliance-export-packs.store'), [
            'period_start' => $periodStart,
            'period_end' => $periodEnd,
        ])->assertRedirect();

        $pack = ComplianceExportPack::query()->first();
        $this->assertNotNull($pack);
        $this->assertSame($periodStart, $pack->period_start->toDateString());
        $this->assertSame($periodEnd, $pack->period_end->toDateString());
        $this->assertSame('generated', $pack->status);
        $this->assertSame(3, $pack->record_counts['audit_logs_sensitive']);
        $this->assertSame(1, $pack->record_counts['data_retention_executions']);
        $this->assertSame(1, $pack->record_counts['workflow_decisions']);
        $this->assertStringContainsString('"metadata"', $pack->payload_json);

        $this->assertDatabaseHas('audit_logs', [
            'event_key' => 'compliance_export_pack.generated',
            'entity_type' => 'compliance_export_pack',
            'entity_id' => $pack->id,
            'is_sensitive' => true,
            'sensitivity_level' => 'high',
        ]);
    }

    public function test_index_and_show_pages_can_be_opened(): void
    {
        $pack = ComplianceExportPack::query()->create([
            'pack_number' => 'CEP-2026-0001',
            'period_start' => now()->startOfMonth()->toDateString(),
            'period_end' => now()->endOfMonth()->toDateString(),
            'status' => 'generated',
            'record_counts' => [
                'audit_logs_sensitive' => 1,
                'data_retention_executions' => 0,
                'workflow_decisions' => 0,
            ],
            'metadata' => [
                'generated_at' => now()->toIso8601String(),
                'generated_by' => $this->user->id,
            ],
            'payload_json' => json_encode(['metadata' => ['sample' => true]], JSON_PRETTY_PRINT),
            'generated_by' => $this->user->id,
            'generated_at' => now(),
        ]);

        $this->get(route('compliance-export-packs.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('ComplianceExportPacks/Index')
                ->has('packs.data', 1)
                ->where('packs.data.0.pack_number', 'CEP-2026-0001')
            );

        $this->get(route('compliance-export-packs.show', $pack))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('ComplianceExportPacks/Show')
                ->where('pack.pack_number', 'CEP-2026-0001')
            );
    }

    public function test_download_returns_json_payload(): void
    {
        $pack = ComplianceExportPack::query()->create([
            'pack_number' => 'CEP-2026-0002',
            'period_start' => now()->startOfMonth()->toDateString(),
            'period_end' => now()->endOfMonth()->toDateString(),
            'status' => 'generated',
            'record_counts' => [
                'audit_logs_sensitive' => 0,
                'data_retention_executions' => 0,
                'workflow_decisions' => 0,
            ],
            'metadata' => [
                'generated_at' => now()->toIso8601String(),
            ],
            'payload_json' => json_encode(['hello' => 'world'], JSON_PRETTY_PRINT),
            'generated_by' => $this->user->id,
            'generated_at' => now(),
        ]);

        $this->get(route('compliance-export-packs.download', $pack))
            ->assertOk()
            ->assertHeader('content-type', 'application/json');
    }
}

<?php

namespace Tests\Feature;

use App\Models\DataRetentionPolicy;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class DataRetentionPolicyTest extends TestCase
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

    public function test_policy_index_page_can_be_opened(): void
    {
        DataRetentionPolicy::factory()->create(['created_by' => $this->user->id]);

        $this->get(route('data-retention-policies.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('DataRetentionPolicies/Index')
                ->has('policies.data')
            );
    }

    public function test_user_can_create_policy(): void
    {
        $this->post(route('data-retention-policies.store'), [
            'policy_key' => 'RET-AUDIT-001',
            'entity_type' => 'audit_log',
            'retention_days' => 365,
            'action' => 'archive',
            'is_active' => true,
            'description' => 'Retention for audit logs',
        ])->assertRedirect();

        $this->assertDatabaseHas('data_retention_policies', [
            'policy_key' => 'RET-AUDIT-001',
            'entity_type' => 'audit_log',
            'retention_days' => 365,
            'action' => 'archive',
            'created_by' => $this->user->id,
        ]);
    }

    public function test_policy_key_must_be_unique(): void
    {
        DataRetentionPolicy::factory()->create([
            'policy_key' => 'RET-UNIQ-001',
            'created_by' => $this->user->id,
        ]);

        $this->post(route('data-retention-policies.store'), [
            'policy_key' => 'RET-UNIQ-001',
            'entity_type' => 'audit_log',
            'retention_days' => 90,
            'action' => 'delete',
            'is_active' => true,
        ])->assertSessionHasErrors(['policy_key']);
    }

    public function test_policy_show_displays_preview_candidate_count(): void
    {
        $policy = DataRetentionPolicy::factory()->create([
            'policy_key' => 'RET-AUDIT-PRV',
            'entity_type' => 'audit_log',
            'retention_days' => 30,
            'created_by' => $this->user->id,
        ]);

        DB::table('audit_logs')->insert([
            'user_id' => $this->user->id,
            'auditable_type' => 'salary_component',
            'auditable_id' => 10,
            'event' => 'update',
            'old_values' => json_encode(['x' => 1]),
            'new_values' => json_encode(['x' => 2]),
            'ip_address' => '127.0.0.1',
            'user_agent' => 'phpunit',
            'event_key' => 'salary_component.updated',
            'entity_type' => 'salary_component',
            'entity_id' => 10,
            'action' => 'update',
            'actor_user_id' => $this->user->id,
            'occurred_at' => now()->subDays(45),
            'metadata' => json_encode(['source' => 'test']),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->get(route('data-retention-policies.show', $policy))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('DataRetentionPolicies/Show')
                ->where('preview.candidate_count', 1)
            );
    }
}

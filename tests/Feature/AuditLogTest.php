<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\SalaryComponent;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class AuditLogTest extends TestCase
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

    public function test_audit_log_index_can_be_opened(): void
    {
        AuditLog::factory()->create(['actor_user_id' => $this->user->id]);

        $this->get(route('audit-logs.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('AuditLogs/Index')
                ->has('logs.data')
                ->has('users')
            );
    }

    public function test_salary_component_create_writes_audit_log(): void
    {
        $this->post(route('salary-components.store'), [
            'code' => 'BASIC-AUDIT',
            'name' => 'Basic Audit',
            'component_type' => 'earning',
            'calculation_method' => 'fixed',
            'default_amount' => 1000000,
            'default_percentage' => 0,
            'is_taxable' => true,
            'is_active' => true,
            'description' => 'Audit test',
        ])->assertRedirect();

        $component = SalaryComponent::where('code', 'BASIC-AUDIT')->firstOrFail();

        $this->assertDatabaseHas('audit_logs', [
            'event_key' => 'salary_component.created',
            'entity_type' => 'salary_component',
            'entity_id' => $component->id,
            'action' => 'create',
            'actor_user_id' => $this->user->id,
        ]);
    }

    public function test_salary_component_update_and_delete_write_audit_logs(): void
    {
        $component = SalaryComponent::factory()->create([
            'code' => 'AUDIT-UPD',
            'name' => 'Audit Update',
        ]);

        $this->put(route('salary-components.update', $component), [
            'code' => 'AUDIT-UPD',
            'name' => 'Audit Updated',
            'component_type' => 'earning',
            'calculation_method' => 'fixed',
            'default_amount' => 2000000,
            'default_percentage' => 0,
            'is_taxable' => true,
            'is_active' => true,
            'description' => 'Updated',
        ])->assertRedirect();

        $this->delete(route('salary-components.destroy', $component))->assertRedirect();

        $this->assertDatabaseHas('audit_logs', [
            'event_key' => 'salary_component.updated',
            'entity_id' => $component->id,
            'action' => 'update',
        ]);
        $this->assertDatabaseHas('audit_logs', [
            'event_key' => 'salary_component.deleted',
            'entity_id' => $component->id,
            'action' => 'delete',
        ]);
    }

    public function test_audit_log_index_filters_by_event_key(): void
    {
        AuditLog::factory()->create(['event_key' => 'salary_component.created']);
        AuditLog::factory()->create(['event_key' => 'salary_component.deleted']);

        $this->get(route('audit-logs.index', ['event_key' => 'salary_component.created']))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('AuditLogs/Index')
                ->where('logs.data.0.event_key', 'salary_component.created')
            );
    }
}

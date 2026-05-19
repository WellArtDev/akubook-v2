<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\SensitiveAlert;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class SensitiveAlertTest extends TestCase
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

    public function test_generate_creates_alert_when_high_threshold_reached(): void
    {
        AuditLog::factory()->count(3)->create([
            'is_sensitive' => true,
            'sensitivity_level' => 'high',
            'entity_type' => 'sales_order',
            'occurred_at' => now()->subMinutes(10),
        ]);

        $this->post(route('sensitive-alerts.store'), [
            'threshold' => 3,
            'window_minutes' => 60,
        ])->assertRedirect(route('sensitive-alerts.index'));

        $alert = SensitiveAlert::query()->first();
        $this->assertNotNull($alert);
        $this->assertSame(3, $alert->high_count);
        $this->assertSame(3, $alert->threshold);
        $this->assertSame('triggered', $alert->status);
        $this->assertNotEmpty($alert->top_entities);

        $this->assertDatabaseHas('audit_logs', [
            'event_key' => 'sensitive_alert.generated',
            'entity_type' => 'sensitive_alert',
            'entity_id' => $alert->id,
            'is_sensitive' => true,
            'sensitivity_level' => 'high',
        ]);
    }

    public function test_generate_does_not_create_alert_when_below_threshold(): void
    {
        AuditLog::factory()->count(2)->create([
            'is_sensitive' => true,
            'sensitivity_level' => 'high',
            'occurred_at' => now()->subMinutes(5),
        ]);

        $this->post(route('sensitive-alerts.store'), [
            'threshold' => 3,
            'window_minutes' => 60,
        ])->assertRedirect(route('sensitive-alerts.index'));

        $this->assertDatabaseCount('sensitive_alerts', 0);
        $this->assertDatabaseMissing('audit_logs', [
            'event_key' => 'sensitive_alert.generated',
        ]);
    }

    public function test_generate_is_idempotent_for_same_window_and_threshold(): void
    {
        AuditLog::factory()->count(3)->create([
            'is_sensitive' => true,
            'sensitivity_level' => 'high',
            'occurred_at' => now()->subMinutes(20),
        ]);

        $this->post(route('sensitive-alerts.store'), [
            'threshold' => 3,
            'window_minutes' => 60,
        ])->assertRedirect(route('sensitive-alerts.index'));

        $this->post(route('sensitive-alerts.store'), [
            'threshold' => 3,
            'window_minutes' => 60,
        ])->assertRedirect(route('sensitive-alerts.index'));

        $this->assertDatabaseCount('sensitive_alerts', 1);
    }

    public function test_sensitive_alert_index_can_be_opened(): void
    {
        SensitiveAlert::create([
            'idempotency_key' => 'high:test:3',
            'window' => '60_minutes',
            'window_start' => now()->subHour(),
            'window_end' => now(),
            'high_count' => 5,
            'threshold' => 3,
            'top_entities' => [['entity_type' => 'sales_order', 'count' => 3]],
            'status' => 'triggered',
            'generated_at' => now(),
            'generated_by' => $this->user->id,
        ]);

        $this->get(route('sensitive-alerts.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('SensitiveAlerts/Index')
                ->has('alerts.data', 1)
                ->where('alerts.data.0.status', 'triggered')
            );
    }
}

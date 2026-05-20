<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class AdminActivityReviewTest extends TestCase
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

    public function test_page_loads_with_expected_shape(): void
    {
        AuditLog::factory()->create([
            'is_sensitive' => true,
            'event_key' => 'security.login.failed',
            'entity_type' => 'user',
            'entity_id' => 1,
            'action' => 'failed',
            'sensitivity_level' => 'high',
            'actor_user_id' => $this->user->id,
            'metadata' => ['ip' => '127.0.0.1'],
            'occurred_at' => now(),
        ]);

        $response = $this->get(route('admin-activity-review.index'));

        $response->assertInertia(fn (Assert $page) => $page
            ->component('AdminActivityReview/Index')
            ->has('kpis')
            ->has('activities.data', 1)
            ->has('filters')
            ->has('users')
            ->has('eventKeys')
            ->has('entityTypes')
            ->has('levels'));
    }

    public function test_metadata_is_masked_for_sensitive_keys(): void
    {
        AuditLog::factory()->create([
            'is_sensitive' => true,
            'event_key' => 'api.key.updated',
            'entity_type' => 'integration',
            'entity_id' => 9,
            'action' => 'updated',
            'sensitivity_level' => 'high',
            'actor_user_id' => $this->user->id,
            'metadata' => [
                'api_key' => 'should-not-leak',
                'nested' => [
                    'token' => 'abc123',
                    'safe' => 'visible',
                ],
            ],
            'occurred_at' => now(),
        ]);

        $response = $this->get(route('admin-activity-review.index'));

        $response->assertInertia(fn (Assert $page) => $page
            ->where('activities.data.0.metadata.api_key', '***')
            ->where('activities.data.0.metadata.nested.token', '***')
            ->where('activities.data.0.metadata.nested.safe', 'visible'));
    }
}

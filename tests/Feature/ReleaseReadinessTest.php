<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReleaseReadinessTest extends TestCase
{
    use RefreshDatabase;

    public function test_release_readiness_page_can_be_opened(): void
    {
        $this->withoutVite();
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('release-readiness.index'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('ReleaseReadiness/Index')
                ->has('checks')
                ->has('summary')
                ->where('summary.total', 7)
            );
    }

    public function test_release_readiness_includes_core_route_checks(): void
    {
        $this->withoutVite();
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('release-readiness.index'))
            ->assertInertia(fn ($page) => $page
                ->component('ReleaseReadiness/Index')
                ->where('checks.0.key', 'pwa.manifest')
                ->where('checks.1.key', 'pwa.service-worker')
                ->where('checks.2.key', 'security-audit.index')
            );
    }
}

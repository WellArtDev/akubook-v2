<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SecurityAuditTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();

        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    }

    public function test_security_audit_page_can_be_opened(): void
    {
        $this->get(route('security-audit.index'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('SecurityAudit/Index')
                ->has('summary.total_routes')
                ->has('summary.public_routes')
                ->has('summary.unprotected_mutations')
            );
    }

    public function test_public_and_unprotected_mutation_summaries_exist(): void
    {
        $response = $this->get(route('security-audit.index'));
        $response->assertOk();

        $payload = $response->viewData('page')['props'];

        $this->assertArrayHasKey('allowedPublicRoutes', $payload);
        $this->assertArrayHasKey('unexpectedPublicRoutes', $payload);
        $this->assertArrayHasKey('unprotectedMutations', $payload);
        $this->assertArrayHasKey('summary', $payload);
        $this->assertIsInt($payload['summary']['unprotected_mutations']);
    }
}

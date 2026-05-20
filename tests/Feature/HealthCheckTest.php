<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HealthCheckTest extends TestCase
{
    use RefreshDatabase;

    public function test_health_endpoint_returns_ok_status(): void
    {
        $response = $this->getJson('/healthz');

        $response->assertOk()
            ->assertJson([
                'status' => 'ok',
                'app' => 'ok',
                'database' => 'ok',
            ])
            ->assertJsonStructure([
                'timestamp',
                'duration_ms',
            ]);
    }
}

<?php

namespace Tests\Feature;

use Tests\TestCase;

class GuardRoutePermissionMatrixCommandTest extends TestCase
{
    public function test_route_permission_matrix_guardrail_passes(): void
    {
        $this->artisan('app:guard-route-permissions')
            ->expectsOutput('Route permission matrix guardrail passed.')
            ->assertExitCode(0);
    }

    public function test_route_permission_matrix_guardrail_fails_on_simulated_missing_map(): void
    {
        $this->artisan('app:guard-route-permissions', ['--simulate-missing-map' => true])
            ->expectsOutput('Route permission matrix guardrail failed:')
            ->assertExitCode(1);
    }
}

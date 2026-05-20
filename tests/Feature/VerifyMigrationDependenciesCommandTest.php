<?php

namespace Tests\Feature;

use Tests\TestCase;

class VerifyMigrationDependenciesCommandTest extends TestCase
{
    public function test_migration_dependency_guard_passes_on_fresh_path(): void
    {
        $this->artisan('app:verify-migration-dependencies')
            ->expectsOutput('Migration dependency guard passed.')
            ->assertExitCode(0);
    }

    public function test_migration_dependency_guard_fails_on_simulated_missing_parent(): void
    {
        $this->artisan('app:verify-migration-dependencies', ['--simulate-missing-parent' => true])
            ->expectsOutput('Migration dependency guard failed:')
            ->assertExitCode(1);
    }
}

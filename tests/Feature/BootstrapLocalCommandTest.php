<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class BootstrapLocalCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_bootstrap_creates_admin_role_permissions_and_user(): void
    {
        $this->artisan('app:bootstrap-local')
            ->expectsOutput('Local bootstrap completed.')
            ->assertExitCode(0);

        $user = User::where('email', 'admin@akubook.com')->firstOrFail();

        $this->assertTrue(Hash::check('password', $user->password));
        $this->assertNotNull($user->email_verified_at);
        $this->assertTrue($user->hasRole('Administrator'));
        $this->assertDatabaseHas('roles', ['name' => 'Administrator']);
        $this->assertDatabaseHas('permissions', ['name' => 'governance.view']);
    }

    public function test_bootstrap_is_idempotent(): void
    {
        $this->artisan('app:bootstrap-local')->assertExitCode(0);
        $this->artisan('app:bootstrap-local')->assertExitCode(0);

        $this->assertSame(1, User::where('email', 'admin@akubook.com')->count());
        $this->assertSame(1, Role::where('name', 'Administrator')->count());
    }

    public function test_bootstrap_accepts_custom_admin_credentials(): void
    {
        $this->artisan('app:bootstrap-local', [
            '--email' => 'admin@example.test',
            '--password' => 'secret-password',
        ])->assertExitCode(0);

        $user = User::where('email', 'admin@example.test')->firstOrFail();

        $this->assertTrue(Hash::check('secret-password', $user->password));
        $this->assertTrue($user->hasRole('Administrator'));
    }
}

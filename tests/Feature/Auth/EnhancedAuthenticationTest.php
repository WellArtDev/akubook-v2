<?php

namespace Tests\Feature\Auth;

use App\Models\AuditLog;
use App\Models\Branch;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class EnhancedAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test registration requires branch selection
     */
    public function test_registration_requires_branch(): void
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            // branch_id missing
        ]);

        $response->assertSessionHasErrors('branch_id');
    }

    /**
     * Test registration with valid branch succeeds
     */
    public function test_registration_with_branch_succeeds(): void
    {
        $branch = Branch::factory()->create();

        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'branch_id' => $branch->id,
        ]);

        $response->assertRedirect('/dashboard');
        
        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
            'branch_id' => $branch->id,
        ]);
    }

    /**
     * Test registration with invalid branch fails
     */
    public function test_registration_with_invalid_branch_fails(): void
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'branch_id' => 99999, // Non-existent branch
        ]);

        $response->assertSessionHasErrors('branch_id');
    }

    /**
     * Test password must meet complexity requirements
     */
    public function test_password_must_meet_complexity_requirements(): void
    {
        $branch = Branch::factory()->create();

        // Test weak password (no uppercase)
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test1@example.com',
            'password' => 'password123!',
            'password_confirmation' => 'password123!',
            'branch_id' => $branch->id,
        ]);
        $response->assertSessionHasErrors('password');

        // Test weak password (no lowercase)
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test2@example.com',
            'password' => 'PASSWORD123!',
            'password_confirmation' => 'PASSWORD123!',
            'branch_id' => $branch->id,
        ]);
        $response->assertSessionHasErrors('password');

        // Test weak password (no number)
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test3@example.com',
            'password' => 'Password!',
            'password_confirmation' => 'Password!',
            'branch_id' => $branch->id,
        ]);
        $response->assertSessionHasErrors('password');

        // Test weak password (no special character)
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test4@example.com',
            'password' => 'Password123',
            'password_confirmation' => 'Password123',
            'branch_id' => $branch->id,
        ]);
        $response->assertSessionHasErrors('password');

        // Test weak password (too short)
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test5@example.com',
            'password' => 'Pass1!',
            'password_confirmation' => 'Pass1!',
            'branch_id' => $branch->id,
        ]);
        $response->assertSessionHasErrors('password');
    }

    /**
     * Test strong password is accepted
     */
    public function test_strong_password_is_accepted(): void
    {
        $branch = Branch::factory()->create();

        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'branch_id' => $branch->id,
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticated();
    }

    /**
     * Test successful login creates audit log
     */
    public function test_successful_login_creates_audit_log(): void
    {
        $branch = Branch::factory()->create();
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('Password123!'),
            'branch_id' => $branch->id,
        ]);

        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'Password123!',
        ]);

        $response->assertRedirect('/dashboard');
        
        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $user->id,
            'event' => 'login',
            'auditable_type' => 'App\Models\User',
            'auditable_id' => $user->id,
        ]);
    }

    /**
     * Test failed login creates audit log
     */
    public function test_failed_login_creates_audit_log(): void
    {
        $response = $this->post('/login', [
            'email' => 'nonexistent@example.com',
            'password' => 'WrongPassword123!',
        ]);

        $response->assertSessionHasErrors('email');
        
        $this->assertDatabaseHas('audit_logs', [
            'user_id' => null,
            'event' => 'login_failed',
        ]);
    }

    /**
     * Test logout creates audit log
     */
    public function test_logout_creates_audit_log(): void
    {
        $branch = Branch::factory()->create();
        $user = User::factory()->create([
            'branch_id' => $branch->id,
        ]);

        $this->actingAs($user);

        $response = $this->post('/logout');

        $response->assertRedirect('/');
        
        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $user->id,
            'event' => 'logout',
        ]);
    }

    /**
     * Test registration creates audit log
     */
    public function test_registration_creates_audit_log(): void
    {
        $branch = Branch::factory()->create();

        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'branch_id' => $branch->id,
        ]);

        $response->assertRedirect('/dashboard');
        
        $user = User::where('email', 'test@example.com')->first();
        
        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $user->id,
            'event' => 'registration',
            'auditable_type' => 'App\Models\User',
            'auditable_id' => $user->id,
        ]);
    }

    /**
     * Test audit log includes IP address and user agent
     */
    public function test_audit_log_includes_ip_and_user_agent(): void
    {
        $branch = Branch::factory()->create();
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('Password123!'),
            'branch_id' => $branch->id,
        ]);

        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'Password123!',
        ]);

        $auditLog = AuditLog::where('user_id', $user->id)
            ->where('event', 'login')
            ->first();

        $this->assertNotNull($auditLog);
        $this->assertNotNull($auditLog->ip_address);
        $this->assertNotNull($auditLog->user_agent);
    }

    /**
     * Test user can access branch information after login
     */
    public function test_user_can_access_branch_after_login(): void
    {
        $branch = Branch::factory()->create(['name' => 'Test Branch']);
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('Password123!'),
            'branch_id' => $branch->id,
        ]);

        $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'Password123!',
        ]);

        $this->assertAuthenticated();
        $this->assertEquals('Test Branch', auth()->user()->branch->name);
    }

    /**
     * Test branches API endpoint returns active branches
     */
    public function test_branches_api_returns_active_branches(): void
    {
        Branch::factory()->create(['name' => 'Active Branch', 'is_active' => true]);
        Branch::factory()->create(['name' => 'Inactive Branch', 'is_active' => false]);

        $response = $this->get('/api/branches');

        $response->assertStatus(200);
        $response->assertJsonCount(1);
        $response->assertJsonFragment(['name' => 'Active Branch']);
        $response->assertJsonMissing(['name' => 'Inactive Branch']);
    }

    /**
     * Test Indonesian locale is set
     */
    public function test_indonesian_locale_is_set(): void
    {
        $this->assertEquals('id', config('app.locale'));
    }

    /**
     * Test Indonesian translations are loaded
     */
    public function test_indonesian_translations_are_loaded(): void
    {
        $this->assertEquals(
            'Kredensial yang Anda masukkan tidak cocok dengan data kami.',
            __('auth.failed')
        );
    }
}

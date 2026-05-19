<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\CashAccount;
use App\Models\DashboardPreference;
use App\Models\User;
use App\Models\Voucher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_page_can_be_opened(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('role-dashboard.index'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page->component('Dashboards/RoleIndex'));
    }

    public function test_finance_email_resolves_finance_role(): void
    {
        $user = User::factory()->create(['email' => 'finance.team@example.com']);

        $this->actingAs($user)
            ->get(route('role-dashboard.index'))
            ->assertInertia(fn ($page) => $page
                ->where('role', 'finance')
                ->has('widgets', 3)
            );
    }

    public function test_unknown_email_falls_back_to_general_role(): void
    {
        $user = User::factory()->create(['email' => 'someone@example.com']);

        $this->actingAs($user)
            ->get(route('role-dashboard.index'))
            ->assertInertia(fn ($page) => $page
                ->where('role', 'general')
                ->has('widgets', 3)
            );
    }

    public function test_metrics_endpoint_returns_refresh_payload(): void
    {
        $user = User::factory()->create(['email' => 'hr.team@example.com']);

        $this->actingAs($user)
            ->getJson(route('role-dashboard.metrics'))
            ->assertOk()
            ->assertJsonPath('role', 'hr')
            ->assertJsonPath('refresh_seconds', 60)
            ->assertJsonCount(3, 'widgets')
            ->assertJsonStructure(['generated_at']);
    }

    public function test_metrics_widgets_include_drilldown_metadata(): void
    {
        $user = User::factory()->create(['email' => 'finance.team@example.com']);

        $response = $this->actingAs($user)->getJson(route('role-dashboard.metrics'));

        $response->assertOk()->assertJsonPath('role', 'finance');
        $widgets = $response->json('widgets');

        $this->assertNotEmpty($widgets);
        $this->assertArrayHasKey('widget_key', $widgets[0]);
        $this->assertArrayHasKey('drilldown_route', $widgets[0]);
    }

    public function test_drilldown_page_can_be_opened_for_cash_receipts(): void
    {
        $user = User::factory()->create(['email' => 'finance.team@example.com']);
        $account = Account::factory()->create([
            'type' => 'asset',
            'category' => 'current_asset',
            'is_header' => false,
            'is_active' => true,
        ]);
        $cashAccount = CashAccount::factory()->create(['account_id' => $account->id]);

        Voucher::factory()->create([
            'voucher_type' => 'receipt',
            'status' => 'posted',
            'amount' => 100000,
            'cash_bank_type' => 'cash',
            'cash_bank_account_id' => $cashAccount->id,
            'counterpart_account_id' => $account->id,
            'created_by' => $user->id,
        ]);

        $this->actingAs($user)
            ->get(route('role-dashboard.drilldown', 'cash_receipts'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Dashboards/Drilldown')
                ->where('widget', 'cash_receipts')
                ->where('title', 'Cash Receipts')
                ->has('rows', 1)
            );
    }

    public function test_metrics_uses_saved_refresh_preference(): void
    {
        $user = User::factory()->create(['email' => 'finance.team@example.com']);

        DashboardPreference::create([
            'user_id' => $user->id,
            'refresh_seconds' => 120,
            'auto_refresh_enabled' => false,
        ]);

        $this->actingAs($user)
            ->getJson(route('role-dashboard.metrics'))
            ->assertOk()
            ->assertJsonPath('refresh_seconds', 120)
            ->assertJsonPath('auto_refresh_enabled', false)
            ->assertJsonPath('refresh_options.0', 15);
    }

    public function test_preference_endpoint_updates_refresh_settings(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->postJson(route('role-dashboard.preference'), [
                'refresh_seconds' => 30,
                'auto_refresh_enabled' => false,
            ])
            ->assertOk()
            ->assertJsonPath('refresh_seconds', 30)
            ->assertJsonPath('auto_refresh_enabled', false);

        $this->assertDatabaseHas('dashboard_preferences', [
            'user_id' => $user->id,
            'refresh_seconds' => 30,
            'auto_refresh_enabled' => false,
        ]);
    }
}


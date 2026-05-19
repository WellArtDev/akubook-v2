<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\AuditLog;
use App\Models\CashAccount;
use App\Models\Employee;
use App\Models\PayrollRun;
use App\Models\SalaryComponent;
use App\Models\User;
use App\Models\Voucher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class SensitiveActionTest extends TestCase
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

    public function test_sensitive_action_index_can_be_opened(): void
    {
        AuditLog::factory()->create([
            'actor_user_id' => $this->user->id,
            'is_sensitive' => true,
            'sensitivity_level' => 'high',
            'sensitivity_reason' => 'test_sensitive',
        ]);

        $this->get(route('sensitive-actions.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('SensitiveActions/Index')
                ->has('actions.data')
                ->has('users')
            );
    }

    public function test_salary_component_delete_is_marked_sensitive(): void
    {
        $component = SalaryComponent::factory()->create([
            'code' => 'SENS-DEL',
            'name' => 'Sensitive Delete',
        ]);

        $this->delete(route('salary-components.destroy', $component))->assertRedirect();

        $this->assertDatabaseHas('audit_logs', [
            'event_key' => 'salary_component.deleted',
            'entity_type' => 'salary_component',
            'entity_id' => $component->id,
            'is_sensitive' => true,
            'sensitivity_level' => 'high',
            'sensitivity_reason' => 'master_data_deletion',
        ]);
    }

    public function test_voucher_cancel_is_marked_sensitive(): void
    {
        $cashAccount = $this->cashAccount();
        $counterpart = Account::factory()->create(['is_active' => true, 'is_header' => false]);
        $voucher = Voucher::factory()->create([
            'voucher_type' => 'payment',
            'cash_bank_type' => 'cash',
            'cash_bank_account_id' => $cashAccount->id,
            'counterpart_account_id' => $counterpart->id,
            'status' => 'draft',
            'amount' => 100000,
        ]);

        $this->post(route('vouchers.cancel', $voucher))->assertRedirect();

        $this->assertDatabaseHas('audit_logs', [
            'event_key' => 'voucher.cancelled',
            'entity_type' => 'voucher',
            'entity_id' => $voucher->id,
            'is_sensitive' => true,
            'sensitivity_level' => 'high',
            'sensitivity_reason' => 'financial_cancellation',
        ]);
    }

    public function test_payroll_run_execution_is_marked_sensitive(): void
    {
        Employee::factory()->create(['employment_status' => 'active']);
        SalaryComponent::factory()->create([
            'code' => 'SENS-BASIC',
            'component_type' => 'earning',
            'calculation_method' => 'fixed',
            'default_amount' => 1000000,
            'is_active' => true,
        ]);

        $this->get(route('payroll-runs.index', ['period' => '2026-05', 'run' => 1]))->assertOk();

        $run = PayrollRun::where('period', '2026-05')->firstOrFail();

        $this->assertDatabaseHas('audit_logs', [
            'event_key' => 'payroll_run.executed',
            'entity_type' => 'payroll_run',
            'entity_id' => $run->id,
            'is_sensitive' => true,
            'sensitivity_level' => 'critical',
            'sensitivity_reason' => 'payroll_execution',
        ]);
    }

    public function test_sensitive_action_index_filters_by_level(): void
    {
        AuditLog::factory()->create([
            'event_key' => 'voucher.cancelled',
            'is_sensitive' => true,
            'sensitivity_level' => 'high',
        ]);
        AuditLog::factory()->create([
            'event_key' => 'payroll_run.executed',
            'is_sensitive' => true,
            'sensitivity_level' => 'critical',
        ]);

        $this->get(route('sensitive-actions.index', ['sensitivity_level' => 'critical']))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('SensitiveActions/Index')
                ->where('actions.data.0.sensitivity_level', 'critical')
            );
    }

    private function cashAccount(): CashAccount
    {
        $account = Account::factory()->create([
            'type' => 'asset',
            'category' => 'current_asset',
            'is_active' => true,
            'is_header' => false,
        ]);

        return CashAccount::factory()->create([
            'account_id' => $account->id,
            'is_active' => true,
        ]);
    }
}

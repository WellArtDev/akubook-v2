<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\CashAccount;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CashAccountTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Account $cashCoa;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
        $this->cashCoa = $this->createCashCoa('1-1001', 'Cash On Hand');
    }

    public function test_index_page_can_be_opened(): void
    {
        CashAccount::factory()->create([
            'account_id' => $this->cashCoa->id,
            'created_by' => $this->user->id,
        ]);

        $this->get(route('cash-accounts.index'))->assertOk();
    }

    public function test_user_can_create_cash_account(): void
    {
        $response = $this->post(route('cash-accounts.store'), [
            'code' => 'CASH-MAIN',
            'name' => 'Main Cash',
            'account_id' => $this->cashCoa->id,
            'opening_balance' => 100000,
            'is_active' => true,
            'description' => 'Main cashier',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('cash_accounts', [
            'code' => 'CASH-MAIN',
            'name' => 'Main Cash',
            'account_id' => $this->cashCoa->id,
            'created_by' => $this->user->id,
        ]);
    }

    public function test_cash_account_code_must_be_unique(): void
    {
        CashAccount::factory()->create([
            'code' => 'CASH-MAIN',
            'account_id' => $this->cashCoa->id,
            'created_by' => $this->user->id,
        ]);

        $response = $this->post(route('cash-accounts.store'), [
            'code' => 'CASH-MAIN',
            'name' => 'Duplicate Cash',
            'account_id' => $this->cashCoa->id,
            'opening_balance' => 0,
            'is_active' => true,
        ]);

        $response->assertSessionHasErrors('code');
    }

    public function test_cash_account_requires_current_asset_detail_account(): void
    {
        $revenue = Account::create([
            'code' => '4-1000',
            'name' => 'Revenue',
            'type' => 'revenue',
            'category' => 'operating_revenue',
            'level' => 1,
            'is_header' => false,
            'is_active' => true,
            'balance' => 0,
        ]);

        $response = $this->post(route('cash-accounts.store'), [
            'code' => 'CASH-BAD',
            'name' => 'Bad Cash',
            'account_id' => $revenue->id,
            'opening_balance' => 0,
            'is_active' => true,
        ]);

        $response->assertSessionHasErrors('account_id');
    }

    public function test_cash_account_can_be_updated(): void
    {
        $cashAccount = CashAccount::factory()->create([
            'account_id' => $this->cashCoa->id,
            'created_by' => $this->user->id,
        ]);

        $response = $this->put(route('cash-accounts.update', $cashAccount), [
            'code' => $cashAccount->code,
            'name' => 'Updated Cash',
            'account_id' => $this->cashCoa->id,
            'opening_balance' => 250000,
            'is_active' => false,
            'description' => null,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('cash_accounts', [
            'id' => $cashAccount->id,
            'name' => 'Updated Cash',
            'opening_balance' => 250000,
            'is_active' => false,
            'updated_by' => $this->user->id,
        ]);
    }

    private function createCashCoa(string $code, string $name): Account
    {
        return Account::create([
            'code' => $code,
            'name' => $name,
            'type' => 'asset',
            'category' => 'current_asset',
            'level' => 1,
            'is_header' => false,
            'is_active' => true,
            'balance' => 0,
        ]);
    }
}

<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\BankAccount;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BankAccountTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Account $bankCoa;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
        $this->bankCoa = $this->createBankCoa('1-1100', 'Bank BCA');
    }

    public function test_index_page_can_be_opened(): void
    {
        BankAccount::factory()->create([
            'account_id' => $this->bankCoa->id,
            'created_by' => $this->user->id,
        ]);

        $this->get(route('bank-accounts.index'))->assertOk();
    }

    public function test_user_can_create_bank_account(): void
    {
        $response = $this->post(route('bank-accounts.store'), [
            'code' => 'BANK-BCA',
            'name' => 'BCA Operasional',
            'bank_name' => 'BCA',
            'account_number' => '1234567890',
            'account_holder' => 'PT AkuBook',
            'account_id' => $this->bankCoa->id,
            'opening_balance' => 500000,
            'is_active' => true,
            'description' => 'Main account',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('bank_accounts', [
            'code' => 'BANK-BCA',
            'account_number' => '1234567890',
            'account_id' => $this->bankCoa->id,
            'created_by' => $this->user->id,
        ]);
    }

    public function test_code_and_account_number_must_be_unique(): void
    {
        BankAccount::factory()->create([
            'code' => 'BANK-BCA',
            'account_number' => '1234567890',
            'account_id' => $this->bankCoa->id,
            'created_by' => $this->user->id,
        ]);

        $response = $this->post(route('bank-accounts.store'), [
            'code' => 'BANK-BCA',
            'name' => 'Duplicate',
            'bank_name' => 'BCA',
            'account_number' => '1234567890',
            'account_holder' => 'PT X',
            'account_id' => $this->bankCoa->id,
            'opening_balance' => 0,
            'is_active' => true,
        ]);

        $response->assertSessionHasErrors(['code', 'account_number']);
    }

    public function test_bank_account_requires_current_asset_detail_account(): void
    {
        $liability = Account::create([
            'code' => '2-1000',
            'name' => 'AP',
            'type' => 'liability',
            'category' => 'current_liability',
            'level' => 1,
            'is_header' => false,
            'is_active' => true,
            'balance' => 0,
        ]);

        $response = $this->post(route('bank-accounts.store'), [
            'code' => 'BANK-BAD',
            'name' => 'Bad Bank',
            'bank_name' => 'Mandiri',
            'account_number' => '9999999999',
            'account_holder' => 'PT X',
            'account_id' => $liability->id,
            'opening_balance' => 0,
            'is_active' => true,
        ]);

        $response->assertSessionHasErrors('account_id');
    }

    private function createBankCoa(string $code, string $name): Account
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

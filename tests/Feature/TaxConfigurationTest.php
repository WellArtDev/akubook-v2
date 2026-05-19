<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\TaxConfiguration;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaxConfigurationTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    }

    public function test_index_page_can_be_opened(): void
    {
        $response = $this->get(route('tax-configurations.index'));

        $response->assertOk();
    }

    public function test_user_can_create_tax_configuration(): void
    {
        $account = $this->createTaxAccount();

        $response = $this->post(route('tax-configurations.store'), [
            'code' => 'PPNOUT-001',
            'name' => 'PPN Keluaran 11%',
            'tax_type' => 'ppn_out',
            'rate' => 11,
            'account_id' => $account->id,
            'is_default' => true,
            'is_active' => true,
            'description' => 'Default PPN output',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('tax_configurations', [
            'code' => 'PPNOUT-001',
            'tax_type' => 'ppn_out',
            'is_default' => true,
        ]);
    }

    public function test_only_one_default_per_tax_type(): void
    {
        $account = $this->createTaxAccount();

        $first = TaxConfiguration::query()->create([
            'code' => 'PPNOUT-OLD',
            'name' => 'Old',
            'tax_type' => 'ppn_out',
            'rate' => 10,
            'account_id' => $account->id,
            'is_default' => true,
            'is_active' => true,
            'created_by' => $this->user->id,
        ]);

        $this->post(route('tax-configurations.store'), [
            'code' => 'PPNOUT-NEW',
            'name' => 'New',
            'tax_type' => 'ppn_out',
            'rate' => 11,
            'account_id' => $account->id,
            'is_default' => true,
            'is_active' => true,
        ])->assertRedirect();

        $first->refresh();
        $this->assertFalse($first->is_default);
        $this->assertDatabaseHas('tax_configurations', [
            'code' => 'PPNOUT-NEW',
            'is_default' => true,
        ]);
    }

    public function test_code_must_be_unique(): void
    {
        $account = $this->createTaxAccount();

        TaxConfiguration::query()->create([
            'code' => 'WHT-001',
            'name' => 'WHT Lama',
            'tax_type' => 'withholding',
            'rate' => 2,
            'account_id' => $account->id,
            'is_default' => false,
            'is_active' => true,
            'created_by' => $this->user->id,
        ]);

        $this->post(route('tax-configurations.store'), [
            'code' => 'WHT-001',
            'name' => 'WHT Baru',
            'tax_type' => 'withholding',
            'rate' => 2,
            'account_id' => $account->id,
            'is_default' => false,
            'is_active' => true,
        ])->assertSessionHasErrors('code');
    }

    private function createTaxAccount(): Account
    {
        return Account::factory()->create([
            'type' => 'liability',
            'category' => 'current_liability',
            'is_header' => false,
            'is_active' => true,
        ]);
    }
}

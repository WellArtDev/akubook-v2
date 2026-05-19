<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\TaxConfiguration;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaxCalculationTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Account $account;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
        $this->account = Account::factory()->create([
            'type' => 'liability',
            'category' => 'current_liability',
            'is_header' => false,
            'is_active' => true,
        ]);
    }

    public function test_index_page_can_be_opened(): void
    {
        $response = $this->get(route('tax-calculations.index'));

        $response->assertOk();
    }

    public function test_tax_exclusive_calculation_uses_default_configuration(): void
    {
        TaxConfiguration::query()->create([
            'code' => 'PPN-OUT',
            'name' => 'PPN Output',
            'tax_type' => 'ppn_out',
            'rate' => 11,
            'account_id' => $this->account->id,
            'is_default' => true,
            'is_active' => true,
            'created_by' => $this->user->id,
        ]);

        $response = $this->get(route('tax-calculations.index', [
            'tax_type' => 'ppn_out',
            'taxable_amount' => 100000,
        ]));

        $response->assertOk();
        $this->assertDatabaseHas('tax_calculations', [
            'tax_type' => 'ppn_out',
            'taxable_amount' => 100000,
            'dpp' => 100000,
            'tax_amount' => 11000,
            'grand_total' => 111000,
        ]);
    }

    public function test_tax_inclusive_calculation_splits_dpp_and_tax(): void
    {
        TaxConfiguration::query()->create([
            'code' => 'PPN-IN',
            'name' => 'PPN Inclusive',
            'tax_type' => 'ppn_in',
            'rate' => 11,
            'account_id' => $this->account->id,
            'is_default' => true,
            'is_active' => true,
            'created_by' => $this->user->id,
        ]);

        $this->get(route('tax-calculations.index', [
            'tax_type' => 'ppn_in',
            'taxable_amount' => 111000,
            'is_inclusive' => 1,
        ]))->assertOk();

        $this->assertDatabaseHas('tax_calculations', [
            'tax_type' => 'ppn_in',
            'taxable_amount' => 111000,
            'dpp' => 100000,
            'tax_amount' => 11000,
            'grand_total' => 111000,
            'is_inclusive' => true,
        ]);
    }

    public function test_calculate_api_returns_validation_error_without_configuration(): void
    {
        $this->postJson(route('tax-calculations.calculate'), [
            'tax_type' => 'withholding',
            'taxable_amount' => 100000,
        ])->assertStatus(422);
    }
}

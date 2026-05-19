<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\FiscalPeriod;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OpeningBalancesImportControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private FiscalPeriod $period;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();

        $this->period = FiscalPeriod::create([
            'name' => 'January 2026',
            'start_date' => '2026-01-01',
            'end_date' => '2026-01-31',
            'status' => 'open',
            'is_current' => true,
        ]);

        Account::create([
            'code' => '1-1000',
            'name' => 'Kas',
            'type' => 'asset',
            'category' => 'current_asset',
            'level' => 2,
            'is_header' => false,
            'is_active' => true,
            'balance' => 0,
        ]);

        Account::create([
            'code' => '3-1000',
            'name' => 'Modal',
            'type' => 'equity',
            'category' => 'equity',
            'level' => 2,
            'is_header' => false,
            'is_active' => true,
            'balance' => 0,
        ]);
    }

    public function test_user_can_preview_opening_balances_import(): void
    {
        $response = $this->actingAs($this->user)->postJson(route('migration.opening-balances.preview'), $this->payload());

        $response->assertOk()
            ->assertJsonPath('total', 2)
            ->assertJsonPath('valid', 2)
            ->assertJsonPath('difference', 0)
            ->assertJsonPath('executed', false);
    }

    public function test_user_can_import_opening_balances_and_create_journal(): void
    {
        $response = $this->actingAs($this->user)->postJson(route('migration.opening-balances.import'), $this->payload());

        $response->assertOk()
            ->assertJsonPath('executed', true)
            ->assertJsonPath('imported', 2)
            ->assertJsonPath('difference', 0);

        $journalId = $response->json('journal_entry_id');

        $this->assertDatabaseHas('journal_entries', [
            'id' => $journalId,
            'reference_type' => 'opening_balance',
            'status' => 'posted',
        ]);
        $this->assertDatabaseCount('journal_entry_lines', 2);
    }

    private function payload(): array
    {
        return [
            'fiscal_period_id' => $this->period->id,
            'balance_date' => '2026-01-01',
            'opening_balances' => [
                ['account_code' => '1-1000', 'debit' => 1000, 'credit' => 0],
                ['account_code' => '3-1000', 'debit' => 0, 'credit' => 1000],
            ],
        ];
    }
}

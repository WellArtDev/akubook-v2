<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChartOfAccountsImportControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
    }

    public function test_user_can_preview_chart_of_accounts_import(): void
    {
        $response = $this->actingAs($this->user)->postJson(route('migration.chart-of-accounts.preview'), [
            'accounts' => [
                ['code' => '1-1000', 'name' => 'Kas', 'type' => 'asset'],
            ],
        ]);

        $response->assertOk()
            ->assertJsonPath('total', 1)
            ->assertJsonPath('valid', 1)
            ->assertJsonPath('executed', false);
    }

    public function test_user_can_import_chart_of_accounts(): void
    {
        $response = $this->actingAs($this->user)->postJson(route('migration.chart-of-accounts.import'), [
            'accounts' => [
                ['code' => '1-1000', 'name' => 'Kas', 'type' => 'asset', 'is_header' => true],
                ['code' => '1-1100', 'name' => 'Kas Kecil', 'type' => 'asset', 'parent_code' => '1-1000'],
            ],
        ]);

        $response->assertOk()
            ->assertJsonPath('total', 2)
            ->assertJsonPath('imported', 2)
            ->assertJsonPath('skipped', 0);

        $parent = Account::where('code', '1-1000')->firstOrFail();

        $this->assertDatabaseHas('accounts', [
            'code' => '1-1100',
            'parent_id' => $parent->id,
            'is_header' => false,
        ]);
    }
}

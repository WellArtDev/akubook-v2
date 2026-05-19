<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MasterDataImportControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
    }

    public function test_user_can_preview_master_data_import(): void
    {
        $response = $this->actingAs($this->user)->postJson(route('migration.master-data.preview'), [
            'customers' => [
                ['code' => 'CUST-010', 'name' => 'PT Preview', 'customer_type' => 'company'],
            ],
        ]);

        $response->assertOk()
            ->assertJsonPath('total', 1)
            ->assertJsonPath('valid', 1)
            ->assertJsonPath('executed', false);
    }

    public function test_user_can_import_master_data(): void
    {
        $response = $this->actingAs($this->user)->postJson(route('migration.master-data.import'), [
            'customers' => [
                ['code' => 'CUST-011', 'name' => 'PT Import', 'customer_type' => 'company'],
            ],
            'suppliers' => [
                ['supplier_code' => 'SUPP-011', 'name' => 'PT Vendor Import', 'tax_type' => 'pkp'],
            ],
            'items' => [
                ['code' => 'ITEM-011', 'name' => 'Produk Import', 'item_type' => 'goods', 'unit' => 'PCS'],
            ],
        ]);

        $response->assertOk()
            ->assertJsonPath('total', 3)
            ->assertJsonPath('imported', 3)
            ->assertJsonPath('skipped', 0);

        $this->assertDatabaseHas('customers', ['code' => 'CUST-011']);
        $this->assertDatabaseHas('suppliers', ['supplier_code' => 'SUPP-011']);
        $this->assertDatabaseHas('items', ['code' => 'ITEM-011']);
    }
}

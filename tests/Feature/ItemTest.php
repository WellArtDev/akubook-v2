<?php

namespace Tests\Feature;

use App\Models\Item;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ItemTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        $this->user = User::factory()->create();
    }

    public function test_item_index_page_can_be_opened(): void
    {
        Item::create([
            'code' => 'ITM-001',
            'name' => 'Item A',
            'category' => 'Raw Material',
            'description' => 'Desc',
            'item_type' => 'goods',
            'inventory_type' => 'stock',
            'valuation_method' => 'moving_average',
            'unit' => 'pcs',
            'purchase_price' => 1000,
            'selling_price' => 1500,
            'minimum_stock' => 10,
            'reorder_point' => 20,
            'is_active' => true,
        ]);

        $this->actingAs($this->user)
            ->get(route('items.index'))
            ->assertOk();
    }

    public function test_user_can_create_item_with_inventory_fields(): void
    {
        $payload = [
            'code' => 'ITM-002',
            'name' => 'Item B',
            'category' => 'Packaging',
            'description' => 'Box',
            'item_type' => 'goods',
            'inventory_type' => 'stock',
            'valuation_method' => 'fifo',
            'unit' => 'box',
            'purchase_price' => 12000,
            'selling_price' => 15000,
            'minimum_stock' => 5,
            'reorder_point' => 8,
            'is_active' => true,
        ];

        $this->actingAs($this->user)
            ->post(route('items.store'), $payload)
            ->assertRedirect();

        $this->assertDatabaseHas('items', [
            'code' => 'ITM-002',
            'inventory_type' => 'stock',
            'valuation_method' => 'fifo',
            'category' => 'Packaging',
        ]);
    }

    public function test_item_code_must_be_unique(): void
    {
        Item::create([
            'code' => 'ITM-003',
            'name' => 'Item C',
            'category' => 'Raw Material',
            'description' => null,
            'item_type' => 'goods',
            'inventory_type' => 'stock',
            'valuation_method' => 'moving_average',
            'unit' => 'pcs',
            'purchase_price' => 100,
            'selling_price' => 120,
            'minimum_stock' => 1,
            'reorder_point' => 2,
            'is_active' => true,
        ]);

        $this->actingAs($this->user)
            ->post(route('items.store'), [
                'code' => 'ITM-003',
                'name' => 'Duplicate',
                'category' => 'Raw Material',
                'description' => null,
                'item_type' => 'goods',
                'inventory_type' => 'stock',
                'valuation_method' => 'moving_average',
                'unit' => 'pcs',
                'purchase_price' => 100,
                'selling_price' => 120,
                'minimum_stock' => 1,
                'reorder_point' => 2,
                'is_active' => true,
            ])
            ->assertSessionHasErrors('code');
    }

    public function test_item_list_can_filter_active_and_search(): void
    {
        Item::create([
            'code' => 'ITM-004',
            'name' => 'Active Item',
            'category' => 'Raw Material',
            'description' => null,
            'item_type' => 'goods',
            'inventory_type' => 'stock',
            'valuation_method' => 'moving_average',
            'unit' => 'pcs',
            'purchase_price' => 100,
            'selling_price' => 120,
            'minimum_stock' => 1,
            'reorder_point' => 2,
            'is_active' => true,
        ]);

        Item::create([
            'code' => 'ITM-005',
            'name' => 'Inactive Item',
            'category' => 'Service',
            'description' => null,
            'item_type' => 'service',
            'inventory_type' => 'non_stock',
            'valuation_method' => 'standard',
            'unit' => 'job',
            'purchase_price' => 0,
            'selling_price' => 1000,
            'minimum_stock' => 0,
            'reorder_point' => 0,
            'is_active' => false,
        ]);

        $this->actingAs($this->user)
            ->get(route('items.index', ['search' => 'Active', 'is_active' => '1']))
            ->assertOk();
    }
}

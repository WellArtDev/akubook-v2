<?php

namespace Tests\Feature;

use App\Models\Item;
use App\Models\StockTransaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InventoryValuationTest extends TestCase
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

    public function test_inventory_valuation_page_can_be_opened(): void
    {
        $response = $this->get(route('inventory-valuations.index'));

        $response->assertOk();
    }

    public function test_inventory_valuation_calculates_stock_value(): void
    {
        $item = $this->createItem('RM-001', 'Raw Material', 2500);

        StockTransaction::create([
            'item_id' => $item->id,
            'movement_type' => 'purchase_receipt',
            'quantity_in' => 10,
            'quantity_out' => 0,
            'movement_date' => now()->toDateString(),
            'created_by' => $this->user->id,
        ]);

        StockTransaction::create([
            'item_id' => $item->id,
            'movement_type' => 'sales_delivery',
            'quantity_in' => 0,
            'quantity_out' => 4,
            'movement_date' => now()->toDateString(),
            'created_by' => $this->user->id,
        ]);

        $response = $this->get(route('inventory-valuations.index'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('InventoryValuations/Index')
            ->where('valuations.0.current_stock', 6)
            ->where('valuations.0.average_cost', 2500)
            ->where('valuations.0.inventory_value', 15000)
            ->where('totalValue', 15000)
        );
    }

    public function test_inventory_valuation_filter_search(): void
    {
        $this->createItem('RM-001', 'Raw Material', 1000);
        $this->createItem('FG-001', 'Finished Good', 2000);

        $response = $this->get(route('inventory-valuations.index', ['search' => 'Finished']));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->where('valuations.0.code', 'FG-001')
            ->where('valuations.0.name', 'Finished Good')
        );
    }

    private function createItem(string $code, string $name, float $purchasePrice): Item
    {
        return Item::create([
            'code' => $code,
            'name' => $name,
            'category' => 'Inventory',
            'description' => null,
            'item_type' => 'goods',
            'inventory_type' => 'stock',
            'valuation_method' => 'moving_average',
            'unit' => 'pcs',
            'purchase_price' => $purchasePrice,
            'selling_price' => $purchasePrice * 1.2,
            'minimum_stock' => 0,
            'reorder_point' => 0,
            'is_active' => true,
        ]);
    }
}

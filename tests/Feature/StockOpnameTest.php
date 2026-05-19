<?php

namespace Tests\Feature;

use App\Models\Item;
use App\Models\StockOpname;
use App\Models\StockOpnameLine;
use App\Models\StockTransaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StockOpnameTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Item $item;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        $this->user = User::factory()->create();
        $this->item = Item::factory()->create();
    }

    public function test_create_stock_opname(): void
    {
        $this->actingAs($this->user)
            ->post(route('stock-opnames.store'), [
                'opname_date' => now()->toDateString(),
                'notes' => 'Cycle count',
                'lines' => [[
                    'item_id' => $this->item->id,
                    'system_quantity' => 10,
                    'physical_quantity' => 8,
                    'notes' => 'Damaged',
                ]],
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('stock_opnames', ['status' => 'draft', 'created_by' => $this->user->id]);
        $this->assertDatabaseHas('stock_opname_lines', ['item_id' => $this->item->id, 'variance_quantity' => -2]);
    }

    public function test_confirm_creates_adjustment_transaction(): void
    {
        $opname = StockOpname::create([
            'opname_number' => StockOpname::generateNumber(),
            'opname_date' => now()->toDateString(),
            'status' => 'draft',
            'notes' => null,
            'created_by' => $this->user->id,
        ]);

        StockOpnameLine::create([
            'stock_opname_id' => $opname->id,
            'item_id' => $this->item->id,
            'system_quantity' => 10,
            'physical_quantity' => 13,
            'variance_quantity' => 3,
            'notes' => null,
        ]);

        $this->actingAs($this->user)
            ->post(route('stock-opnames.confirm', $opname))
            ->assertRedirect();

        $this->assertDatabaseHas('stock_opnames', ['id' => $opname->id, 'status' => 'confirmed']);
        $this->assertDatabaseHas('stock_transactions', [
            'item_id' => $this->item->id,
            'movement_type' => 'adjustment',
            'quantity_in' => 3,
            'quantity_out' => 0,
            'reference_type' => 'stock_opname',
            'reference_id' => $opname->id,
        ]);
    }

    public function test_index_and_show_open(): void
    {
        $opname = StockOpname::create([
            'opname_number' => StockOpname::generateNumber(),
            'opname_date' => now()->toDateString(),
            'status' => 'draft',
            'notes' => null,
            'created_by' => $this->user->id,
        ]);

        $this->actingAs($this->user)->get(route('stock-opnames.index'))->assertOk();
        $this->actingAs($this->user)->get(route('stock-opnames.show', $opname))->assertOk();
    }
}

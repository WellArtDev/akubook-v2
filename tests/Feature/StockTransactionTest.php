<?php

namespace Tests\Feature;

use App\Models\Item;
use App\Models\StockTransaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StockTransactionTest extends TestCase
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

    public function test_index_page_can_be_opened(): void
    {
        StockTransaction::factory()->create([
            'item_id' => $this->item->id,
            'created_by' => $this->user->id,
        ]);

        $this->actingAs($this->user)
            ->get(route('stock-transactions.index'))
            ->assertOk();
    }

    public function test_user_can_create_stock_adjustment(): void
    {
        $this->actingAs($this->user)
            ->post(route('stock-transactions.store'), [
                'item_id' => $this->item->id,
                'movement_type' => 'adjustment',
                'quantity' => 12.5,
                'direction' => 'in',
                'movement_date' => now()->toDateString(),
                'reference_type' => 'manual',
                'reference_id' => null,
                'notes' => 'Initial stock',
            ])
            ->assertRedirect(route('stock-transactions.index'));

        $this->assertDatabaseHas('stock_transactions', [
            'item_id' => $this->item->id,
            'movement_type' => 'adjustment',
            'quantity_in' => 12.5,
            'quantity_out' => 0,
            'created_by' => $this->user->id,
        ]);
    }

    public function test_stock_tracking_filter_works(): void
    {
        StockTransaction::factory()->create([
            'item_id' => $this->item->id,
            'movement_type' => 'adjustment',
            'created_by' => $this->user->id,
            'movement_date' => '2026-05-01',
        ]);

        StockTransaction::factory()->create([
            'item_id' => $this->item->id,
            'movement_type' => 'sales_delivery',
            'quantity_in' => 0,
            'quantity_out' => 2,
            'created_by' => $this->user->id,
            'movement_date' => '2026-05-10',
        ]);

        $this->actingAs($this->user)
            ->get(route('stock-transactions.index', [
                'movement_type' => 'adjustment',
                'date_from' => '2026-05-01',
                'date_to' => '2026-05-05',
            ]))
            ->assertOk();
    }

    public function test_invalid_movement_type_rejected(): void
    {
        $this->actingAs($this->user)
            ->post(route('stock-transactions.store'), [
                'item_id' => $this->item->id,
                'movement_type' => 'invalid_type',
                'quantity' => 1,
                'direction' => 'in',
                'movement_date' => now()->toDateString(),
            ])
            ->assertSessionHasErrors('movement_type');
    }
}

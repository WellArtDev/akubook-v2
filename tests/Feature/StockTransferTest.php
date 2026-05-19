<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Item;
use App\Models\StockTransaction;
use App\Models\StockTransfer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StockTransferTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    }

    public function test_user_can_create_stock_transfer(): void
    {
        $from = Branch::factory()->create();
        $to = Branch::factory()->create();
        $item = Item::factory()->create(['item_type' => 'goods']);

        $response = $this->post(route('stock-transfers.store'), [
            'transfer_date' => now()->toDateString(),
            'from_branch_id' => $from->id,
            'to_branch_id' => $to->id,
            'notes' => 'Transfer antar gudang',
            'lines' => [
                ['item_id' => $item->id, 'quantity' => 3, 'notes' => null],
            ],
        ]);

        $response->assertRedirect(route('stock-transfers.index'));

        $this->assertDatabaseHas('stock_transfers', [
            'from_branch_id' => $from->id,
            'to_branch_id' => $to->id,
            'status' => 'draft',
        ]);

        $this->assertDatabaseHas('stock_transfer_lines', [
            'item_id' => $item->id,
            'quantity' => 3,
        ]);
    }

    public function test_confirm_creates_out_and_in_transactions(): void
    {
        $from = Branch::factory()->create();
        $to = Branch::factory()->create();
        $item = Item::factory()->create(['item_type' => 'goods']);

        StockTransaction::create([
            'item_id' => $item->id,
            'branch_id' => $from->id,
            'movement_type' => 'adjustment',
            'quantity_in' => 10,
            'quantity_out' => 0,
            'movement_date' => now()->toDateString(),
            'reference_type' => 'seed',
            'reference_id' => 1,
            'notes' => null,
            'created_by' => $this->user->id,
        ]);

        $transfer = StockTransfer::factory()->create([
            'from_branch_id' => $from->id,
            'to_branch_id' => $to->id,
            'status' => 'draft',
            'created_by' => $this->user->id,
        ]);

        $transfer->lines()->create([
            'item_id' => $item->id,
            'line_number' => 1,
            'quantity' => 4,
            'unit' => 'pcs',
        ]);

        $response = $this->post(route('stock-transfers.confirm', $transfer));

        $response->assertRedirect(route('stock-transfers.show', $transfer));

        $this->assertDatabaseHas('stock_transfers', [
            'id' => $transfer->id,
            'status' => 'confirmed',
            'confirmed_by' => $this->user->id,
        ]);

        $this->assertDatabaseHas('stock_transactions', [
            'item_id' => $item->id,
            'branch_id' => $from->id,
            'movement_type' => 'transfer_out',
            'quantity_out' => 4,
            'reference_type' => 'stock_transfer',
            'reference_id' => $transfer->id,
        ]);

        $this->assertDatabaseHas('stock_transactions', [
            'item_id' => $item->id,
            'branch_id' => $to->id,
            'movement_type' => 'transfer_in',
            'quantity_in' => 4,
            'reference_type' => 'stock_transfer',
            'reference_id' => $transfer->id,
        ]);
    }

    public function test_confirm_fails_if_source_stock_not_enough(): void
    {
        $from = Branch::factory()->create();
        $to = Branch::factory()->create();
        $item = Item::factory()->create(['item_type' => 'goods']);

        $transfer = StockTransfer::factory()->create([
            'from_branch_id' => $from->id,
            'to_branch_id' => $to->id,
            'status' => 'draft',
            'created_by' => $this->user->id,
        ]);

        $transfer->lines()->create([
            'item_id' => $item->id,
            'line_number' => 1,
            'quantity' => 2,
            'unit' => 'pcs',
        ]);

        $response = $this->post(route('stock-transfers.confirm', $transfer));

        $response->assertSessionHasErrors('error');

        $this->assertDatabaseMissing('stock_transactions', [
            'movement_type' => 'transfer_out',
            'reference_id' => $transfer->id,
        ]);
    }
}

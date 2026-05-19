<?php

namespace Tests\Feature;

use App\Models\DotMatrixTemplate;
use App\Models\PrintDraft;
use App\Models\PrintHistory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PrintHistoryTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        $this->user = User::factory()->create();
    }

    public function test_record_print_creates_history(): void
    {
        $template = DotMatrixTemplate::factory()->create([
            'document_type' => 'sales_invoice',
            'created_by' => $this->user->id,
        ]);

        $draft = PrintDraft::factory()->create([
            'document_type' => 'sales_invoice',
            'document_id' => 99,
            'dot_matrix_template_id' => $template->id,
            'status' => 'ready',
            'created_by' => $this->user->id,
        ]);

        $this->actingAs($this->user)
            ->post(route('print-drafts.record-print', $draft))
            ->assertRedirect(route('print-histories.index'));

        $this->assertDatabaseHas('print_histories', [
            'print_draft_id' => $draft->id,
            'document_type' => 'sales_invoice',
            'document_id' => 99,
            'dot_matrix_template_id' => $template->id,
            'printed_by' => $this->user->id,
        ]);
    }

    public function test_print_history_index_can_be_filtered(): void
    {
        PrintHistory::factory()->create([
            'document_type' => 'sales_invoice',
            'printed_by' => $this->user->id,
            'printed_at' => now(),
        ]);

        $this->actingAs($this->user)
            ->get(route('print-histories.index', [
                'document_type' => 'sales_invoice',
                'printed_by' => $this->user->id,
                'date_from' => now()->toDateString(),
                'date_to' => now()->toDateString(),
            ]))
            ->assertOk();
    }

    public function test_print_history_detail_can_be_opened(): void
    {
        $history = PrintHistory::factory()->create([
            'printed_by' => $this->user->id,
        ]);

        $this->actingAs($this->user)
            ->get(route('print-histories.show', $history))
            ->assertOk();
    }
}

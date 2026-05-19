<?php

namespace Tests\Feature;

use App\Models\DotMatrixTemplate;
use App\Models\PrintDraft;
use App\Models\SalesInvoice;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PrintDraftTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        $this->user = User::factory()->create();
    }

    public function test_index_page_can_be_opened(): void
    {
        PrintDraft::factory()->create([
            'created_by' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('print-drafts.index'));

        $response->assertOk();
    }

    public function test_user_can_create_print_draft_without_mutating_source_document(): void
    {
        $invoice = SalesInvoice::factory()->create([
            'invoice_number' => 'INV-IMMUTABLE-001',
        ]);

        $template = DotMatrixTemplate::factory()->create([
            'document_type' => 'sales_invoice',
            'created_by' => $this->user->id,
        ]);

        $payload = [
            'document_type' => 'sales_invoice',
            'document_id' => $invoice->id,
            'dot_matrix_template_id' => $template->id,
            'override_payload' => [
                'header' => [
                    'title' => 'Invoice Cetak Ulang',
                    'document_number' => 'INV-PRINT-EDIT-001',
                ],
                'lines' => [],
            ],
            'status' => 'draft',
        ];

        $this->actingAs($this->user)
            ->post(route('print-drafts.store'), $payload)
            ->assertRedirect();

        $this->assertDatabaseHas('print_drafts', [
            'document_type' => 'sales_invoice',
            'document_id' => $invoice->id,
            'dot_matrix_template_id' => $template->id,
            'status' => 'draft',
            'created_by' => $this->user->id,
        ]);

        $this->assertDatabaseHas('sales_invoices', [
            'id' => $invoice->id,
            'invoice_number' => 'INV-IMMUTABLE-001',
        ]);
    }

    public function test_print_draft_can_be_updated(): void
    {
        $template = DotMatrixTemplate::factory()->create([
            'document_type' => 'sales_invoice',
            'created_by' => $this->user->id,
        ]);

        $draft = PrintDraft::factory()->create([
            'document_type' => 'sales_invoice',
            'document_id' => 1,
            'dot_matrix_template_id' => $template->id,
            'created_by' => $this->user->id,
        ]);

        $payload = [
            'document_type' => 'sales_invoice',
            'document_id' => 2,
            'dot_matrix_template_id' => $template->id,
            'override_payload' => [
                'header' => [
                    'title' => 'Edited Header',
                    'document_number' => 'INV-EDIT-002',
                ],
                'lines' => [],
            ],
            'status' => 'ready',
        ];

        $this->actingAs($this->user)
            ->put(route('print-drafts.update', $draft), $payload)
            ->assertRedirect();

        $this->assertDatabaseHas('print_drafts', [
            'id' => $draft->id,
            'document_id' => 2,
            'status' => 'ready',
            'updated_by' => $this->user->id,
        ]);
    }

    public function test_preview_page_can_be_opened(): void
    {
        $template = DotMatrixTemplate::factory()->create([
            'document_type' => 'sales_invoice',
            'created_by' => $this->user->id,
        ]);

        $draft = PrintDraft::factory()->create([
            'document_type' => 'sales_invoice',
            'dot_matrix_template_id' => $template->id,
            'created_by' => $this->user->id,
            'override_payload' => [
                'header' => [
                    'invoice_number' => 'INV-PREVIEW-001',
                ],
            ],
        ]);

        $this->actingAs($this->user)
            ->get(route('print-drafts.preview', $draft))
            ->assertOk();
    }

    public function test_mark_ready_from_preview_updates_status(): void
    {
        $template = DotMatrixTemplate::factory()->create([
            'document_type' => 'sales_invoice',
            'created_by' => $this->user->id,
        ]);

        $draft = PrintDraft::factory()->create([
            'document_type' => 'sales_invoice',
            'status' => 'draft',
            'dot_matrix_template_id' => $template->id,
            'created_by' => $this->user->id,
        ]);

        $this->actingAs($this->user)
            ->post(route('print-drafts.mark-ready', $draft))
            ->assertRedirect(route('print-drafts.preview', $draft));

        $this->assertDatabaseHas('print_drafts', [
            'id' => $draft->id,
            'status' => 'ready',
            'updated_by' => $this->user->id,
        ]);
    }

    public function test_sales_document_types_can_open_create_page(): void
    {
        foreach (['sales_quotation', 'sales_order', 'delivery_order', 'sales_invoice', 'credit_note'] as $documentType) {
            $this->actingAs($this->user)
                ->get(route('print-drafts.create', ['document_type' => $documentType]))
                ->assertOk();
        }
    }

    public function test_purchase_document_types_can_open_create_page(): void
    {
        foreach (['purchase_request', 'purchase_order', 'goods_receipt', 'purchase_invoice', 'debit_note'] as $documentType) {
            $this->actingAs($this->user)
                ->get(route('print-drafts.create', ['document_type' => $documentType]))
                ->assertOk();
        }
    }

    public function test_print_draft_can_be_deleted(): void
    {
        $draft = PrintDraft::factory()->create([
            'created_by' => $this->user->id,
        ]);

        $this->actingAs($this->user)
            ->delete(route('print-drafts.destroy', $draft))
            ->assertRedirect(route('print-drafts.index'));

        $this->assertSoftDeleted('print_drafts', [
            'id' => $draft->id,
        ]);
    }
}

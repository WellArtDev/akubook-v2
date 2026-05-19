<?php

namespace Tests\Feature;

use App\Models\DotMatrixTemplate;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DotMatrixTemplateTest extends TestCase
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
        DotMatrixTemplate::factory()->create([
            'created_by' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('dot-matrix-templates.index'));

        $response->assertOk();
    }

    public function test_user_can_create_template(): void
    {
        $payload = [
            'name' => 'Sales Invoice Dot Matrix',
            'document_type' => 'sales_invoice',
            'paper_size' => 'continuous_9_5x11',
            'columns' => 80,
            'rows' => 66,
            'margins' => [
                'top' => 1,
                'left' => 2,
                'right' => 2,
                'bottom' => 1,
            ],
            'field_map' => [
                ['field' => 'invoice_number', 'x' => 2, 'y' => 2],
                ['field' => 'customer_name', 'x' => 2, 'y' => 4],
            ],
            'is_default' => true,
            'is_active' => true,
        ];

        $response = $this->actingAs($this->user)
            ->post(route('dot-matrix-templates.store'), $payload);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect();

        $this->assertDatabaseHas('dot_matrix_templates', [
            'name' => 'Sales Invoice Dot Matrix',
            'document_type' => 'sales_invoice',
            'is_default' => true,
            'is_active' => true,
            'created_by' => $this->user->id,
        ]);
    }

    public function test_default_template_is_unique_per_document_type(): void
    {
        DotMatrixTemplate::factory()->create([
            'name' => 'Old Default',
            'document_type' => 'sales_invoice',
            'is_default' => true,
            'created_by' => $this->user->id,
        ]);

        $payload = [
            'name' => 'New Default',
            'document_type' => 'sales_invoice',
            'paper_size' => 'continuous_9_5x11',
            'columns' => 80,
            'rows' => 66,
            'margins' => [
                'top' => 1,
                'left' => 2,
                'right' => 2,
                'bottom' => 1,
            ],
            'field_map' => [
                ['field' => 'invoice_number', 'x' => 2, 'y' => 2],
            ],
            'is_default' => true,
            'is_active' => true,
        ];

        $this->actingAs($this->user)
            ->post(route('dot-matrix-templates.store'), $payload)
            ->assertRedirect();

        $this->assertDatabaseHas('dot_matrix_templates', [
            'name' => 'Old Default',
            'document_type' => 'sales_invoice',
            'is_default' => false,
        ]);

        $this->assertDatabaseHas('dot_matrix_templates', [
            'name' => 'New Default',
            'document_type' => 'sales_invoice',
            'is_default' => true,
        ]);
    }

    public function test_template_can_be_updated(): void
    {
        $template = DotMatrixTemplate::factory()->create([
            'document_type' => 'delivery_order',
            'created_by' => $this->user->id,
        ]);

        $payload = [
            'name' => 'Delivery Order Updated',
            'document_type' => 'delivery_order',
            'paper_size' => 'fanfold_14_7x11',
            'columns' => 132,
            'rows' => 66,
            'margins' => [
                'top' => 1,
                'left' => 1,
                'right' => 1,
                'bottom' => 1,
            ],
            'field_map' => [
                ['field' => 'do_number', 'x' => 3, 'y' => 2],
            ],
            'is_default' => false,
            'is_active' => true,
        ];

        $response = $this->actingAs($this->user)
            ->put(route('dot-matrix-templates.update', $template), $payload);

        $response->assertRedirect();

        $this->assertDatabaseHas('dot_matrix_templates', [
            'id' => $template->id,
            'name' => 'Delivery Order Updated',
            'paper_size' => 'fanfold_14_7x11',
            'columns' => 132,
            'updated_by' => $this->user->id,
        ]);
    }

    public function test_defaults_endpoint_returns_field_map(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('dot-matrix-templates.defaults', [
                'document_type' => 'goods_receipt',
            ]));

        $response->assertOk();
        $response->assertJsonStructure(['field_map']);
    }
}

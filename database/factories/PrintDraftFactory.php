<?php

namespace Database\Factories;

use App\Models\DotMatrixTemplate;
use App\Models\PrintDraft;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PrintDraft>
 */
class PrintDraftFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'draft_number' => 'PD-' . now()->year . '-' . $this->faker->unique()->numerify('####'),
            'document_type' => 'sales_invoice',
            'document_id' => 1,
            'dot_matrix_template_id' => DotMatrixTemplate::factory(),
            'override_payload' => [
                'header' => [
                    'title' => 'Sales Invoice',
                    'document_number' => 'INV-2026-0001',
                ],
                'lines' => [],
            ],
            'status' => 'draft',
            'created_by' => User::factory(),
        ];
    }
}

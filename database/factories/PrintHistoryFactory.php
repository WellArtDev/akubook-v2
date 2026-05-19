<?php

namespace Database\Factories;

use App\Models\DotMatrixTemplate;
use App\Models\PrintDraft;
use App\Models\PrintHistory;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PrintHistory>
 */
class PrintHistoryFactory extends Factory
{
    protected $model = PrintHistory::class;

    public function definition(): array
    {
        return [
            'print_draft_id' => PrintDraft::factory(),
            'document_type' => 'sales_invoice',
            'document_id' => 1,
            'dot_matrix_template_id' => DotMatrixTemplate::factory(),
            'printed_by' => User::factory(),
            'printed_at' => now(),
            'output_metadata' => [
                'columns' => 80,
                'rows' => 66,
                'draft_status' => 'ready',
            ],
        ];
    }
}

<?php

namespace Database\Factories;

use App\Models\EFakturExport;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class EFakturExportFactory extends Factory
{
    protected $model = EFakturExport::class;

    public function definition(): array
    {
        $year = now()->year;

        return [
            'export_number' => "EF-{$year}-" . str_pad((string) fake()->numberBetween(1, 9999), 4, '0', STR_PAD_LEFT),
            'period_start' => now()->startOfMonth()->toDateString(),
            'period_end' => now()->endOfMonth()->toDateString(),
            'status' => 'generated',
            'row_count' => 1,
            'csv_content' => "faktur_number,faktur_date\nFP-{$year}-0001," . now()->toDateString() . "\n",
            'metadata' => ['format' => 'csv'],
            'created_by' => User::factory(),
            'generated_at' => now(),
        ];
    }
}

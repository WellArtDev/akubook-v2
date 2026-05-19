<?php

namespace Database\Factories;

use App\Models\EFakturExport;
use App\Models\EFakturExportLine;
use App\Models\FakturPajak;
use Illuminate\Database\Eloquent\Factories\Factory;

class EFakturExportLineFactory extends Factory
{
    protected $model = EFakturExportLine::class;

    public function definition(): array
    {
        return [
            'e_faktur_export_id' => EFakturExport::factory(),
            'faktur_pajak_id' => FakturPajak::factory(),
            'line_number' => 1,
            'faktur_number' => 'FP-' . now()->year . '-0001',
            'faktur_date' => now()->toDateString(),
            'customer_name' => fake()->company(),
            'customer_tax_id' => fake()->numerify('##.###.###.#-###.###'),
            'dpp' => 100000,
            'ppn_amount' => 11000,
            'grand_total' => 111000,
        ];
    }
}

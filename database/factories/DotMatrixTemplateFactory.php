<?php

namespace Database\Factories;

use App\Models\DotMatrixTemplate;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class DotMatrixTemplateFactory extends Factory
{
    protected $model = DotMatrixTemplate::class;

    public function definition(): array
    {
        $documentType = $this->faker->randomElement(DotMatrixTemplate::DOCUMENT_TYPES);

        return [
            'name' => strtoupper($documentType) . ' Default',
            'document_type' => $documentType,
            'paper_size' => 'continuous_9_5x11',
            'columns' => 80,
            'rows' => 66,
            'margins' => [
                'top' => 1,
                'left' => 2,
                'right' => 2,
                'bottom' => 1,
            ],
            'field_map' => DotMatrixTemplate::defaultFieldMap($documentType),
            'is_default' => false,
            'is_active' => true,
            'created_by' => User::factory(),
            'updated_by' => null,
        ];
    }
}

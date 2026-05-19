<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaxCalculation extends Model
{
    use HasFactory;

    protected $fillable = [
        'tax_configuration_id',
        'tax_type',
        'taxable_amount',
        'is_inclusive',
        'rate',
        'dpp',
        'tax_amount',
        'grand_total',
        'created_by',
    ];

    protected $casts = [
        'taxable_amount' => 'decimal:2',
        'is_inclusive' => 'boolean',
        'rate' => 'decimal:4',
        'dpp' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'grand_total' => 'decimal:2',
        'tax_configuration_id' => 'integer',
        'created_by' => 'integer',
    ];

    public function taxConfiguration(): BelongsTo
    {
        return $this->belongsTo(TaxConfiguration::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}

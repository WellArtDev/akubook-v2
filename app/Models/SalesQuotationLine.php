<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SalesQuotationLine extends Model
{
    use HasFactory;

    protected $fillable = [
        'sales_quotation_id',
        'line_number',
        'item_id',
        'description',
        'quantity',
        'unit',
        'unit_price',
        'discount_percentage',
        'discount_amount',
        'tax_percentage',
        'tax_amount',
        'line_total',
        'notes',
    ];

    protected $casts = [
        'quantity' => 'decimal:3',
        'unit_price' => 'decimal:2',
        'discount_percentage' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'tax_percentage' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'line_total' => 'decimal:2',
    ];

    public function quotation(): BelongsTo
    {
        return $this->belongsTo(SalesQuotation::class, 'sales_quotation_id');
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    public function calculateLineTotal(): void
    {
        $gross = (float) $this->quantity * (float) $this->unit_price;
        $discount = (float) $this->discount_amount;

        if ((float) $this->discount_percentage > 0) {
            $discount = round($gross * ((float) $this->discount_percentage / 100), 2);
            $this->discount_amount = $discount;
        }

        $taxableAmount = max($gross - $discount, 0);
        $this->tax_amount = round($taxableAmount * ((float) $this->tax_percentage / 100), 2);
        $this->line_total = $taxableAmount;
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SalesReturnLine extends Model
{
    use HasFactory;

    protected $fillable = [
        'sales_return_id',
        'sales_invoice_line_id',
        'product_id',
        'product_name',
        'return_quantity',
        'accepted_quantity',
        'rejected_quantity',
        'unit_price',
        'tax_amount',
        'line_total',
        'inspection_notes',
    ];

    protected $casts = [
        'return_quantity' => 'decimal:3',
        'accepted_quantity' => 'decimal:3',
        'rejected_quantity' => 'decimal:3',
        'unit_price' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'line_total' => 'decimal:2',
    ];

    public function salesReturn(): BelongsTo
    {
        return $this->belongsTo(SalesReturn::class);
    }

    public function salesInvoiceLine(): BelongsTo
    {
        return $this->belongsTo(SalesInvoiceLine::class);
    }

    public function calculateTotals(): void
    {
        $base = $this->return_quantity * $this->unit_price;
        $this->tax_amount = round($base * 0.11, 2);
        $this->line_total = $base;
    }
}

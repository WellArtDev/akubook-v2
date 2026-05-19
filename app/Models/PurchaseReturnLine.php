<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchaseReturnLine extends Model
{
    use HasFactory;

    protected $fillable = [
        'purchase_return_id',
        'purchase_invoice_line_id',
        'line_number',
        'product_id',
        'product_name',
        'return_quantity',
        'accepted_quantity',
        'rejected_quantity',
        'unit',
        'unit_price',
        'tax_percentage',
        'tax_amount',
        'line_total',
        'inspection_notes',
    ];

    protected $casts = [
        'return_quantity' => 'decimal:3',
        'accepted_quantity' => 'decimal:3',
        'rejected_quantity' => 'decimal:3',
        'unit_price' => 'decimal:2',
        'tax_percentage' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'line_total' => 'decimal:2',
    ];

    public function purchaseReturn(): BelongsTo
    {
        return $this->belongsTo(PurchaseReturn::class);
    }

    public function purchaseInvoiceLine(): BelongsTo
    {
        return $this->belongsTo(PurchaseInvoiceLine::class);
    }

    public function calculateTotals(): void
    {
        $this->line_total = round((float) $this->return_quantity * (float) $this->unit_price, 2);
        $this->tax_amount = round((float) $this->line_total * ((float) $this->tax_percentage / 100), 2);
    }
}

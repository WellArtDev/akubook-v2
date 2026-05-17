<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchaseOrderLine extends Model
{
    use HasFactory;

    protected $fillable = [
        'purchase_order_id',
        'line_number',
        'product_code',
        'product_name',
        'description',
        'quantity',
        'unit',
        'unit_price',
        'tax_amount',
        'line_total',
        'received_quantity',
        'invoiced_quantity',
    ];

    protected $casts = [
        'quantity' => 'decimal:3',
        'unit_price' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'line_total' => 'decimal:2',
        'received_quantity' => 'decimal:3',
        'invoiced_quantity' => 'decimal:3',
    ];

    // Relationships
    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    // Business Logic
    public function calculateLineTotal(): void
    {
        $this->line_total = $this->quantity * $this->unit_price;
    }

    public function getRemainingQuantity(): float
    {
        return $this->quantity - $this->received_quantity;
    }

    public function getUninvoicedQuantity(): float
    {
        return $this->received_quantity - $this->invoiced_quantity;
    }

    protected static function booted(): void
    {
        static::saving(function (PurchaseOrderLine $line) {
            $line->calculateLineTotal();
        });

        static::saved(function (PurchaseOrderLine $line) {
            $line->purchaseOrder->calculateTotals();
        });

        static::deleted(function (PurchaseOrderLine $line) {
            $line->purchaseOrder->calculateTotals();
        });
    }
}

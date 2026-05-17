<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchaseRequestLine extends Model
{
    use HasFactory;

    protected $fillable = [
        'purchase_request_id',
        'line_number',
        'product_code',
        'product_name',
        'description',
        'quantity',
        'unit',
        'estimated_price',
        'line_total',
        'notes',
    ];

    protected $casts = [
        'quantity' => 'decimal:3',
        'estimated_price' => 'decimal:2',
        'line_total' => 'decimal:2',
    ];

    // Relationships
    public function purchaseRequest(): BelongsTo
    {
        return $this->belongsTo(PurchaseRequest::class);
    }

    // Business Logic
    public function calculateLineTotal(): void
    {
        $this->line_total = $this->quantity * $this->estimated_price;
    }

    protected static function booted(): void
    {
        static::saving(function (PurchaseRequestLine $line) {
            $line->calculateLineTotal();
        });

        static::saved(function (PurchaseRequestLine $line) {
            $line->purchaseRequest->calculateTotal();
        });

        static::deleted(function (PurchaseRequestLine $line) {
            $line->purchaseRequest->calculateTotal();
        });
    }
}

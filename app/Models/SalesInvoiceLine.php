<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SalesInvoiceLine extends Model
{
    use HasFactory;

    protected $fillable = [
        'sales_invoice_id',
        'sales_order_line_id',
        'line_number',
        'product_id',
        'product_name',
        'description',
        'quantity',
        'unit',
        'unit_price',
        'discount_amount',
        'tax_amount',
        'line_total',
    ];

    protected $casts = [
        'quantity' => 'decimal:3',
        'unit_price' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'line_total' => 'decimal:2',
    ];

    public function salesInvoice(): BelongsTo
    {
        return $this->belongsTo(SalesInvoice::class);
    }

    public function salesOrderLine(): BelongsTo
    {
        return $this->belongsTo(SalesOrderLine::class);
    }

    public function calculateLineTotal(): void
    {
        $subtotal = $this->quantity * $this->unit_price;
        $afterDiscount = $subtotal - $this->discount_amount;
        $this->line_total = $afterDiscount + $this->tax_amount;
    }

    public function calculateTax(float $taxRate = 0.11): void
    {
        $subtotal = $this->quantity * $this->unit_price;
        $afterDiscount = $subtotal - $this->discount_amount;
        $this->tax_amount = $afterDiscount * $taxRate;
    }
}

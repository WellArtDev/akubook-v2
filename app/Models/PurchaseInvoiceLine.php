<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchaseInvoiceLine extends Model
{
    use HasFactory;

    protected $fillable = [
        'purchase_invoice_id',
        'goods_receipt_line_id',
        'purchase_order_line_id',
        'line_number',
        'product_code',
        'product_name',
        'description',
        'ordered_quantity',
        'received_quantity',
        'previously_invoiced_quantity',
        'remaining_to_invoice_quantity',
        'invoice_quantity',
        'unit',
        'unit_price',
        'tax_percentage',
        'tax_amount',
        'line_total',
        'notes',
    ];

    protected $casts = [
        'ordered_quantity' => 'decimal:3',
        'received_quantity' => 'decimal:3',
        'previously_invoiced_quantity' => 'decimal:3',
        'remaining_to_invoice_quantity' => 'decimal:3',
        'invoice_quantity' => 'decimal:3',
        'unit_price' => 'decimal:2',
        'tax_percentage' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'line_total' => 'decimal:2',
    ];

    public function purchaseInvoice(): BelongsTo
    {
        return $this->belongsTo(PurchaseInvoice::class);
    }

    public function goodsReceiptLine(): BelongsTo
    {
        return $this->belongsTo(GoodsReceiptLine::class);
    }

    public function purchaseOrderLine(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrderLine::class);
    }

    public function calculateTotals(): void
    {
        $this->line_total = round((float) $this->invoice_quantity * (float) $this->unit_price, 2);
        $this->tax_amount = round((float) $this->line_total * ((float) $this->tax_percentage / 100), 2);
    }
}

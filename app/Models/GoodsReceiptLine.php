<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GoodsReceiptLine extends Model
{
    use HasFactory;

    protected $fillable = [
        'goods_receipt_id',
        'purchase_order_line_id',
        'line_number',
        'product_code',
        'product_name',
        'description',
        'po_quantity',
        'previously_received_quantity',
        'remaining_quantity',
        'receipt_quantity',
        'accepted_quantity',
        'rejected_quantity',
        'unit',
        'inspection_notes',
    ];

    protected $casts = [
        'po_quantity' => 'decimal:3',
        'previously_received_quantity' => 'decimal:3',
        'remaining_quantity' => 'decimal:3',
        'receipt_quantity' => 'decimal:3',
        'accepted_quantity' => 'decimal:3',
        'rejected_quantity' => 'decimal:3',
    ];

    public function goodsReceipt(): BelongsTo
    {
        return $this->belongsTo(GoodsReceipt::class);
    }

    public function purchaseOrderLine(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrderLine::class);
    }
}

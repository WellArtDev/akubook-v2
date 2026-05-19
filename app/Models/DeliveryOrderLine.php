<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeliveryOrderLine extends Model
{
    use HasFactory;

    protected $fillable = [
        'delivery_order_id',
        'sales_order_line_id',
        'line_number',
        'item_id',
        'description',
        'so_quantity',
        'previously_delivered_quantity',
        'remaining_quantity',
        'delivery_quantity',
        'unit',
        'notes',
    ];

    protected $casts = [
        'so_quantity' => 'decimal:3',
        'previously_delivered_quantity' => 'decimal:3',
        'remaining_quantity' => 'decimal:3',
        'delivery_quantity' => 'decimal:3',
    ];

    public function deliveryOrder(): BelongsTo
    {
        return $this->belongsTo(DeliveryOrder::class);
    }

    public function salesOrderLine(): BelongsTo
    {
        return $this->belongsTo(SalesOrderLine::class);
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }
}

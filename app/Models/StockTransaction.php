<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockTransaction extends Model
{
    use HasFactory;

    public const MOVEMENT_TYPES = [
        'purchase_receipt',
        'purchase_return',
        'sales_delivery',
        'sales_return',
        'adjustment',
        'transfer_out',
        'transfer_in',
    ];

    protected $fillable = [
        'item_id',
        'branch_id',
        'movement_type',
        'quantity_in',
        'quantity_out',
        'movement_date',
        'reference_type',
        'reference_id',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'branch_id' => 'integer',
        'quantity_in' => 'decimal:3',
        'quantity_out' => 'decimal:3',
        'movement_date' => 'date',
        'created_by' => 'integer',
    ];

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getNetQuantityAttribute(): float
    {
        return (float) $this->quantity_in - (float) $this->quantity_out;
    }
}

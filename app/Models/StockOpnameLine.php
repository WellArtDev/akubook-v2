<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockOpnameLine extends Model
{
    use HasFactory;

    protected $fillable = [
        'stock_opname_id',
        'item_id',
        'system_quantity',
        'physical_quantity',
        'variance_quantity',
        'notes',
    ];

    protected $casts = [
        'system_quantity' => 'decimal:3',
        'physical_quantity' => 'decimal:3',
        'variance_quantity' => 'decimal:3',
    ];

    public function opname(): BelongsTo
    {
        return $this->belongsTo(StockOpname::class, 'stock_opname_id');
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }
}

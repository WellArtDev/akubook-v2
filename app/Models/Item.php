<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'category',
        'description',
        'item_type',
        'inventory_type',
        'valuation_method',
        'unit',
        'purchase_price',
        'selling_price',
        'minimum_stock',
        'reorder_point',
        'is_active',
    ];

    protected $casts = [
        'purchase_price' => 'decimal:2',
        'selling_price' => 'decimal:2',
        'minimum_stock' => 'decimal:3',
        'reorder_point' => 'decimal:3',
        'is_active' => 'boolean',
    ];
}

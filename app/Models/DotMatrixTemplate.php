<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class DotMatrixTemplate extends Model
{
    use HasFactory, SoftDeletes;

    public const DOCUMENT_TYPES = [
        'sales_invoice',
        'delivery_order',
        'purchase_order',
        'goods_receipt',
    ];

    protected $fillable = [
        'name',
        'document_type',
        'paper_size',
        'columns',
        'rows',
        'margins',
        'field_map',
        'is_default',
        'is_active',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'columns' => 'integer',
        'rows' => 'integer',
        'margins' => 'array',
        'field_map' => 'array',
        'is_default' => 'boolean',
        'is_active' => 'boolean',
        'created_by' => 'integer',
        'updated_by' => 'integer',
    ];

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public static function defaultFieldMap(string $documentType): array
    {
        return match ($documentType) {
            'sales_invoice' => [
                ['field' => 'invoice_number', 'x' => 2, 'y' => 2],
                ['field' => 'invoice_date', 'x' => 55, 'y' => 2],
                ['field' => 'customer_name', 'x' => 2, 'y' => 4],
                ['field' => 'grand_total', 'x' => 55, 'y' => 58],
            ],
            'delivery_order' => [
                ['field' => 'do_number', 'x' => 2, 'y' => 2],
                ['field' => 'do_date', 'x' => 55, 'y' => 2],
                ['field' => 'customer_name', 'x' => 2, 'y' => 4],
                ['field' => 'status', 'x' => 55, 'y' => 4],
            ],
            'purchase_order' => [
                ['field' => 'po_number', 'x' => 2, 'y' => 2],
                ['field' => 'order_date', 'x' => 55, 'y' => 2],
                ['field' => 'supplier_name', 'x' => 2, 'y' => 4],
                ['field' => 'grand_total', 'x' => 55, 'y' => 58],
            ],
            'goods_receipt' => [
                ['field' => 'gr_number', 'x' => 2, 'y' => 2],
                ['field' => 'gr_date', 'x' => 55, 'y' => 2],
                ['field' => 'supplier_name', 'x' => 2, 'y' => 4],
                ['field' => 'status', 'x' => 55, 'y' => 4],
            ],
            default => [],
        };
    }
}

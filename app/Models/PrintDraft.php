<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class PrintDraft extends Model
{
    use HasFactory, SoftDeletes;

    public const DOCUMENT_TYPES = [
        'sales_invoice',
        'delivery_order',
        'purchase_request',
        'purchase_order',
        'goods_receipt',
        'purchase_invoice',
        'debit_note',
    ];

    protected $fillable = [
        'draft_number',
        'document_type',
        'document_id',
        'dot_matrix_template_id',
        'override_payload',
        'status',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'document_id' => 'integer',
        'dot_matrix_template_id' => 'integer',
        'override_payload' => 'array',
        'created_by' => 'integer',
        'updated_by' => 'integer',
    ];

    public static function generateNumber(): string
    {
        return DB::transaction(function () {
            $year = now()->year;
            $prefix = "PD-{$year}-";
            $last = self::where('draft_number', 'like', $prefix . '%')
                ->whereRaw('LENGTH(draft_number) = ?', [strlen($prefix) + 4])
                ->lockForUpdate()
                ->orderByDesc('draft_number')
                ->value('draft_number');

            $next = $last ? ((int) substr($last, -4)) + 1 : 1;

            return $prefix . str_pad((string) $next, 4, '0', STR_PAD_LEFT);
        });
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(DotMatrixTemplate::class, 'dot_matrix_template_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}

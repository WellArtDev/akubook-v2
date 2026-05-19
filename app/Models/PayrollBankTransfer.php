<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class PayrollBankTransfer extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'transfer_number',
        'period',
        'status',
        'row_count',
        'success_count',
        'failed_count',
        'total_amount',
        'csv_content',
        'metadata',
        'created_by',
        'generated_at',
    ];

    protected $casts = [
        'row_count' => 'integer',
        'success_count' => 'integer',
        'failed_count' => 'integer',
        'total_amount' => 'decimal:2',
        'metadata' => 'array',
        'generated_at' => 'datetime',
    ];

    public static function generateNumber(): string
    {
        $year = now()->format('Y');
        $prefix = "BT-$year-";

        return DB::transaction(function () use ($prefix) {
            $last = static::query()
                ->where('transfer_number', 'like', "$prefix%")
                ->whereRaw('LENGTH(transfer_number) = ?', [strlen($prefix) + 4])
                ->lockForUpdate()
                ->orderByDesc('transfer_number')
                ->value('transfer_number');

            $next = 1;
            if ($last) {
                $next = ((int) substr($last, -4)) + 1;
            }

            return $prefix . str_pad((string) $next, 4, '0', STR_PAD_LEFT);
        });
    }

    public function lines(): HasMany
    {
        return $this->hasMany(PayrollBankTransferLine::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}

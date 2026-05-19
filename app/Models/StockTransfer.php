<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class StockTransfer extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'transfer_number',
        'transfer_date',
        'from_branch_id',
        'to_branch_id',
        'status',
        'notes',
        'created_by',
        'confirmed_by',
        'confirmed_at',
    ];

    protected $casts = [
        'transfer_date' => 'date',
        'created_by' => 'integer',
        'confirmed_by' => 'integer',
        'confirmed_at' => 'datetime',
    ];

    public static function generateNumber(): string
    {
        return DB::transaction(function () {
            $year = now()->format('Y');
            $prefix = "TRF-{$year}-";

            $lastNumber = static::query()
                ->where('transfer_number', 'like', $prefix.'%')
                ->whereRaw('LENGTH(transfer_number) = ?', [strlen($prefix) + 4])
                ->lockForUpdate()
                ->orderByDesc('transfer_number')
                ->value('transfer_number');

            $sequence = $lastNumber
                ? ((int) substr($lastNumber, -4)) + 1
                : 1;

            return $prefix.str_pad((string) $sequence, 4, '0', STR_PAD_LEFT);
        });
    }

    public function fromBranch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'from_branch_id');
    }

    public function toBranch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'to_branch_id');
    }

    public function lines(): HasMany
    {
        return $this->hasMany(StockTransferLine::class)->orderBy('line_number');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function confirmer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'confirmed_by');
    }
}

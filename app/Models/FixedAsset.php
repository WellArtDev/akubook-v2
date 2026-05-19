<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class FixedAsset extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'asset_code',
        'name',
        'category',
        'acquisition_date',
        'acquisition_cost',
        'useful_life_months',
        'residual_value',
        'status',
        'asset_account_id',
        'accumulated_depreciation_account_id',
        'depreciation_expense_account_id',
        'notes',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'acquisition_date' => 'date',
        'acquisition_cost' => 'decimal:2',
        'residual_value' => 'decimal:2',
        'useful_life_months' => 'integer',
        'created_by' => 'integer',
        'updated_by' => 'integer',
    ];

    public static function generateCode(): string
    {
        return DB::transaction(function () {
            $year = now()->year;
            $prefix = "FA-{$year}-";

            $last = self::query()
                ->where('asset_code', 'like', "{$prefix}%")
                ->whereRaw('LENGTH(asset_code) = ?', [strlen($prefix) + 4])
                ->lockForUpdate()
                ->orderByDesc('asset_code')
                ->first();

            $next = $last ? ((int) substr($last->asset_code, -4)) + 1 : 1;

            return $prefix . str_pad((string) $next, 4, '0', STR_PAD_LEFT);
        });
    }

    public function assetAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'asset_account_id');
    }

    public function accumulatedDepreciationAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'accumulated_depreciation_account_id');
    }

    public function depreciationExpenseAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'depreciation_expense_account_id');
    }
}

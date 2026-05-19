<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class AssetDisposal extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'disposal_number',
        'disposal_date',
        'fixed_asset_id',
        'acquisition_cost',
        'accumulated_depreciation',
        'book_value',
        'proceeds_amount',
        'proceeds_account_id',
        'gain_loss_account_id',
        'status',
        'journal_entry_id',
        'notes',
        'created_by',
        'posted_by',
        'posted_at',
    ];

    protected $casts = [
        'disposal_date' => 'date',
        'acquisition_cost' => 'decimal:2',
        'accumulated_depreciation' => 'decimal:2',
        'book_value' => 'decimal:2',
        'proceeds_amount' => 'decimal:2',
        'posted_at' => 'datetime',
        'fixed_asset_id' => 'integer',
        'proceeds_account_id' => 'integer',
        'gain_loss_account_id' => 'integer',
        'journal_entry_id' => 'integer',
        'created_by' => 'integer',
        'posted_by' => 'integer',
    ];

    public static function generateNumber(): string
    {
        return DB::transaction(function () {
            $year = now()->year;
            $prefix = "AD-{$year}-";

            $last = self::query()
                ->where('disposal_number', 'like', "{$prefix}%")
                ->whereRaw('LENGTH(disposal_number) = ?', [strlen($prefix) + 4])
                ->lockForUpdate()
                ->orderByDesc('disposal_number')
                ->first();

            $next = $last ? ((int) substr($last->disposal_number, -4)) + 1 : 1;

            return $prefix . str_pad((string) $next, 4, '0', STR_PAD_LEFT);
        });
    }

    public function fixedAsset(): BelongsTo
    {
        return $this->belongsTo(FixedAsset::class);
    }

    public function proceedsAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'proceeds_account_id');
    }

    public function gainLossAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'gain_loss_account_id');
    }

    public function journalEntry(): BelongsTo
    {
        return $this->belongsTo(JournalEntry::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function poster(): BelongsTo
    {
        return $this->belongsTo(User::class, 'posted_by');
    }
}

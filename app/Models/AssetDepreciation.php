<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssetDepreciation extends Model
{
    use HasFactory;

    protected $fillable = [
        'fixed_asset_id',
        'period',
        'monthly_depreciation',
        'accumulated_depreciation',
        'book_value_end',
        'journal_entry_id',
        'journal_posted_at',
        'created_by',
    ];

    protected $casts = [
        'monthly_depreciation' => 'decimal:2',
        'accumulated_depreciation' => 'decimal:2',
        'book_value_end' => 'decimal:2',
        'journal_posted_at' => 'datetime',
        'fixed_asset_id' => 'integer',
        'journal_entry_id' => 'integer',
        'created_by' => 'integer',
    ];

    public function fixedAsset(): BelongsTo
    {
        return $this->belongsTo(FixedAsset::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function journalEntry(): BelongsTo
    {
        return $this->belongsTo(JournalEntry::class);
    }
}

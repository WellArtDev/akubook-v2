<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class StockOpname extends Model
{
    use HasFactory;

    protected $fillable = [
        'opname_number',
        'opname_date',
        'status',
        'notes',
        'created_by',
        'confirmed_by',
        'confirmed_at',
    ];

    protected $casts = [
        'opname_date' => 'date',
        'confirmed_at' => 'datetime',
    ];

    public static function generateNumber(): string
    {
        $year = now()->format('Y');
        $prefix = "OPN-{$year}-";

        return DB::transaction(function () use ($prefix) {
            $last = self::where('opname_number', 'like', $prefix . '%')
                ->whereRaw('LENGTH(opname_number) = ?', [strlen($prefix) + 4])
                ->lockForUpdate()
                ->orderByDesc('opname_number')
                ->value('opname_number');

            $next = $last ? ((int) substr($last, -4)) + 1 : 1;

            return $prefix . str_pad((string) $next, 4, '0', STR_PAD_LEFT);
        });
    }

    public function lines(): HasMany
    {
        return $this->hasMany(StockOpnameLine::class);
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

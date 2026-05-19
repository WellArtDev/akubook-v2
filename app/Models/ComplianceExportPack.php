<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

class ComplianceExportPack extends Model
{
    use HasFactory;

    protected $fillable = [
        'pack_number',
        'period_start',
        'period_end',
        'status',
        'record_counts',
        'metadata',
        'payload_json',
        'generated_by',
        'generated_at',
    ];

    protected $casts = [
        'period_start' => 'date',
        'period_end' => 'date',
        'record_counts' => 'array',
        'metadata' => 'array',
        'generated_by' => 'integer',
        'generated_at' => 'datetime',
    ];

    public static function generateNumber(): string
    {
        return DB::transaction(function () {
            $year = now()->year;
            $prefix = "CEP-{$year}-";

            $last = self::query()
                ->where('pack_number', 'like', "{$prefix}%")
                ->whereRaw('LENGTH(pack_number) = ?', [strlen($prefix) + 4])
                ->lockForUpdate()
                ->orderByDesc('pack_number')
                ->first();

            $next = $last ? ((int) substr($last->pack_number, -4)) + 1 : 1;

            return $prefix . str_pad((string) $next, 4, '0', STR_PAD_LEFT);
        });
    }

    public function generator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'generated_by');
    }
}

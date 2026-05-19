<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class EFakturExport extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'export_number',
        'period_start',
        'period_end',
        'status',
        'row_count',
        'csv_content',
        'metadata',
        'created_by',
        'generated_at',
    ];

    protected $casts = [
        'period_start' => 'date',
        'period_end' => 'date',
        'metadata' => 'array',
        'generated_at' => 'datetime',
        'created_by' => 'integer',
    ];

    public static function generateNumber(): string
    {
        return DB::transaction(function () {
            $year = now()->year;
            $prefix = "EF-{$year}-";

            $last = self::query()
                ->where('export_number', 'like', "{$prefix}%")
                ->whereRaw('LENGTH(export_number) = ?', [strlen($prefix) + 4])
                ->lockForUpdate()
                ->orderByDesc('export_number')
                ->first();

            $next = $last ? ((int) substr($last->export_number, -4)) + 1 : 1;

            return $prefix . str_pad((string) $next, 4, '0', STR_PAD_LEFT);
        });
    }

    public function lines(): HasMany
    {
        return $this->hasMany(EFakturExportLine::class)->orderBy('line_number');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class PayrollRun extends Model
{
    use HasFactory, SoftDeletes;

    public const STATUSES = ['draft', 'calculated'];

    protected $fillable = [
        'run_number',
        'period',
        'status',
        'total_earnings',
        'total_deductions',
        'total_gross_pay',
        'total_net_pay',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'total_earnings' => 'decimal:2',
        'total_deductions' => 'decimal:2',
        'total_gross_pay' => 'decimal:2',
        'total_net_pay' => 'decimal:2',
    ];

    public static function generateNumber(): string
    {
        return DB::transaction(function () {
            $year = now()->format('Y');
            $prefix = "PR-{$year}-";
            $latest = self::query()
                ->where('run_number', 'like', $prefix . '%')
                ->whereRaw('LENGTH(run_number) = ?', [strlen($prefix) + 4])
                ->lockForUpdate()
                ->orderByDesc('run_number')
                ->value('run_number');

            $number = $latest ? ((int) substr($latest, -4)) + 1 : 1;

            return $prefix . str_pad((string) $number, 4, '0', STR_PAD_LEFT);
        });
    }

    public function lines(): HasMany
    {
        return $this->hasMany(PayrollRunLine::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}

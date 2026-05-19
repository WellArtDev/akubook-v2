<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class FakturPajak extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'faktur_number',
        'faktur_date',
        'sales_invoice_id',
        'customer_id',
        'dpp',
        'ppn_amount',
        'grand_total',
        'status',
        'notes',
        'created_by',
        'issued_by',
        'issued_at',
        'cancelled_by',
        'cancelled_at',
    ];

    protected $casts = [
        'faktur_date' => 'date',
        'dpp' => 'decimal:2',
        'ppn_amount' => 'decimal:2',
        'grand_total' => 'decimal:2',
        'issued_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'sales_invoice_id' => 'integer',
        'customer_id' => 'integer',
        'created_by' => 'integer',
        'issued_by' => 'integer',
        'cancelled_by' => 'integer',
    ];

    public static function generateNumber(): string
    {
        return DB::transaction(function () {
            $year = now()->year;
            $prefix = "FP-{$year}-";

            $last = self::query()
                ->where('faktur_number', 'like', "{$prefix}%")
                ->whereRaw('LENGTH(faktur_number) = ?', [strlen($prefix) + 4])
                ->lockForUpdate()
                ->orderByDesc('faktur_number')
                ->first();

            $next = $last ? ((int) substr($last->faktur_number, -4)) + 1 : 1;

            return $prefix . str_pad((string) $next, 4, '0', STR_PAD_LEFT);
        });
    }

    public function salesInvoice(): BelongsTo
    {
        return $this->belongsTo(SalesInvoice::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}

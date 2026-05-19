<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class GoodsReceipt extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'gr_number',
        'gr_date',
        'purchase_order_id',
        'supplier_id',
        'receive_location_id',
        'status',
        'reference_number',
        'notes',
        'received_by',
        'received_at',
        'cancelled_by',
        'cancelled_at',
        'cancellation_reason',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'gr_date' => 'date',
        'received_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    protected $appends = [
        'can_edit',
        'can_receive',
        'can_cancel',
    ];

    public static function generateNumber(): string
    {
        return DB::transaction(function () {
            $year = now()->year;
            $prefix = "GR-{$year}-";
            $last = self::where('gr_number', 'like', $prefix . '%')
                ->whereRaw('LENGTH(gr_number) = ?', [strlen($prefix) + 4])
                ->lockForUpdate()
                ->orderByDesc('gr_number')
                ->value('gr_number');

            $next = $last ? ((int) substr($last, -4)) + 1 : 1;

            return $prefix . str_pad((string) $next, 4, '0', STR_PAD_LEFT);
        });
    }

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function receiveLocation(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'receive_location_id');
    }

    public function lines(): HasMany
    {
        return $this->hasMany(GoodsReceiptLine::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function receivedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    public function cancelledBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }

    public function getCanEditAttribute(): bool
    {
        return $this->status === 'draft';
    }

    public function getCanReceiveAttribute(): bool
    {
        return $this->status === 'draft' && $this->lines()->exists();
    }

    public function getCanCancelAttribute(): bool
    {
        return in_array($this->status, ['draft', 'received'], true);
    }
}

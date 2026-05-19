<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class DeliveryOrder extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'do_number',
        'do_date',
        'sales_order_id',
        'customer_id',
        'delivery_address_id',
        'delivery_date',
        'driver_name',
        'vehicle_number',
        'notes',
        'status',
        'received_by',
        'received_at',
        'signature_path',
        'pod_notes',
        'cancelled_by',
        'cancelled_at',
        'cancellation_reason',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'do_date' => 'date',
        'delivery_date' => 'date',
        'received_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    protected $appends = [
        'can_edit',
        'can_confirm',
        'can_ship',
        'can_deliver',
        'can_cancel',
    ];

    public static function generateNumber(): string
    {
        return DB::transaction(function () {
            $year = now()->year;
            $prefix = "DO-{$year}-";
            $last = self::where('do_number', 'like', $prefix . '%')
                ->whereRaw('LENGTH(do_number) = ?', [strlen($prefix) + 4])
                ->lockForUpdate()
                ->orderByDesc('do_number')
                ->value('do_number');

            $next = $last ? ((int) substr($last, -4)) + 1 : 1;

            return $prefix . str_pad((string) $next, 4, '0', STR_PAD_LEFT);
        });
    }

    public function salesOrder(): BelongsTo
    {
        return $this->belongsTo(SalesOrder::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function deliveryAddress(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'delivery_address_id');
    }

    public function lines(): HasMany
    {
        return $this->hasMany(DeliveryOrderLine::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function cancelledBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }

    public function getCanEditAttribute(): bool
    {
        return $this->status === 'draft';
    }

    public function getCanConfirmAttribute(): bool
    {
        return $this->status === 'draft' && $this->lines()->exists();
    }

    public function getCanShipAttribute(): bool
    {
        return $this->status === 'ready_to_ship';
    }

    public function getCanDeliverAttribute(): bool
    {
        return $this->status === 'in_transit';
    }

    public function getCanCancelAttribute(): bool
    {
        return !in_array($this->status, ['cancelled'], true);
    }
}

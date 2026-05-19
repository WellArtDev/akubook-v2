<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Customer extends Model
{
    use HasFactory, SoftDeletes, Auditable;

    protected $fillable = [
        'code',
        'name',
        'category',
        'tax_id',
        'tax_type',
        'phone',
        'email',
        'website',
        'credit_limit',
        'payment_terms',
        'outstanding_balance',
        'is_active',
        'notes',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'credit_limit' => 'decimal:2',
        'outstanding_balance' => 'decimal:2',
        'payment_terms' => 'integer',
    ];

    protected $appends = [
        'available_credit',
        'credit_status',
    ];

    public static function generateCode(): string
    {
        return DB::transaction(function () {
            $year = date('Y');
            $prefix = "CUST-{$year}-";

            $lastCustomer = self::where('code', 'like', "{$prefix}%")
                ->lockForUpdate()
                ->orderBy('code', 'desc')
                ->first();

            $lastNumber = $lastCustomer ? (int) substr($lastCustomer->code, -4) : 0;

            return $prefix . str_pad((string) ($lastNumber + 1), 4, '0', STR_PAD_LEFT);
        });
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($customer) {
            if (empty($customer->code)) {
                $customer->code = self::generateCode();
            }
        });
    }

    public function contacts(): HasMany
    {
        return $this->hasMany(CustomerContact::class);
    }

    public function addresses(): HasMany
    {
        return $this->hasMany(CustomerAddress::class);
    }

    public function salesOrders(): HasMany
    {
        return $this->hasMany(SalesOrder::class);
    }

    public function salesInvoices(): HasMany
    {
        return $this->hasMany(SalesInvoice::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(CustomerPayment::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function getAvailableCreditAttribute(): float
    {
        return (float) $this->credit_limit - (float) $this->outstanding_balance;
    }

    public function getCreditStatusAttribute(): string
    {
        if ($this->available_credit < 0) {
            return 'exceeded';
        }

        if ((float) $this->credit_limit > 0 && $this->available_credit < ((float) $this->credit_limit * 0.2)) {
            return 'warning';
        }

        return 'good';
    }

    public function primaryContact(): ?CustomerContact
    {
        return $this->contacts()->where('is_primary', true)->first();
    }

    public function defaultAddress(): ?CustomerAddress
    {
        return $this->addresses()->where('is_default', true)->first();
    }
}

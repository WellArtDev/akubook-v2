<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class SalesQuotation extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'quotation_number',
        'quotation_date',
        'valid_until',
        'customer_id',
        'customer_contact_id',
        'reference',
        'sales_person_id',
        'payment_terms',
        'delivery_terms',
        'notes',
        'status',
        'discount_type',
        'discount_value',
        'subtotal',
        'discount_amount',
        'subtotal_after_discount',
        'tax_amount',
        'grand_total',
        'original_quotation_id',
        'revision_number',
        'converted_to_sales_order_id',
        'sent_at',
        'approved_at',
        'rejected_at',
        'expired_at',
        'converted_at',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'quotation_date' => 'date',
        'valid_until' => 'date',
        'discount_value' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'subtotal_after_discount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'grand_total' => 'decimal:2',
        'sent_at' => 'datetime',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
        'expired_at' => 'datetime',
        'converted_at' => 'datetime',
    ];

    protected $appends = [
        'can_edit',
        'can_send',
        'can_convert',
        'can_revise',
        'is_expired',
    ];

    public static function generateQuotationNumber(): string
    {
        return DB::transaction(function (): string {
            $year = date('Y');
            $prefix = "QT-{$year}-";
            $lastQuotation = self::where('quotation_number', 'like', "{$prefix}%")
                ->whereRaw('LENGTH(quotation_number) = ?', [strlen($prefix) + 4])
                ->lockForUpdate()
                ->orderBy('quotation_number', 'desc')
                ->first();
            $lastNumber = $lastQuotation ? (int) substr($lastQuotation->quotation_number, -4) : 0;

            return $prefix . str_pad((string) ($lastNumber + 1), 4, '0', STR_PAD_LEFT);
        });
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function customerContact(): BelongsTo
    {
        return $this->belongsTo(CustomerContact::class);
    }

    public function salesPerson(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sales_person_id');
    }

    public function lines(): HasMany
    {
        return $this->hasMany(SalesQuotationLine::class)->orderBy('line_number');
    }

    public function originalQuotation(): BelongsTo
    {
        return $this->belongsTo(self::class, 'original_quotation_id');
    }

    public function revisions(): HasMany
    {
        return $this->hasMany(self::class, 'original_quotation_id')->orderBy('revision_number');
    }

    public function convertedSalesOrder(): BelongsTo
    {
        return $this->belongsTo(SalesOrder::class, 'converted_to_sales_order_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function getIsExpiredAttribute(): bool
    {
        return $this->status === 'sent' && $this->valid_until->isPast();
    }

    public function getCanEditAttribute(): bool
    {
        return $this->status === 'draft';
    }

    public function getCanSendAttribute(): bool
    {
        return $this->status === 'draft' && $this->lines()->count() > 0;
    }

    public function getCanConvertAttribute(): bool
    {
        return $this->status === 'approved' && $this->converted_to_sales_order_id === null;
    }

    public function getCanReviseAttribute(): bool
    {
        return in_array($this->status, ['sent', 'rejected'], true);
    }

    public function calculateTotals(): void
    {
        $this->loadMissing('lines');
        $this->subtotal = $this->lines->sum('line_total');
        $discountValue = (float) $this->discount_value;
        $this->discount_amount = $this->discount_type === 'percentage'
            ? round(((float) $this->subtotal * $discountValue) / 100, 2)
            : min($discountValue, (float) $this->subtotal);
        $this->subtotal_after_discount = max((float) $this->subtotal - (float) $this->discount_amount, 0);
        $this->tax_amount = round((float) $this->subtotal_after_discount * 0.11, 2);
        $this->grand_total = (float) $this->subtotal_after_discount + (float) $this->tax_amount;
    }
}

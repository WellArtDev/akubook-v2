<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class SalesOrder extends Model
{
    use HasFactory, SoftDeletes;

    protected static function booted(): void
    {
        static::creating(function (self $salesOrder): void {
            foreach (['subtotal', 'discount_amount', 'tax_amount', 'grand_total', 'total_amount'] as $field) {
                if ($salesOrder->{$field} === null) {
                    $salesOrder->{$field} = 0;
                }
            }
        });

        static::deleting(function (self $salesOrder): void {
            $salesOrder->lines()->delete();
        });
    }

    protected $fillable = [
        'sales_quotation_id',
        'so_number',
        'so_date',
        'customer_id',
        'branch_id',
        'customer_po_number',
        'sales_person_id',
        'payment_terms',
        'delivery_terms',
        'delivery_address_id',
        'requested_delivery_date',
        'notes',
        'status',
        'subtotal',
        'discount_amount',
        'tax_amount',
        'grand_total',
        'total_amount',
        'approval_required',
        'approved_by',
        'approved_at',
        'rejected_by',
        'rejected_at',
        'rejection_reason',
        'cancelled_by',
        'cancelled_at',
        'cancellation_reason',
        'credit_check_passed',
        'credit_check_notes',
        'created_by',
    ];

    protected $casts = [
        'so_date' => 'date',
        'requested_delivery_date' => 'date',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'sales_quotation_id' => 'integer',
        'subtotal' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'grand_total' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'approval_required' => 'boolean',
        'credit_check_passed' => 'boolean',
    ];

    public function quotation(): BelongsTo
    {
        return $this->belongsTo(SalesQuotation::class, 'sales_quotation_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function salesPerson(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sales_person_id');
    }

    public function deliveryAddress(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'delivery_address_id');
    }

    public function lines(): HasMany
    {
        return $this->hasMany(SalesOrderLine::class);
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function rejectedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'rejected_by');
    }

    public function cancelledBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }

    public function approvals(): HasMany
    {
        return $this->hasMany(SalesOrderApproval::class);
    }

    public function currentApproval()
    {
        return $this->hasOne(SalesOrderApproval::class)->latestOfMany();
    }

    public function approvalReasons(): array
    {
        $reasons = [];

        if ($this->grand_total > 10000000) {
            $reasons[] = [
                'type' => 'high_value',
                'message' => 'Order total exceeds Rp 10,000,000',
                'value' => (float) $this->grand_total,
            ];
        }

        $creditCheck = $this->checkCreditLimit();
        if (!$creditCheck['passed']) {
            $reasons[] = [
                'type' => 'credit_exceeded',
                'message' => $creditCheck['notes'],
            ];
        }

        return $reasons;
    }

    public function calculateTotals(): void
    {
        $this->subtotal = $this->lines->sum(function($line) {
            return $line->line_total - $line->discount_amount;
        });
        $this->discount_amount = $this->lines->sum('discount_amount');
        $this->tax_amount = $this->lines->sum('tax_amount');
        $this->grand_total = $this->subtotal + $this->tax_amount;
    }

    public function requiresApproval(): bool
    {
        return $this->grand_total > 10000000;
    }

    public function checkCreditLimit(): array
    {
        $customer = $this->customer;
        
        if (!$customer || $customer->credit_limit <= 0) {
            return ['passed' => true, 'notes' => null];
        }

        // Calculate customer's outstanding balance
        $outstanding = SalesOrder::where('customer_id', $customer->id)
            ->whereIn('status', ['approved', 'in_progress'])
            ->sum('grand_total');

        $availableCredit = $customer->credit_limit - $outstanding;
        $passed = $this->grand_total <= $availableCredit;

        $notes = $passed 
            ? null 
            : "Credit limit exceeded. Available: Rp " . number_format($availableCredit, 0, ',', '.') . 
              ", Required: Rp " . number_format($this->grand_total, 0, ',', '.');

        return ['passed' => $passed, 'notes' => $notes];
    }
}

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

    protected $fillable = [
        'so_number',
        'so_date',
        'customer_id',
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
        'approval_required',
        'approved_by',
        'approved_at',
        'credit_check_passed',
        'credit_check_notes',
        'created_by',
    ];

    protected $casts = [
        'so_date' => 'date',
        'requested_delivery_date' => 'date',
        'approved_at' => 'datetime',
        'subtotal' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'grand_total' => 'decimal:2',
        'approval_required' => 'boolean',
        'credit_check_passed' => 'boolean',
    ];

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

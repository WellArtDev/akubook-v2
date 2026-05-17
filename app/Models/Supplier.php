<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Supplier extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'supplier_code',
        'name',
        'category',
        'tax_id',
        'tax_type',
        'payment_terms',
        'phone',
        'email',
        'website',
        'notes',
        'delivery_rating',
        'quality_rating',
        'total_purchase_amount',
        'last_purchase_date',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'delivery_rating' => 'decimal:2',
        'quality_rating' => 'decimal:2',
        'total_purchase_amount' => 'decimal:2',
        'last_purchase_date' => 'date',
    ];

    /**
     * Generate supplier code: SUPP-YYYY-NNNN
     * Uses transaction lock to prevent race conditions
     */
    public static function generateSupplierCode(): string
    {
        return \DB::transaction(function () {
            $year = date('Y');
            $prefix = "SUPP-{$year}-";
            
            $lastSupplier = self::where('supplier_code', 'like', "{$prefix}%")
                ->lockForUpdate()
                ->orderBy('supplier_code', 'desc')
                ->first();
            
            if ($lastSupplier) {
                $lastNumber = (int) substr($lastSupplier->supplier_code, -4);
                $newNumber = $lastNumber + 1;
            } else {
                $newNumber = 1;
            }
            
            return $prefix . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
        });
    }

    /**
     * Boot method to auto-generate supplier code
     */
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($supplier) {
            if (empty($supplier->supplier_code)) {
                $supplier->supplier_code = self::generateSupplierCode();
            }
        });
    }

    /**
     * Relationships
     */
    public function contacts(): HasMany
    {
        return $this->hasMany(SupplierContact::class);
    }

    public function addresses(): HasMany
    {
        return $this->hasMany(SupplierAddress::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Get primary contact
     */
    public function primaryContact()
    {
        return $this->contacts()->where('is_primary', true)->first();
    }

    /**
     * Get default address
     */
    public function defaultAddress()
    {
        return $this->addresses()->where('is_default', true)->first();
    }
}

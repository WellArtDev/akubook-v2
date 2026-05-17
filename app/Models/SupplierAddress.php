<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SupplierAddress extends Model
{
    use HasFactory;

    protected $fillable = [
        'supplier_id',
        'address_type',
        'street_address',
        'city',
        'province',
        'postal_code',
        'country',
        'is_default',
    ];

    protected $casts = [
        'is_default' => 'boolean',
    ];

    /**
     * Relationships
     */
    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    /**
     * Boot method to ensure only one default address
     */
    protected static function boot()
    {
        parent::boot();
        
        static::saving(function ($address) {
            if ($address->is_default) {
                // Unset other default addresses for this supplier
                self::where('supplier_id', $address->supplier_id)
                    ->where('id', '!=', $address->id)
                    ->update(['is_default' => false]);
            }
        });
    }

    /**
     * Get full address string
     */
    public function getFullAddressAttribute(): string
    {
        $parts = array_filter([
            $this->street_address,
            $this->city,
            $this->province,
            $this->postal_code,
            $this->country,
        ]);
        
        return implode(', ', $parts);
    }
}

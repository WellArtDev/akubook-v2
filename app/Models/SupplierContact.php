<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SupplierContact extends Model
{
    use HasFactory;

    protected $fillable = [
        'supplier_id',
        'name',
        'position',
        'phone',
        'email',
        'is_primary',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
    ];

    /**
     * Relationships
     */
    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    /**
     * Boot method to ensure only one primary contact
     */
    protected static function boot()
    {
        parent::boot();
        
        static::saving(function ($contact) {
            if ($contact->is_primary) {
                // Unset other primary contacts for this supplier
                self::where('supplier_id', $contact->supplier_id)
                    ->where('id', '!=', $contact->id)
                    ->update(['is_primary' => false]);
            }
        });
    }
}

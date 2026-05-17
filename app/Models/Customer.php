<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use HasFactory, SoftDeletes, Auditable;

    protected $fillable = [
        'code',
        'name',
        'customer_type',
        'contact_person',
        'email',
        'phone',
        'address',
        'city',
        'tax_id',
        'credit_limit',
        'payment_terms_days',
        'is_active',
        'notes',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'credit_limit' => 'decimal:2',
        'payment_terms_days' => 'integer',
    ];
}

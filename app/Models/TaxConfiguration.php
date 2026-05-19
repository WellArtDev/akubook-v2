<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class TaxConfiguration extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'code',
        'name',
        'tax_type',
        'rate',
        'account_id',
        'is_default',
        'is_active',
        'description',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'rate' => 'decimal:4',
        'is_default' => 'boolean',
        'is_active' => 'boolean',
        'account_id' => 'integer',
        'created_by' => 'integer',
        'updated_by' => 'integer',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}

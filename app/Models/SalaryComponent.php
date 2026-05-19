<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class SalaryComponent extends Model
{
    use HasFactory, SoftDeletes;

    public const COMPONENT_TYPES = ['earning', 'deduction'];
    public const CALCULATION_METHODS = ['fixed', 'percentage'];

    protected $fillable = [
        'code',
        'name',
        'component_type',
        'calculation_method',
        'default_amount',
        'default_percentage',
        'is_taxable',
        'is_active',
        'account_id',
        'description',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'default_amount' => 'decimal:2',
        'default_percentage' => 'decimal:4',
        'is_taxable' => 'boolean',
        'is_active' => 'boolean',
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

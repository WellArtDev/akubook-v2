<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Employee extends Model
{
    use HasFactory, SoftDeletes;

    public const STATUSES = ['active', 'inactive', 'resigned'];

    protected $fillable = [
        'employee_id',
        'full_name',
        'email',
        'phone',
        'join_date',
        'employment_status',
        'department',
        'position',
        'bank_name',
        'bank_account_number',
        'bank_account_holder',
        'notes',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'join_date' => 'date',
        'created_by' => 'integer',
        'updated_by' => 'integer',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(EmployeeAssignment::class);
    }

    public function activeAssignment(): HasOne
    {
        return $this->hasOne(EmployeeAssignment::class)->where('status', 'active')->latestOfMany('effective_date');
    }

    public function shiftAssignments(): HasMany
    {
        return $this->hasMany(EmployeeShiftAssignment::class);
    }

    public function activeShiftAssignment(): HasOne
    {
        return $this->hasOne(EmployeeShiftAssignment::class)->where('status', 'active')->latestOfMany('effective_date');
    }
}

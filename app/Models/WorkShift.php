<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class WorkShift extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'shift_code',
        'name',
        'check_in_time',
        'check_out_time',
        'tolerance_minutes',
        'is_overnight',
        'is_active',
        'notes',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'check_in_time' => 'datetime:H:i:s',
        'check_out_time' => 'datetime:H:i:s',
        'tolerance_minutes' => 'integer',
        'is_overnight' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function assignments(): HasMany
    {
        return $this->hasMany(EmployeeShiftAssignment::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}

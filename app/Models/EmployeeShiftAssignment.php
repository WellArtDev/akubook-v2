<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmployeeShiftAssignment extends Model
{
    use HasFactory, SoftDeletes;

    public const STATUSES = ['active', 'inactive'];

    protected $fillable = [
        'employee_id',
        'work_shift_id',
        'effective_date',
        'status',
        'notes',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'effective_date' => 'date',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function shift(): BelongsTo
    {
        return $this->belongsTo(WorkShift::class, 'work_shift_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class LeaveRequest extends Model
{
    use HasFactory, SoftDeletes;

    public const STATUSES = ['pending', 'approved', 'rejected', 'cancelled'];

    protected $fillable = [
        'employee_id',
        'leave_type',
        'start_date',
        'end_date',
        'total_days',
        'reason',
        'status',
        'created_by',
        'approved_by',
        'approved_at',
        'rejected_by',
        'rejected_at',
        'rejection_reason',
        'cancelled_by',
        'cancelled_at',
        'cancellation_reason',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'total_days' => 'integer',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function rejector(): BelongsTo
    {
        return $this->belongsTo(User::class, 'rejected_by');
    }

    public function canceller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }
}

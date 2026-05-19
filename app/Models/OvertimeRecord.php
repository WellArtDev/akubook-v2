<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class OvertimeRecord extends Model
{
    use HasFactory, SoftDeletes;

    public const STATUSES = ['pending', 'approved', 'rejected', 'cancelled'];

    protected $fillable = [
        'employee_id',
        'attendance_record_id',
        'overtime_date',
        'start_at',
        'end_at',
        'hours',
        'status',
        'reason',
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
        'overtime_date' => 'date',
        'start_at' => 'datetime',
        'end_at' => 'datetime',
        'hours' => 'decimal:2',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function attendanceRecord(): BelongsTo
    {
        return $this->belongsTo(AttendanceRecord::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ZktecoAttendanceLog extends Model
{
    use HasFactory;

    public const PUNCH_TYPES = ['check_in', 'check_out'];

    protected $fillable = [
        'zkteco_device_id',
        'employee_identifier',
        'punch_at',
        'punch_type',
        'employee_id',
        'attendance_record_id',
        'is_mapped',
        'source_key',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'punch_at' => 'datetime',
        'employee_id' => 'integer',
        'attendance_record_id' => 'integer',
        'is_mapped' => 'boolean',
        'created_by' => 'integer',
    ];

    public function device(): BelongsTo
    {
        return $this->belongsTo(ZktecoDevice::class, 'zkteco_device_id');
    }

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
}

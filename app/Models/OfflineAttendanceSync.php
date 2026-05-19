<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OfflineAttendanceSync extends Model
{
    use HasFactory;

    public const CLOCK_TYPES = ['check_in', 'check_out'];
    public const STATUSES = ['pending', 'synced', 'failed'];

    protected $fillable = [
        'sync_key',
        'employee_id',
        'employee_identifier',
        'clock_type',
        'clock_at',
        'status',
        'source_type',
        'source_id',
        'failure_reason',
        'created_by',
    ];

    protected $casts = [
        'clock_at' => 'datetime',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}

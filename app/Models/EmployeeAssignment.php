<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmployeeAssignment extends Model
{
    use HasFactory, SoftDeletes;

    public const STATUSES = ['active', 'inactive'];

    protected $fillable = [
        'employee_id',
        'branch_id',
        'department',
        'position',
        'effective_date',
        'status',
        'notes',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'effective_date' => 'date',
        'created_by' => 'integer',
        'updated_by' => 'integer',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
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

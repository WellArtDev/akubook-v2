<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ZktecoDevice extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'device_code',
        'name',
        'ip_address',
        'port',
        'is_active',
        'notes',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'port' => 'integer',
        'is_active' => 'boolean',
        'created_by' => 'integer',
        'updated_by' => 'integer',
    ];

    public function logs(): HasMany
    {
        return $this->hasMany(ZktecoAttendanceLog::class);
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

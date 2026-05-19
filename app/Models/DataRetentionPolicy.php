<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DataRetentionPolicy extends Model
{
    use HasFactory;
    use SoftDeletes;

    public const ACTIONS = ['archive', 'delete'];

    protected $fillable = [
        'policy_key',
        'entity_type',
        'retention_days',
        'action',
        'is_active',
        'description',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'retention_days' => 'integer',
        'is_active' => 'boolean',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}

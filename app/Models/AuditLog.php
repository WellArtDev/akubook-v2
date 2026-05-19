<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'auditable_type',
        'auditable_id',
        'event',
        'event_key',
        'entity_type',
        'entity_id',
        'action',
        'actor_user_id',
        'is_sensitive',
        'sensitivity_level',
        'sensitivity_reason',
        'occurred_at',
        'ip_address',
        'user_agent',
        'old_values',
        'new_values',
        'metadata',
    ];

    protected $casts = [
        'is_sensitive' => 'boolean',
        'occurred_at' => 'datetime',
        'old_values' => 'array',
        'new_values' => 'array',
        'metadata' => 'array',
    ];

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_user_id');
    }
}

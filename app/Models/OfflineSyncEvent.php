<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class OfflineSyncEvent extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_event_id',
        'entity',
        'action',
        'payload',
        'encrypted_payload',
        'status',
        'failure_reason',
        'source_type',
        'source_id',
        'created_by',
    ];

    protected $casts = [
        'payload' => 'array',
        'encrypted_payload' => 'encrypted:array',
    ];

    public function source(): MorphTo
    {
        return $this->morphTo();
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SensitiveAlert extends Model
{
    use HasFactory;

    protected $fillable = [
        'idempotency_key',
        'window',
        'window_start',
        'window_end',
        'high_count',
        'threshold',
        'top_entities',
        'status',
        'generated_at',
        'generated_by',
    ];

    protected $casts = [
        'window_start' => 'datetime',
        'window_end' => 'datetime',
        'generated_at' => 'datetime',
        'high_count' => 'integer',
        'threshold' => 'integer',
        'top_entities' => 'array',
    ];

    public function generator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'generated_by');
    }
}

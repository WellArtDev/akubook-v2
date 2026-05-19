<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DashboardPreference extends Model
{
    use HasFactory;

    public const REFRESH_INTERVALS = [15, 30, 60, 120, 300];

    protected $fillable = [
        'user_id',
        'refresh_seconds',
        'auto_refresh_enabled',
    ];

    protected $casts = [
        'user_id' => 'integer',
        'refresh_seconds' => 'integer',
        'auto_refresh_enabled' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

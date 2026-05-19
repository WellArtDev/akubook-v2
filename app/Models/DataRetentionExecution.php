<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DataRetentionExecution extends Model
{
    use HasFactory;

    protected $fillable = [
        'data_retention_policy_id',
        'mode',
        'entity_type',
        'action',
        'cutoff_date',
        'candidate_count',
        'processed_count',
        'status',
        'summary',
        'created_by',
    ];

    protected $casts = [
        'cutoff_date' => 'date',
        'candidate_count' => 'integer',
        'processed_count' => 'integer',
        'summary' => 'array',
    ];

    public function policy(): BelongsTo
    {
        return $this->belongsTo(DataRetentionPolicy::class, 'data_retention_policy_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}

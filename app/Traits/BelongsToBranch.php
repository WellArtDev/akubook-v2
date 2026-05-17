<?php

namespace App\Traits;

use App\Models\Branch;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait BelongsToBranch
{
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function scopeForBranch($query, $branchId)
    {
        return $query->where('branch_id', $branchId);
    }

    public function scopeForCurrentUserBranch($query)
    {
        if (auth()->check() && auth()->user()->branch_id) {
            return $query->where('branch_id', auth()->user()->branch_id);
        }
        return $query;
    }
}

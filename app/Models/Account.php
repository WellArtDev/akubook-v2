<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Account extends Model
{
    use HasFactory, SoftDeletes, Auditable;

    protected $fillable = [
        'code',
        'name',
        'type',
        'category',
        'parent_id',
        'level',
        'is_header',
        'is_active',
        'description',
        'balance',
    ];

    protected $casts = [
        'is_header' => 'boolean',
        'is_active' => 'boolean',
        'balance' => 'decimal:2',
    ];

    public function parent()
    {
        return $this->belongsTo(Account::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Account::class, 'parent_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopeHeaders($query)
    {
        return $query->where('is_header', true);
    }

    public function scopeDetails($query)
    {
        return $query->where('is_header', false);
    }

    public function getNormalBalance(): string
    {
        // Determine normal balance based on account type
        return match($this->type) {
            'asset', 'expense' => 'debit',
            'liability', 'equity', 'revenue' => 'credit',
            default => 'debit',
        };
    }

    public function journalLines()
    {
        return $this->hasMany(JournalLine::class, 'account_id');
    }
}

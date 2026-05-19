<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BankReconciliationLine extends Model
{
    use HasFactory;

    protected $fillable = [
        'bank_reconciliation_id',
        'line_number',
        'transaction_date',
        'description',
        'debit',
        'credit',
        'reference_number',
        'is_matched',
        'matched_reference_type',
        'matched_reference_id',
        'matched_at',
        'matched_by',
        'notes',
    ];

    protected $casts = [
        'bank_reconciliation_id' => 'integer',
        'line_number' => 'integer',
        'transaction_date' => 'date',
        'debit' => 'decimal:2',
        'credit' => 'decimal:2',
        'is_matched' => 'boolean',
        'matched_reference_id' => 'integer',
        'matched_at' => 'datetime',
        'matched_by' => 'integer',
    ];

    public function reconciliation()
    {
        return $this->belongsTo(BankReconciliation::class, 'bank_reconciliation_id');
    }

    public function matcher()
    {
        return $this->belongsTo(User::class, 'matched_by');
    }
}

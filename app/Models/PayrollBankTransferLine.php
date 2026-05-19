<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PayrollBankTransferLine extends Model
{
    use HasFactory;

    protected $fillable = [
        'payroll_bank_transfer_id',
        'employee_id',
        'line_number',
        'employee_code',
        'employee_name',
        'bank_name',
        'bank_account_number',
        'bank_account_holder',
        'amount',
        'status',
        'failure_reason',
    ];

    protected $casts = [
        'line_number' => 'integer',
        'amount' => 'decimal:2',
    ];

    public function transfer(): BelongsTo
    {
        return $this->belongsTo(PayrollBankTransfer::class, 'payroll_bank_transfer_id');
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}

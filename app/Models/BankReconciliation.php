<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class BankReconciliation extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'reconciliation_number',
        'bank_account_id',
        'statement_start_date',
        'statement_end_date',
        'reconciliation_date',
        'statement_opening_balance',
        'statement_closing_balance',
        'matched_debit_total',
        'matched_credit_total',
        'system_balance',
        'difference',
        'status',
        'notes',
        'created_by',
        'reconciled_by',
        'reconciled_at',
    ];

    protected $casts = [
        'bank_account_id' => 'integer',
        'statement_start_date' => 'date',
        'statement_end_date' => 'date',
        'reconciliation_date' => 'date',
        'statement_opening_balance' => 'decimal:2',
        'statement_closing_balance' => 'decimal:2',
        'matched_debit_total' => 'decimal:2',
        'matched_credit_total' => 'decimal:2',
        'system_balance' => 'decimal:2',
        'difference' => 'decimal:2',
        'created_by' => 'integer',
        'reconciled_by' => 'integer',
        'reconciled_at' => 'datetime',
    ];

    public static function generateNumber(): string
    {
        return DB::transaction(function () {
            $year = now()->year;
            $prefix = "BRC-{$year}-";
            $last = static::where('reconciliation_number', 'like', $prefix.'%')
                ->whereRaw('LENGTH(reconciliation_number) = ?', [strlen($prefix) + 4])
                ->lockForUpdate()
                ->orderByDesc('reconciliation_number')
                ->first();
            $next = $last ? ((int) substr($last->reconciliation_number, -4)) + 1 : 1;

            return $prefix.str_pad((string) $next, 4, '0', STR_PAD_LEFT);
        });
    }

    public function bankAccount()
    {
        return $this->belongsTo(BankAccount::class);
    }

    public function lines()
    {
        return $this->hasMany(BankReconciliationLine::class)->orderBy('line_number');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function reconciler()
    {
        return $this->belongsTo(User::class, 'reconciled_by');
    }

    public function recalculate(): void
    {
        $this->loadMissing('lines');
        $matchedDebit = $this->lines->where('is_matched', true)->sum('debit');
        $matchedCredit = $this->lines->where('is_matched', true)->sum('credit');
        $systemBalance = (float) $this->statement_opening_balance + $matchedDebit - $matchedCredit;

        $this->matched_debit_total = $matchedDebit;
        $this->matched_credit_total = $matchedCredit;
        $this->system_balance = $systemBalance;
        $this->difference = (float) $this->statement_closing_balance - $systemBalance;
    }
}

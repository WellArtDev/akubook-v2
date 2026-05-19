<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class Voucher extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'voucher_number',
        'voucher_type',
        'voucher_date',
        'cash_bank_type',
        'cash_bank_account_id',
        'counterpart_account_id',
        'amount',
        'reference_number',
        'notes',
        'status',
        'journal_entry_id',
        'created_by',
        'updated_by',
        'posted_by',
        'posted_at',
        'cancelled_by',
        'cancelled_at',
    ];

    protected $casts = [
        'voucher_date' => 'date',
        'cash_bank_account_id' => 'integer',
        'counterpart_account_id' => 'integer',
        'amount' => 'decimal:2',
        'journal_entry_id' => 'integer',
        'created_by' => 'integer',
        'updated_by' => 'integer',
        'posted_by' => 'integer',
        'posted_at' => 'datetime',
        'cancelled_by' => 'integer',
        'cancelled_at' => 'datetime',
    ];

    public static function generateNumber(string $type): string
    {
        $prefix = $type === 'payment' ? 'PV-' : 'RV-';
        $year = now()->format('Y');
        $fullPrefix = $prefix.$year.'-';

        return DB::transaction(function () use ($fullPrefix) {
            $last = static::query()
                ->where('voucher_number', 'like', $fullPrefix.'%')
                ->whereRaw('LENGTH(voucher_number) = ?', [strlen($fullPrefix) + 4])
                ->lockForUpdate()
                ->orderByDesc('voucher_number')
                ->value('voucher_number');

            $next = 1;
            if ($last) {
                $next = ((int) substr($last, -4)) + 1;
            }

            return $fullPrefix.str_pad((string) $next, 4, '0', STR_PAD_LEFT);
        });
    }

    public function post(): bool
    {
        if ($this->status !== 'draft') {
            return false;
        }

        return DB::transaction(function () {
            $journalEntry = $this->createJournalEntry();

            $this->update([
                'status' => 'posted',
                'journal_entry_id' => $journalEntry->id,
                'posted_by' => Auth::id(),
                'posted_at' => now(),
                'updated_by' => Auth::id(),
            ]);

            return true;
        });
    }

    public function cashAccount(): BelongsTo
    {
        return $this->belongsTo(CashAccount::class, 'cash_bank_account_id');
    }

    public function bankAccount(): BelongsTo
    {
        return $this->belongsTo(BankAccount::class, 'cash_bank_account_id');
    }

    public function counterpartAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'counterpart_account_id');
    }

    public function journalEntry(): BelongsTo
    {
        return $this->belongsTo(JournalEntry::class);
    }

    public function createJournalEntry(): JournalEntry
    {
        $fiscalPeriod = FiscalPeriod::query()
            ->where('status', 'open')
            ->whereDate('start_date', '<=', $this->voucher_date)
            ->whereDate('end_date', '>=', $this->voucher_date)
            ->firstOrFail();

        $cashBankGl = $this->cash_bank_type === 'cash'
            ? CashAccount::query()->findOrFail($this->cash_bank_account_id)->account_id
            : BankAccount::query()->findOrFail($this->cash_bank_account_id)->account_id;

        $journalEntry = JournalEntry::query()->create([
            'journal_number' => $this->generateJournalNumber(),
            'journal_date' => $this->voucher_date,
            'fiscal_period_id' => $fiscalPeriod->id,
            'type' => $this->voucher_type === 'payment' ? 'auto_payment' : 'auto_receipt',
            'reference_type' => 'voucher',
            'reference_id' => $this->id,
            'description' => strtoupper($this->voucher_type).' VOUCHER '.$this->voucher_number,
            'total_debit' => $this->amount,
            'total_credit' => $this->amount,
            'status' => 'posted',
            'posted_at' => now(),
            'posted_by' => Auth::id(),
            'created_by' => Auth::id(),
        ]);

        if ($this->voucher_type === 'payment') {
            JournalEntryLine::query()->create([
                'journal_entry_id' => $journalEntry->id,
                'account_id' => $this->counterpart_account_id,
                'description' => 'Payment Voucher '.$this->voucher_number,
                'debit' => $this->amount,
                'credit' => 0,
            ]);

            JournalEntryLine::query()->create([
                'journal_entry_id' => $journalEntry->id,
                'account_id' => $cashBankGl,
                'description' => 'Payment Voucher '.$this->voucher_number,
                'debit' => 0,
                'credit' => $this->amount,
            ]);
        } else {
            JournalEntryLine::query()->create([
                'journal_entry_id' => $journalEntry->id,
                'account_id' => $cashBankGl,
                'description' => 'Receipt Voucher '.$this->voucher_number,
                'debit' => $this->amount,
                'credit' => 0,
            ]);

            JournalEntryLine::query()->create([
                'journal_entry_id' => $journalEntry->id,
                'account_id' => $this->counterpart_account_id,
                'description' => 'Receipt Voucher '.$this->voucher_number,
                'debit' => 0,
                'credit' => $this->amount,
            ]);
        }

        return $journalEntry;
    }

    private function generateJournalNumber(): string
    {
        $prefix = 'JV-'.now()->format('Ym').'-';

        $last = JournalEntry::query()
            ->where('journal_number', 'like', $prefix.'%')
            ->whereRaw('LENGTH(journal_number) = ?', [strlen($prefix) + 4])
            ->orderByDesc('journal_number')
            ->value('journal_number');

        $next = $last ? ((int) substr($last, -4)) + 1 : 1;

        return $prefix.str_pad((string) $next, 4, '0', STR_PAD_LEFT);
    }
}

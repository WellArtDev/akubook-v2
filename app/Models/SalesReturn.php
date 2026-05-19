<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class SalesReturn extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'rma_number',
        'return_date',
        'sales_invoice_id',
        'customer_id',
        'return_reason',
        'status',
        'subtotal',
        'tax_amount',
        'total_amount',
        'journal_entry_id',
        'approved_by',
        'approved_at',
        'received_by',
        'received_at',
        'completed_by',
        'completed_at',
        'rejected_by',
        'rejected_at',
        'rejection_reason',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'return_date' => 'date',
        'subtotal' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'approved_at' => 'datetime',
        'received_at' => 'datetime',
        'completed_at' => 'datetime',
        'rejected_at' => 'datetime',
    ];

    public static function generateRmaNumber(): string
    {
        return DB::transaction(function () {
            $year = now()->year;
            $prefix = "RMA-{$year}-";
            $lastReturn = self::where('rma_number', 'like', "{$prefix}%")
                ->whereRaw('LENGTH(rma_number) = ?', [strlen($prefix) + 4])
                ->lockForUpdate()
                ->orderByDesc('rma_number')
                ->first();

            $lastNumber = $lastReturn ? (int) substr($lastReturn->rma_number, -4) : 0;

            return $prefix . str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        });
    }

    public function salesInvoice(): BelongsTo
    {
        return $this->belongsTo(SalesInvoice::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function lines(): HasMany
    {
        return $this->hasMany(SalesReturnLine::class);
    }

    public function journalEntry(): BelongsTo
    {
        return $this->belongsTo(JournalEntry::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function calculateTotals(): void
    {
        $this->subtotal = $this->lines->sum('line_total');
        $this->tax_amount = $this->lines->sum('tax_amount');
        $this->total_amount = $this->subtotal + $this->tax_amount;
    }

    public function createJournalEntry(): void
    {
        if ($this->journal_entry_id) {
            return;
        }

        $fiscalPeriod = FiscalPeriod::where('is_current', true)->where('status', 'open')->first();
        $salesReturnAccount = Account::where('code', '4-9000')->first() ?? Account::where('code', '4-1000')->first();
        $arAccount = Account::where('code', '1-1300')->first();

        if (!$fiscalPeriod || !$salesReturnAccount || !$arAccount) {
            return;
        }

        $journalEntry = JournalEntry::create([
            'journal_number' => $this->generateJournalNumber(),
            'journal_date' => $this->return_date,
            'fiscal_period_id' => $fiscalPeriod->id,
            'type' => 'auto_sales',
            'reference_type' => 'sales_return',
            'reference_id' => $this->id,
            'description' => "Sales Return {$this->rma_number} - {$this->customer->name}",
            'total_debit' => $this->total_amount,
            'total_credit' => $this->total_amount,
            'status' => 'posted',
            'posted_at' => now(),
            'posted_by' => auth()->id(),
            'created_by' => $this->created_by,
        ]);

        $journalEntry->lines()->create([
            'account_id' => $salesReturnAccount->id,
            'description' => "Sales return {$this->rma_number}",
            'debit' => $this->total_amount,
            'credit' => 0,
        ]);

        $journalEntry->lines()->create([
            'account_id' => $arAccount->id,
            'description' => "Credit customer AR {$this->customer->name}",
            'debit' => 0,
            'credit' => $this->total_amount,
        ]);

        $this->journal_entry_id = $journalEntry->id;
        $this->save();
    }

    private function generateJournalNumber(): string
    {
        $year = now()->year;
        $month = now()->format('m');
        $prefix = "JV-{$year}{$month}-";
        $lastJournal = JournalEntry::where('journal_number', 'like', "{$prefix}%")
            ->orderByDesc('journal_number')
            ->first();
        $lastNumber = $lastJournal ? (int) substr($lastJournal->journal_number, -4) : 0;

        return $prefix . str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
    }
}

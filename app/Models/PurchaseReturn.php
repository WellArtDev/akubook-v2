<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PurchaseReturn extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'return_number',
        'return_date',
        'purchase_invoice_id',
        'purchase_order_id',
        'supplier_id',
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

    public static function generateNumber(): string
    {
        return DB::transaction(function () {
            $year = now()->year;
            $prefix = "PRET-{$year}-";
            $last = self::where('return_number', 'like', $prefix . '%')
                ->whereRaw('LENGTH(return_number) = ?', [strlen($prefix) + 4])
                ->lockForUpdate()
                ->orderByDesc('return_number')
                ->value('return_number');
            $next = $last ? ((int) substr($last, -4)) + 1 : 1;

            return $prefix . str_pad((string) $next, 4, '0', STR_PAD_LEFT);
        });
    }

    public function purchaseInvoice(): BelongsTo
    {
        return $this->belongsTo(PurchaseInvoice::class);
    }

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function lines(): HasMany
    {
        return $this->hasMany(PurchaseReturnLine::class);
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
        $this->loadMissing('lines');
        $this->subtotal = $this->lines->sum(fn ($line) => (float) $line->line_total);
        $this->tax_amount = $this->lines->sum(fn ($line) => (float) $line->tax_amount);
        $this->total_amount = (float) $this->subtotal + (float) $this->tax_amount;
    }

    public function createJournalEntry(): ?JournalEntry
    {
        if ($this->journal_entry_id) {
            return $this->journalEntry;
        }

        $period = FiscalPeriod::where('status', 'open')
            ->whereDate('start_date', '<=', $this->return_date)
            ->whereDate('end_date', '>=', $this->return_date)
            ->first();

        $apAccount = Account::where('code', '2-1100')->first();
        $purchaseReturnAccount = Account::where('code', '5-9000')->first() ?? Account::where('code', '1-1400')->first();
        $taxAccount = Account::where('code', '1-1500')->first();

        if (!$period || !$apAccount || !$purchaseReturnAccount) {
            return null;
        }

        $journal = JournalEntry::create([
            'journal_number' => $this->generateJournalNumber(),
            'journal_date' => $this->return_date,
            'fiscal_period_id' => $period->id,
            'type' => 'auto_purchase',
            'reference_type' => 'purchase_return',
            'reference_id' => $this->id,
            'description' => 'Purchase Return ' . $this->return_number . ' - ' . $this->supplier?->name,
            'total_debit' => $this->total_amount,
            'total_credit' => $this->total_amount,
            'status' => 'posted',
            'posted_at' => now(),
            'posted_by' => Auth::id(),
            'created_by' => Auth::id(),
        ]);

        $journal->lines()->create([
            'account_id' => $apAccount->id,
            'description' => 'Debit AP from ' . $this->return_number,
            'debit' => $this->total_amount,
            'credit' => 0,
        ]);

        $journal->lines()->create([
            'account_id' => $purchaseReturnAccount->id,
            'description' => 'Purchase return from ' . $this->return_number,
            'debit' => 0,
            'credit' => $this->subtotal,
        ]);

        if ((float) $this->tax_amount > 0 && $taxAccount) {
            $journal->lines()->create([
                'account_id' => $taxAccount->id,
                'description' => 'Input tax reduction from ' . $this->return_number,
                'debit' => 0,
                'credit' => $this->tax_amount,
            ]);
        }

        $this->update(['journal_entry_id' => $journal->id]);

        return $journal;
    }

    private function generateJournalNumber(): string
    {
        $prefix = 'JV-' . now()->format('Ym') . '-';
        $last = JournalEntry::where('journal_number', 'like', $prefix . '%')
            ->whereRaw('LENGTH(journal_number) = ?', [strlen($prefix) + 4])
            ->orderByDesc('journal_number')
            ->value('journal_number');
        $next = $last ? ((int) substr($last, -4)) + 1 : 1;

        return $prefix . str_pad((string) $next, 4, '0', STR_PAD_LEFT);
    }
}

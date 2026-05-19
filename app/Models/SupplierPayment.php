<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class SupplierPayment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'payment_number',
        'payment_date',
        'supplier_id',
        'payment_method',
        'bank_account_id',
        'reference_number',
        'total_amount',
        'allocated_amount',
        'unapplied_amount',
        'status',
        'journal_entry_id',
        'notes',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'payment_date' => 'date',
        'total_amount' => 'decimal:2',
        'allocated_amount' => 'decimal:2',
        'unapplied_amount' => 'decimal:2',
    ];

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function allocations(): HasMany
    {
        return $this->hasMany(SupplierPaymentAllocation::class);
    }

    public function journalEntry(): BelongsTo
    {
        return $this->belongsTo(JournalEntry::class);
    }

    public static function generatePaymentNumber(): string
    {
        return DB::transaction(function () {
            $year = now()->year;
            $prefix = "SPAY-{$year}-";
            $last = self::where('payment_number', 'like', $prefix . '%')
                ->whereRaw('LENGTH(payment_number) = ?', [strlen($prefix) + 4])
                ->lockForUpdate()
                ->orderByDesc('payment_number')
                ->value('payment_number');

            $next = $last ? ((int) substr($last, -4)) + 1 : 1;

            return $prefix . str_pad((string) $next, 4, '0', STR_PAD_LEFT);
        });
    }

    public function allocateToInvoices(array $allocations): void
    {
        DB::transaction(function () use ($allocations) {
            $totalAllocated = 0;

            foreach ($allocations as $allocation) {
                $invoice = PurchaseInvoice::findOrFail($allocation['purchase_invoice_id']);
                $amount = (float) $allocation['allocated_amount'];

                if ($amount > (float) $invoice->outstanding_amount) {
                    throw new \RuntimeException('Allocation amount exceeds outstanding amount.');
                }

                $this->allocations()->create([
                    'purchase_invoice_id' => $invoice->id,
                    'allocated_amount' => $amount,
                ]);

                $invoice->paid_amount = (float) $invoice->paid_amount + $amount;
                $invoice->outstanding_amount = max((float) $invoice->total_amount - (float) $invoice->paid_amount, 0);
                $invoice->status = $invoice->outstanding_amount <= 0 ? 'paid' : 'partially_paid';
                $invoice->save();

                $totalAllocated += $amount;
            }

            $this->allocated_amount = $totalAllocated;
            $this->unapplied_amount = (float) $this->total_amount - $totalAllocated;
            $this->save();
        });
    }

    public function post(): void
    {
        if ($this->status !== 'draft') {
            throw new \RuntimeException('Only draft payments can be posted.');
        }

        DB::transaction(function () {
            $this->status = 'posted';
            $this->save();
            $this->createJournalEntry();
        });
    }

    public function createJournalEntry(): void
    {
        if ($this->journal_entry_id) {
            return;
        }

        $period = FiscalPeriod::where('is_current', true)
            ->where('status', 'open')
            ->first();
        $apAccount = Account::where('code', '2-1100')->first();
        $cashAccount = Account::where('code', '1-1100')->first();

        if (!$period || !$apAccount || !$cashAccount) {
            throw new \RuntimeException('Required fiscal period or accounts not found.');
        }

        $journal = JournalEntry::create([
            'journal_number' => $this->generateJournalNumber(),
            'journal_date' => $this->payment_date,
            'fiscal_period_id' => $period->id,
            'type' => 'auto_payment',
            'reference_type' => 'supplier_payment',
            'reference_id' => $this->id,
            'description' => 'Supplier Payment ' . $this->payment_number . ' - ' . $this->supplier?->name,
            'total_debit' => $this->total_amount,
            'total_credit' => $this->total_amount,
            'status' => 'posted',
            'posted_at' => now(),
            'posted_by' => auth()->id(),
            'created_by' => $this->created_by,
        ]);

        $journal->lines()->create([
            'account_id' => $apAccount->id,
            'description' => 'AP payment ' . $this->payment_number,
            'debit' => $this->total_amount,
            'credit' => 0,
        ]);

        $journal->lines()->create([
            'account_id' => $cashAccount->id,
            'description' => 'Cash out ' . $this->payment_number,
            'debit' => 0,
            'credit' => $this->total_amount,
        ]);

        $this->journal_entry_id = $journal->id;
        $this->save();
    }

    private function generateJournalNumber(): string
    {
        $prefix = 'JV-' . now()->format('Ym') . '-';
        $last = JournalEntry::where('journal_number', 'like', $prefix . '%')
            ->orderByDesc('journal_number')
            ->value('journal_number');
        $next = $last ? ((int) substr($last, -4)) + 1 : 1;

        return $prefix . str_pad((string) $next, 4, '0', STR_PAD_LEFT);
    }
}

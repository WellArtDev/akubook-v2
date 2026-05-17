<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class CustomerPayment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'payment_number',
        'payment_date',
        'customer_id',
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

    // Relationships
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function allocations(): HasMany
    {
        return $this->hasMany(CustomerPaymentAllocation::class);
    }

    public function journalEntry(): BelongsTo
    {
        return $this->belongsTo(JournalEntry::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    // Business Logic
    public static function generatePaymentNumber(): string
    {
        $year = now()->year;
        $prefix = "PAY-{$year}-";
        
        $lastPayment = self::where('payment_number', 'like', "{$prefix}%")
            ->orderBy('payment_number', 'desc')
            ->first();
        
        if (!$lastPayment) {
            return $prefix . '0001';
        }
        
        $lastNumber = (int) substr($lastPayment->payment_number, -4);
        $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        
        return $prefix . $newNumber;
    }

    public function allocateToInvoices(array $allocations): void
    {
        DB::beginTransaction();
        try {
            $totalAllocated = 0;

            foreach ($allocations as $allocation) {
                $invoice = SalesInvoice::findOrFail($allocation['sales_invoice_id']);
                $amount = $allocation['allocated_amount'];

                // Validate allocation amount
                if ($amount > $invoice->amount_due) {
                    throw new \Exception("Allocation amount exceeds invoice amount due");
                }

                // Create allocation
                $this->allocations()->create([
                    'sales_invoice_id' => $invoice->id,
                    'allocated_amount' => $amount,
                ]);

                // Update invoice payment
                $invoice->recordPayment($amount, $this->payment_method);

                $totalAllocated += $amount;
            }

            // Update payment amounts
            $this->allocated_amount = $totalAllocated;
            $this->unapplied_amount = $this->total_amount - $totalAllocated;
            $this->save();

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function post(): bool
    {
        if ($this->status !== 'draft') {
            return false;
        }

        DB::beginTransaction();
        try {
            $this->status = 'posted';
            $this->save();

            // Create journal entry
            $this->createJournalEntry();

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function createJournalEntry(): void
    {
        if ($this->journal_entry_id) {
            return;
        }

        $fiscalPeriod = FiscalPeriod::where('is_current', true)
            ->where('status', 'open')
            ->first();

        if (!$fiscalPeriod) {
            throw new \Exception('No open fiscal period found');
        }

        // Get accounts
        $cashAccount = Account::where('code', '1-1100')->first(); // Kas & Bank
        $arAccount = Account::where('code', '1-1200')->first(); // Piutang Usaha
        $unappliedAccount = Account::where('code', '2-1300')->first(); // Unapplied Cash (if exists)

        if (!$cashAccount || !$arAccount) {
            throw new \Exception('Required accounts not found');
        }

        // Create journal entry
        $journalEntry = JournalEntry::create([
            'journal_number' => $this->generateJournalNumber(),
            'journal_date' => $this->payment_date,
            'fiscal_period_id' => $fiscalPeriod->id,
            'type' => 'auto_receipt',
            'reference_type' => 'customer_payment',
            'reference_id' => $this->id,
            'description' => "Customer Payment {$this->payment_number} - {$this->customer->name}",
            'total_debit' => $this->total_amount,
            'total_credit' => $this->total_amount,
            'status' => 'posted',
            'posted_at' => now(),
            'posted_by' => auth()->id(),
            'created_by' => $this->created_by,
        ]);

        // DR: Cash/Bank
        $journalEntry->lines()->create([
            'account_id' => $cashAccount->id,
            'description' => "Payment from {$this->customer->name}",
            'debit' => $this->total_amount,
            'credit' => 0,
        ]);

        // CR: Accounts Receivable (allocated amount)
        if ($this->allocated_amount > 0) {
            $journalEntry->lines()->create([
                'account_id' => $arAccount->id,
                'description' => "Payment allocation",
                'debit' => 0,
                'credit' => $this->allocated_amount,
            ]);
        }

        // CR: Unapplied Cash (if any)
        if ($this->unapplied_amount > 0 && $unappliedAccount) {
            $journalEntry->lines()->create([
                'account_id' => $unappliedAccount->id,
                'description' => "Unapplied cash",
                'debit' => 0,
                'credit' => $this->unapplied_amount,
            ]);
        } elseif ($this->unapplied_amount > 0) {
            // If no unapplied account, credit to AR
            $journalEntry->lines()->create([
                'account_id' => $arAccount->id,
                'description' => "Unapplied payment",
                'debit' => 0,
                'credit' => $this->unapplied_amount,
            ]);
        }

        $this->journal_entry_id = $journalEntry->id;
        $this->save();
    }

    private function generateJournalNumber(): string
    {
        $year = now()->year;
        $month = now()->format('m');
        $prefix = "JV-{$year}{$month}-";
        
        $lastJournal = JournalEntry::where('journal_number', 'like', "{$prefix}%")
            ->orderBy('journal_number', 'desc')
            ->first();
        
        if (!$lastJournal) {
            return $prefix . '0001';
        }
        
        $lastNumber = (int) substr($lastJournal->journal_number, -4);
        $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        
        return $prefix . $newNumber;
    }

    public function void(int $userId, string $reason): bool
    {
        if ($this->status === 'reconciled') {
            return false; // Cannot void reconciled payments
        }

        DB::beginTransaction();
        try {
            // Reverse allocations
            foreach ($this->allocations as $allocation) {
                $invoice = $allocation->salesInvoice;
                $invoice->amount_paid -= $allocation->allocated_amount;
                $invoice->amount_due += $allocation->allocated_amount;
                $invoice->updateStatus();
                $invoice->save();
            }

            // Delete allocations
            $this->allocations()->delete();

            // Reverse journal entry if exists
            if ($this->journal_entry_id) {
                $this->reverseJournalEntry($userId, $reason);
            }

            $this->status = 'voided';
            $this->notes = ($this->notes ? $this->notes . "\n\n" : '') . "VOIDED: {$reason}";
            $this->save();

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    private function reverseJournalEntry(int $userId, string $reason): void
    {
        $originalJournal = $this->journalEntry;
        
        if (!$originalJournal) {
            return;
        }

        $fiscalPeriod = FiscalPeriod::where('is_current', true)
            ->where('status', 'open')
            ->first();

        if (!$fiscalPeriod) {
            throw new \Exception('No open fiscal period found for reversal');
        }

        $reversalJournal = JournalEntry::create([
            'journal_number' => $this->generateJournalNumber(),
            'journal_date' => now()->toDateString(),
            'fiscal_period_id' => $fiscalPeriod->id,
            'type' => 'manual',
            'reference_type' => 'customer_payment',
            'reference_id' => $this->id,
            'description' => "Reversal of Payment {$this->payment_number} - {$reason}",
            'total_debit' => $this->total_amount,
            'total_credit' => $this->total_amount,
            'status' => 'posted',
            'posted_at' => now(),
            'posted_by' => $userId,
            'created_by' => $userId,
        ]);

        foreach ($originalJournal->lines as $line) {
            $reversalJournal->lines()->create([
                'account_id' => $line->account_id,
                'description' => "Reversal - {$line->description}",
                'debit' => $line->credit,
                'credit' => $line->debit,
            ]);
        }
    }
}

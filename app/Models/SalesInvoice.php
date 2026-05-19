<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class SalesInvoice extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'invoice_number',
        'invoice_date',
        'due_date',
        'sales_order_id',
        'customer_id',
        'billing_address',
        'tax_invoice_number',
        'payment_terms',
        'reference',
        'notes',
        'status',
        'subtotal',
        'discount_amount',
        'tax_amount',
        'grand_total',
        'amount_paid',
        'amount_due',
        'journal_entry_id',
        'sent_at',
        'cancelled_by',
        'cancelled_at',
        'cancellation_reason',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'invoice_date' => 'date',
        'due_date' => 'date',
        'sent_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'subtotal' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'grand_total' => 'decimal:2',
        'amount_paid' => 'decimal:2',
        'amount_due' => 'decimal:2',
    ];

    // Relationships
    public function salesOrder(): BelongsTo
    {
        return $this->belongsTo(SalesOrder::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function lines(): HasMany
    {
        return $this->hasMany(SalesInvoiceLine::class);
    }

    public function journalEntry(): BelongsTo
    {
        return $this->belongsTo(JournalEntry::class);
    }

    public function cancelledBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    // Business Logic Methods
    public static function generateInvoiceNumber(): string
    {
        $year = now()->year;
        $prefix = "INV-{$year}-";
        
        $lastInvoice = self::where('invoice_number', 'like', "{$prefix}%")
            ->orderBy('invoice_number', 'desc')
            ->first();
        
        if (!$lastInvoice) {
            return $prefix . '0001';
        }
        
        $lastNumber = (int) substr($lastInvoice->invoice_number, -4);
        $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        
        return $prefix . $newNumber;
    }

    public static function generateTaxInvoiceNumber(): string
    {
        $year = now()->year;
        $prefix = "FP-{$year}-";
        
        $lastInvoice = self::where('tax_invoice_number', 'like', "{$prefix}%")
            ->orderBy('tax_invoice_number', 'desc')
            ->first();
        
        if (!$lastInvoice) {
            return $prefix . '0001';
        }
        
        $lastNumber = (int) substr($lastInvoice->tax_invoice_number, -4);
        $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        
        return $prefix . $newNumber;
    }

    public function calculateTotals(): void
    {
        $this->subtotal = $this->lines->sum(function($line) {
            return ($line->quantity * $line->unit_price) - $line->discount_amount;
        });
        
        $this->discount_amount = $this->lines->sum('discount_amount');
        $this->tax_amount = $this->lines->sum('tax_amount');
        $this->grand_total = $this->subtotal + $this->tax_amount;
        $this->amount_due = $this->grand_total - $this->amount_paid;
    }

    public function send(): bool
    {
        if ($this->status !== 'draft') {
            return false;
        }

        $this->status = 'sent';
        $this->sent_at = now();
        $this->save();

        return true;
    }

    public function post(): bool
    {
        if ($this->status !== 'draft') {
            return false;
        }

        DB::beginTransaction();
        try {
            $this->status = 'sent';
            $this->sent_at = now();
            $this->save();

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
            return; // Already has journal entry
        }

        // Get current fiscal period
        $fiscalPeriod = FiscalPeriod::where('is_current', true)
            ->where('status', 'open')
            ->first();

        if (!$fiscalPeriod) {
            return;
        }

        $arAccount = Account::where('code', '1-1300')->first();
        $salesAccount = Account::where('code', '4-1000')->first();
        $taxAccount = Account::where('code', '2-1200')->first();

        if (!$arAccount || !$salesAccount || !$taxAccount) {
            return;
        }

        // Create journal entry
        $journalEntry = JournalEntry::create([
            'journal_number' => $this->generateJournalNumber(),
            'journal_date' => $this->invoice_date,
            'fiscal_period_id' => $fiscalPeriod->id,
            'type' => 'auto_sales',
            'reference_type' => 'sales_invoice',
            'reference_id' => $this->id,
            'description' => "Sales Invoice {$this->invoice_number} - {$this->customer->name}",
            'total_debit' => $this->grand_total,
            'total_credit' => $this->grand_total,
            'status' => 'posted',
            'posted_at' => now(),
            'posted_by' => auth()->id(),
            'created_by' => $this->created_by,
        ]);

        // Create journal lines
        // DR: Accounts Receivable
        $journalEntry->lines()->create([
            'account_id' => $arAccount->id,
            'description' => "Invoice {$this->invoice_number}",
            'debit' => $this->grand_total,
            'credit' => 0,
        ]);

        // CR: Sales Revenue
        $journalEntry->lines()->create([
            'account_id' => $salesAccount->id,
            'description' => "Sales - {$this->customer->name}",
            'debit' => 0,
            'credit' => $this->subtotal,
        ]);

        // CR: Tax Payable (PPN)
        if ($this->tax_amount > 0) {
            $journalEntry->lines()->create([
                'account_id' => $taxAccount->id,
                'description' => "PPN 11% - Invoice {$this->invoice_number}",
                'debit' => 0,
                'credit' => $this->tax_amount,
            ]);
        }

        // Link journal entry to invoice
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

    public function recordPayment(float $amount, string $paymentMethod): void
    {
        $this->amount_paid += $amount;
        $this->amount_due = $this->grand_total - $this->amount_paid;
        
        $this->updateStatus();
        $this->save();
    }

    public function updateStatus(): void
    {
        if ($this->amount_paid >= $this->grand_total) {
            $this->status = 'paid';
        } elseif ($this->amount_paid > 0) {
            $this->status = 'partially_paid';
        } elseif ($this->due_date < now() && $this->status === 'sent') {
            $this->status = 'overdue';
        }
    }

    public function cancel(int $userId, string $reason): bool
    {
        if ($this->amount_paid > 0) {
            return false; // Cannot cancel if has payments
        }

        DB::beginTransaction();
        try {
            // Reverse journal entry if exists
            if ($this->journal_entry_id) {
                $this->reverseJournalEntry($userId);
            }

            $this->status = 'cancelled';
            $this->cancelled_by = $userId;
            $this->cancelled_at = now();
            $this->cancellation_reason = $reason;
            $this->save();

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    private function reverseJournalEntry(int $userId): void
    {
        $originalJournal = $this->journalEntry;
        
        if (!$originalJournal) {
            return;
        }

        // Get current fiscal period
        $fiscalPeriod = FiscalPeriod::where('is_current', true)
            ->where('status', 'open')
            ->first();

        if (!$fiscalPeriod) {
            throw new \Exception('No open fiscal period found for reversal');
        }

        // Create reversal journal entry
        $reversalJournal = JournalEntry::create([
            'journal_number' => $this->generateJournalNumber(),
            'journal_date' => now()->toDateString(),
            'fiscal_period_id' => $fiscalPeriod->id,
            'type' => 'manual',
            'reference_type' => 'sales_invoice',
            'reference_id' => $this->id,
            'description' => "Reversal of Invoice {$this->invoice_number} - {$this->cancellation_reason}",
            'total_debit' => $this->grand_total,
            'total_credit' => $this->grand_total,
            'status' => 'posted',
            'posted_at' => now(),
            'posted_by' => $userId,
            'created_by' => $userId,
        ]);

        // Create reversal lines (opposite of original)
        foreach ($originalJournal->lines as $line) {
            $reversalJournal->lines()->create([
                'account_id' => $line->account_id,
                'description' => "Reversal - {$line->description}",
                'debit' => $line->credit, // Swap debit/credit
                'credit' => $line->debit,
            ]);
        }
    }
}

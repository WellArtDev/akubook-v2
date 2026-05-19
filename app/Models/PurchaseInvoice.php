<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PurchaseInvoice extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'invoice_number',
        'invoice_date',
        'due_date',
        'goods_receipt_id',
        'purchase_order_id',
        'supplier_id',
        'supplier_invoice_number',
        'tax_invoice_number',
        'generate_tax_invoice',
        'notes',
        'status',
        'subtotal',
        'tax_amount',
        'total_amount',
        'paid_amount',
        'outstanding_amount',
        'journal_entry_id',
        'posted_at',
        'posted_by',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'invoice_date' => 'date',
        'due_date' => 'date',
        'generate_tax_invoice' => 'boolean',
        'subtotal' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'outstanding_amount' => 'decimal:2',
        'posted_at' => 'datetime',
    ];

    public static function generateNumber(): string
    {
        return DB::transaction(function () {
            $year = now()->year;
            $prefix = "PINV-{$year}-";
            $last = self::where('invoice_number', 'like', $prefix . '%')
                ->whereRaw('LENGTH(invoice_number) = ?', [strlen($prefix) + 4])
                ->lockForUpdate()
                ->orderByDesc('invoice_number')
                ->value('invoice_number');

            $next = $last ? ((int) substr($last, -4)) + 1 : 1;

            return $prefix . str_pad((string) $next, 4, '0', STR_PAD_LEFT);
        });
    }

    public function goodsReceipt(): BelongsTo
    {
        return $this->belongsTo(GoodsReceipt::class);
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
        return $this->hasMany(PurchaseInvoiceLine::class);
    }

    public function journalEntry(): BelongsTo
    {
        return $this->belongsTo(JournalEntry::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function postedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'posted_by');
    }

    public function calculateTotals(): void
    {
        $this->loadMissing('lines');
        $this->subtotal = $this->lines->sum(fn ($line) => (float) $line->line_total);
        $this->tax_amount = $this->lines->sum(fn ($line) => (float) $line->tax_amount);
        $this->total_amount = (float) $this->subtotal + (float) $this->tax_amount;
        $this->outstanding_amount = max((float) $this->total_amount - (float) $this->paid_amount, 0);
    }

    public function post(): void
    {
        abort_unless($this->status === 'draft', 403, 'Only draft purchase invoices can be posted.');

        DB::transaction(function () {
            $this->loadMissing('lines.purchaseOrderLine', 'supplier');
            foreach ($this->lines as $line) {
                $poLine = $line->purchaseOrderLine;
                $poLine->update([
                    'invoiced_quantity' => (float) $poLine->invoiced_quantity + (float) $line->invoice_quantity,
                ]);
            }

            $this->status = 'posted';
            $this->posted_by = Auth::id();
            $this->posted_at = now();
            $this->calculateTotals();
            $this->save();
            $this->createJournalEntry();
        });
    }

    public function createJournalEntry(): ?JournalEntry
    {
        if ($this->journal_entry_id) {
            return $this->journalEntry;
        }

        $period = FiscalPeriod::where('status', 'open')
            ->whereDate('start_date', '<=', $this->invoice_date)
            ->whereDate('end_date', '>=', $this->invoice_date)
            ->first();

        $inventoryAccount = Account::where('code', '1-1400')->first();
        $taxAccount = Account::where('code', '1-1500')->first();
        $apAccount = Account::where('code', '2-1100')->first();

        if (!$period || !$inventoryAccount || !$taxAccount || !$apAccount) {
            return null;
        }

        $journal = JournalEntry::create([
            'journal_number' => $this->generateJournalNumber(),
            'journal_date' => $this->invoice_date,
            'fiscal_period_id' => $period->id,
            'type' => 'auto_purchase',
            'reference_type' => 'purchase_invoice',
            'reference_id' => $this->id,
            'description' => 'Purchase Invoice ' . $this->invoice_number . ' - ' . $this->supplier?->name,
            'total_debit' => $this->total_amount,
            'total_credit' => $this->total_amount,
            'status' => 'posted',
            'posted_at' => now(),
            'posted_by' => Auth::id(),
            'created_by' => Auth::id(),
        ]);

        $journal->lines()->create([
            'account_id' => $inventoryAccount->id,
            'description' => 'Inventory/expense from ' . $this->invoice_number,
            'debit' => $this->subtotal,
            'credit' => 0,
        ]);

        if ((float) $this->tax_amount > 0) {
            $journal->lines()->create([
                'account_id' => $taxAccount->id,
                'description' => 'Input tax from ' . $this->invoice_number,
                'debit' => $this->tax_amount,
                'credit' => 0,
            ]);
        }

        $journal->lines()->create([
            'account_id' => $apAccount->id,
            'description' => 'Accounts payable from ' . $this->invoice_number,
            'debit' => 0,
            'credit' => $this->total_amount,
        ]);

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

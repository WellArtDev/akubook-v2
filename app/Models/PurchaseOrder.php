<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class PurchaseOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'po_number',
        'po_date',
        'supplier_id',
        'delivery_address_id',
        'payment_terms',
        'delivery_terms',
        'expected_delivery_date',
        'notes',
        'status',
        'subtotal',
        'tax_amount',
        'grand_total',
        'approval_required',
        'approved_by',
        'approved_at',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'po_date' => 'date',
        'expected_delivery_date' => 'date',
        'approved_at' => 'datetime',
        'subtotal' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'grand_total' => 'decimal:2',
        'approval_required' => 'boolean',
    ];

    // Relationships
    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function deliveryAddress(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'delivery_address_id');
    }

    public function lines(): HasMany
    {
        return $this->hasMany(PurchaseOrderLine::class);
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
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
    public static function generatePONumber(): string
    {
        return DB::transaction(function () {
            $year = now()->year;
            $prefix = "PO-{$year}-";

            $lastPO = self::where('po_number', 'like', "{$prefix}%")
                ->lockForUpdate()
                ->orderBy('po_number', 'desc')
                ->first();

            if ($lastPO) {
                $lastNumber = (int) substr($lastPO->po_number, -4);
                $newNumber = $lastNumber + 1;
            } else {
                $newNumber = 1;
            }

            return $prefix . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
        });
    }

    public function calculateTotals(): void
    {
        $this->subtotal = $this->lines->sum('line_total');
        $this->tax_amount = $this->lines->sum('tax_amount');
        $this->grand_total = $this->subtotal + $this->tax_amount;
        $this->approval_required = $this->requiresApproval();
        $this->save();
    }

    public function requiresApproval(): bool
    {
        return $this->grand_total > 10000000;
    }

    public function submit(): void
    {
        if ($this->status !== 'draft') {
            throw new \Exception('Only draft POs can be submitted');
        }

        if ($this->requiresApproval()) {
            $this->status = 'pending_approval';
        } else {
            $this->status = 'approved';
            $this->approved_at = now();
        }
        
        $this->save();
    }

    public function approve(int $userId): void
    {
        if ($this->status !== 'pending_approval') {
            throw new \Exception('Only pending POs can be approved');
        }

        $this->status = 'approved';
        $this->approved_by = $userId;
        $this->approved_at = now();
        $this->save();
    }

    public function reject(int $userId): void
    {
        if ($this->status !== 'pending_approval') {
            throw new \Exception('Only pending POs can be rejected');
        }

        $this->status = 'draft';
        $this->approved_by = $userId;
        $this->approved_at = now();
        $this->save();
    }

    public function cancel(): void
    {
        if (!in_array($this->status, ['draft', 'pending_approval', 'approved'])) {
            throw new \Exception('Cannot cancel PO in current status');
        }

        $this->status = 'cancelled';
        $this->save();
    }

    public function markInProgress(): void
    {
        if ($this->status !== 'approved') {
            throw new \Exception('Only approved POs can be marked in progress');
        }

        $this->status = 'in_progress';
        $this->save();
    }

    public function markCompleted(): void
    {
        if ($this->status !== 'in_progress') {
            throw new \Exception('Only in-progress POs can be completed');
        }

        // Check if all lines are fully received
        foreach ($this->lines as $line) {
            if ($line->received_quantity < $line->quantity) {
                throw new \Exception('Cannot complete PO with unreceived items');
            }
        }

        $this->status = 'completed';
        $this->save();
    }

    public static function createFromPRs(array $prIds, array $poData): self
    {
        return DB::transaction(function () use ($prIds, $poData) {
            // Get approved PRs
            $prs = PurchaseRequest::whereIn('id', $prIds)
                ->where('status', 'approved')
                ->with('lines')
                ->get();

            if ($prs->isEmpty()) {
                throw new \Exception('No approved PRs found');
            }

            // Create PO
            $po = self::create([
                'po_number' => self::generatePONumber(),
                'po_date' => $poData['po_date'],
                'supplier_id' => $poData['supplier_id'],
                'delivery_address_id' => $poData['delivery_address_id'] ?? null,
                'payment_terms' => $poData['payment_terms'] ?? null,
                'delivery_terms' => $poData['delivery_terms'] ?? null,
                'expected_delivery_date' => $poData['expected_delivery_date'] ?? null,
                'notes' => $poData['notes'] ?? null,
                'status' => 'draft',
                'created_by' => auth()->id(),
            ]);

            // Copy lines from PRs
            $lineNumber = 1;
            foreach ($prs as $pr) {
                foreach ($pr->lines as $prLine) {
                    PurchaseOrderLine::create([
                        'purchase_order_id' => $po->id,
                        'line_number' => $lineNumber++,
                        'product_code' => $prLine->product_code,
                        'product_name' => $prLine->product_name,
                        'description' => $prLine->description,
                        'quantity' => $prLine->quantity,
                        'unit' => $prLine->unit,
                        'unit_price' => $prLine->estimated_price,
                    ]);
                }

                // Mark PR as converted
                $pr->markAsConverted();
            }

            return $po;
        });
    }
}

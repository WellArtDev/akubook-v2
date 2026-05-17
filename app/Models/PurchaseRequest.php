<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class PurchaseRequest extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'pr_number',
        'pr_date',
        'department_id',
        'required_date',
        'justification',
        'status',
        'total_estimated_amount',
        'approved_by',
        'approved_at',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'pr_date' => 'date',
        'required_date' => 'date',
        'total_estimated_amount' => 'decimal:2',
        'approved_at' => 'datetime',
    ];

    // Relationships
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function lines(): HasMany
    {
        return $this->hasMany(PurchaseRequestLine::class);
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
    public static function generatePRNumber(): string
    {
        return DB::transaction(function () {
            $year = now()->year;
            $prefix = "PR-{$year}-";

            // Lock for update to prevent race conditions
            $lastPR = self::where('pr_number', 'like', "{$prefix}%")
                ->lockForUpdate()
                ->orderBy('pr_number', 'desc')
                ->first();

            if ($lastPR) {
                $lastNumber = (int) substr($lastPR->pr_number, -4);
                $newNumber = $lastNumber + 1;
            } else {
                $newNumber = 1;
            }

            return $prefix . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
        });
    }

    public function calculateTotal(): void
    {
        $this->total_estimated_amount = $this->lines->sum('line_total');
        $this->save();
    }

    public function submit(): void
    {
        if ($this->status !== 'draft') {
            throw new \Exception('Only draft PRs can be submitted');
        }

        $this->status = 'pending_approval';
        $this->save();
    }

    public function approve(int $userId): void
    {
        if ($this->status !== 'pending_approval') {
            throw new \Exception('Only pending PRs can be approved');
        }

        $this->status = 'approved';
        $this->approved_by = $userId;
        $this->approved_at = now();
        $this->save();
    }

    public function reject(int $userId): void
    {
        if ($this->status !== 'pending_approval') {
            throw new \Exception('Only pending PRs can be rejected');
        }

        $this->status = 'rejected';
        $this->approved_by = $userId;
        $this->approved_at = now();
        $this->save();
    }

    public function cancel(): void
    {
        if (!in_array($this->status, ['draft', 'pending_approval', 'approved'])) {
            throw new \Exception('Cannot cancel PR in current status');
        }

        $this->status = 'cancelled';
        $this->save();
    }

    public function markAsConverted(): void
    {
        if ($this->status !== 'approved') {
            throw new \Exception('Only approved PRs can be converted');
        }

        $this->status = 'converted';
        $this->save();
    }
}

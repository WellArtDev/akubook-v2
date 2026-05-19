<?php

namespace App\Http\Controllers;

use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderApproval;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class PurchaseOrderApprovalController extends Controller
{
    public function index(Request $request)
    {
        $query = PurchaseOrderApproval::with(['purchaseOrder.supplier', 'submitter'])
            ->where('status', 'pending');

        if ($request->filled('reason_type')) {
            $query->whereJsonContains('approval_reasons', [['type' => $request->reason_type]]);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('purchaseOrder', function ($purchaseOrderQuery) use ($search) {
                $purchaseOrderQuery->where('po_number', 'ilike', "%{$search}%")
                    ->orWhereHas('supplier', function ($supplierQuery) use ($search) {
                        $supplierQuery->where('name', 'ilike', "%{$search}%");
                    });
            });
        }

        $approvals = $query->orderBy('submitted_at', 'asc')->paginate(50)->withQueryString();

        $metrics = [
            'pending_count' => PurchaseOrderApproval::where('status', 'pending')->count(),
            'approval_rate' => $this->approvalRate(),
            'avg_approval_time_hours' => $this->averageApprovalTimeHours(),
        ];

        return Inertia::render('PurchaseOrderApprovals/Index', [
            'approvals' => $approvals,
            'metrics' => $metrics,
            'filters' => $request->only(['reason_type', 'search']),
        ]);
    }

    public function show(PurchaseOrderApproval $purchaseOrderApproval)
    {
        $purchaseOrderApproval->load([
            'purchaseOrder.supplier',
            'purchaseOrder.lines',
            'purchaseOrder.deliveryAddress',
            'purchaseOrder.createdBy',
            'submitter',
            'reviewer',
        ]);

        return Inertia::render('PurchaseOrderApprovals/Show', [
            'approval' => $purchaseOrderApproval,
        ]);
    }

    public function approve(Request $request, PurchaseOrderApproval $purchaseOrderApproval)
    {
        if ($purchaseOrderApproval->status !== 'pending') {
            return back()->withErrors(['error' => 'Approval sudah diproses.']);
        }

        if ($purchaseOrderApproval->submitted_by === auth()->id()) {
            return back()->withErrors(['error' => 'Tidak bisa approve PO buatan sendiri.']);
        }

        $validated = $request->validate([
            'comments' => 'nullable|string',
        ]);

        DB::transaction(function () use ($purchaseOrderApproval, $validated) {
            $purchaseOrderApproval->update([
                'status' => 'approved',
                'reviewed_by' => auth()->id(),
                'reviewed_at' => now(),
                'comments' => $validated['comments'] ?? null,
            ]);

            $purchaseOrderApproval->purchaseOrder->update([
                'status' => 'approved',
                'approved_by' => auth()->id(),
                'approved_at' => now(),
            ]);
        });

        return redirect()->route('purchase-order-approvals.index')->with('success', 'Purchase order berhasil diapprove.');
    }

    public function reject(Request $request, PurchaseOrderApproval $purchaseOrderApproval)
    {
        if ($purchaseOrderApproval->status !== 'pending') {
            return back()->withErrors(['error' => 'Approval sudah diproses.']);
        }

        if ($purchaseOrderApproval->submitted_by === auth()->id()) {
            return back()->withErrors(['error' => 'Tidak bisa reject PO buatan sendiri.']);
        }

        $validated = $request->validate([
            'rejection_reason' => 'required|string|max:1000',
            'comments' => 'nullable|string',
        ]);

        DB::transaction(function () use ($purchaseOrderApproval, $validated) {
            $purchaseOrderApproval->update([
                'status' => 'rejected',
                'reviewed_by' => auth()->id(),
                'reviewed_at' => now(),
                'rejection_reason' => $validated['rejection_reason'],
                'comments' => $validated['comments'] ?? null,
            ]);

            $purchaseOrderApproval->purchaseOrder->update([
                'status' => 'draft',
                'approved_by' => auth()->id(),
                'approved_at' => now(),
                'notes' => trim(($purchaseOrderApproval->purchaseOrder->notes ?? '') . "\nRejected: " . $validated['rejection_reason']),
            ]);
        });

        return redirect()->route('purchase-order-approvals.index')->with('success', 'Purchase order berhasil direject.');
    }

    public function bulkApprove(Request $request)
    {
        $validated = $request->validate([
            'approval_ids' => 'required|array|min:1',
            'approval_ids.*' => 'required|exists:purchase_order_approvals,id',
            'comments' => 'nullable|string',
        ]);

        $approvals = PurchaseOrderApproval::whereIn('id', $validated['approval_ids'])
            ->where('status', 'pending')
            ->get();

        $approvedCount = 0;

        DB::transaction(function () use ($approvals, $validated, &$approvedCount) {
            foreach ($approvals as $approval) {
                if ($approval->submitted_by === auth()->id()) {
                    continue;
                }

                $approval->update([
                    'status' => 'approved',
                    'reviewed_by' => auth()->id(),
                    'reviewed_at' => now(),
                    'comments' => $validated['comments'] ?? null,
                ]);

                $approval->purchaseOrder->update([
                    'status' => 'approved',
                    'approved_by' => auth()->id(),
                    'approved_at' => now(),
                ]);

                $approvedCount++;
            }
        });

        return back()->with('success', "{$approvedCount} purchase order berhasil diapprove.");
    }

    private function approvalRate(): float
    {
        $approved = PurchaseOrderApproval::where('status', 'approved')->count();
        $rejected = PurchaseOrderApproval::where('status', 'rejected')->count();
        $totalReviewed = $approved + $rejected;

        if ($totalReviewed === 0) {
            return 0;
        }

        return round(($approved / $totalReviewed) * 100, 2);
    }

    private function averageApprovalTimeHours(): float
    {
        $durations = PurchaseOrderApproval::whereNotNull('reviewed_at')
            ->whereNotNull('submitted_at')
            ->get()
            ->map(function (PurchaseOrderApproval $approval) {
                return $approval->submitted_at->diffInSeconds($approval->reviewed_at) / 3600;
            });

        if ($durations->isEmpty()) {
            return 0;
        }

        return round($durations->avg(), 2);
    }
}

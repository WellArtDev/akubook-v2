<?php

namespace App\Http\Controllers;

use App\Models\SalesOrder;
use App\Models\SalesOrderApproval;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class SalesOrderApprovalController extends Controller
{
    public function index(Request $request)
    {
        $query = SalesOrderApproval::with(['salesOrder.customer', 'submitter'])
            ->where('status', 'pending');

        if ($request->filled('reason_type')) {
            $query->whereJsonContains('approval_reasons', [['type' => $request->reason_type]]);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('salesOrder', function ($salesOrderQuery) use ($search) {
                $salesOrderQuery->where('so_number', 'ilike', "%{$search}%")
                    ->orWhereHas('customer', function ($customerQuery) use ($search) {
                        $customerQuery->where('name', 'ilike', "%{$search}%");
                    });
            });
        }

        $approvals = $query
            ->orderBy('submitted_at', 'asc')
            ->paginate(50)
            ->withQueryString();

        $metrics = [
            'pending_count' => SalesOrderApproval::where('status', 'pending')->count(),
            'approval_rate' => $this->approvalRate(),
            'avg_approval_time_hours' => $this->averageApprovalTimeHours(),
        ];

        return Inertia::render('SalesOrderApprovals/Index', [
            'approvals' => $approvals,
            'metrics' => $metrics,
            'filters' => $request->only(['reason_type', 'search']),
        ]);
    }

    public function show(SalesOrderApproval $salesOrderApproval)
    {
        $salesOrderApproval->load([
            'salesOrder.customer',
            'salesOrder.lines.item',
            'salesOrder.deliveryAddress',
            'salesOrder.salesPerson',
            'salesOrder.createdBy',
            'submitter',
            'reviewer',
        ]);

        $salesOrder = $salesOrderApproval->salesOrder;
        $customer = $salesOrder->customer;

        $creditStatus = [
            'credit_limit' => (float) ($customer->credit_limit ?? 0),
            'outstanding' => $this->customerOutstanding($customer?->id),
            'this_order' => (float) $salesOrder->grand_total,
        ];
        $creditStatus['new_total'] = $creditStatus['outstanding'] + $creditStatus['this_order'];
        $creditStatus['available'] = max($creditStatus['credit_limit'] - $creditStatus['outstanding'], 0);
        $creditStatus['exceeded_amount'] = max($creditStatus['new_total'] - $creditStatus['credit_limit'], 0);

        return Inertia::render('SalesOrderApprovals/Show', [
            'approval' => $salesOrderApproval,
            'creditStatus' => $creditStatus,
            'stockStatus' => [],
        ]);
    }

    public function approve(Request $request, SalesOrderApproval $salesOrderApproval)
    {
        if ($salesOrderApproval->status !== 'pending') {
            return back()->withErrors(['error' => 'Approval sudah diproses.']);
        }

        if ($salesOrderApproval->submitted_by === auth()->id()) {
            return back()->withErrors(['error' => 'Tidak bisa approve SO buatan sendiri.']);
        }

        $validated = $request->validate([
            'comments' => 'nullable|string',
        ]);

        DB::transaction(function () use ($salesOrderApproval, $validated) {
            $salesOrderApproval->update([
                'status' => 'approved',
                'reviewed_by' => auth()->id(),
                'reviewed_at' => now(),
                'comments' => $validated['comments'] ?? null,
            ]);

            $salesOrderApproval->salesOrder->update([
                'status' => 'approved',
                'approved_by' => auth()->id(),
                'approved_at' => now(),
            ]);
        });

        return redirect()->route('sales-order-approvals.index')
            ->with('success', 'Sales order berhasil diapprove.');
    }

    public function reject(Request $request, SalesOrderApproval $salesOrderApproval)
    {
        if ($salesOrderApproval->status !== 'pending') {
            return back()->withErrors(['error' => 'Approval sudah diproses.']);
        }

        if ($salesOrderApproval->submitted_by === auth()->id()) {
            return back()->withErrors(['error' => 'Tidak bisa reject SO buatan sendiri.']);
        }

        $validated = $request->validate([
            'rejection_reason' => 'required|string|max:1000',
            'comments' => 'nullable|string',
        ]);

        DB::transaction(function () use ($salesOrderApproval, $validated) {
            $salesOrderApproval->update([
                'status' => 'rejected',
                'reviewed_by' => auth()->id(),
                'reviewed_at' => now(),
                'rejection_reason' => $validated['rejection_reason'],
                'comments' => $validated['comments'] ?? null,
            ]);

            $salesOrderApproval->salesOrder->update([
                'status' => 'draft',
                'rejected_by' => auth()->id(),
                'rejected_at' => now(),
                'rejection_reason' => $validated['rejection_reason'],
            ]);
        });

        return redirect()->route('sales-order-approvals.index')
            ->with('success', 'Sales order berhasil direject.');
    }

    public function bulkApprove(Request $request)
    {
        $validated = $request->validate([
            'approval_ids' => 'required|array|min:1',
            'approval_ids.*' => 'required|exists:sales_order_approvals,id',
            'comments' => 'nullable|string',
        ]);

        $approvals = SalesOrderApproval::whereIn('id', $validated['approval_ids'])
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

                $approval->salesOrder->update([
                    'status' => 'approved',
                    'approved_by' => auth()->id(),
                    'approved_at' => now(),
                ]);

                $approvedCount++;
            }
        });

        return back()->with('success', "{$approvedCount} sales order berhasil diapprove.");
    }

    private function approvalRate(): float
    {
        $approved = SalesOrderApproval::where('status', 'approved')->count();
        $rejected = SalesOrderApproval::where('status', 'rejected')->count();
        $totalReviewed = $approved + $rejected;

        if ($totalReviewed === 0) {
            return 0;
        }

        return round(($approved / $totalReviewed) * 100, 2);
    }

    private function averageApprovalTimeHours(): float
    {
        $durations = SalesOrderApproval::whereNotNull('reviewed_at')
            ->whereNotNull('submitted_at')
            ->get()
            ->map(function (SalesOrderApproval $approval) {
                return $approval->submitted_at->diffInSeconds($approval->reviewed_at) / 3600;
            });

        if ($durations->isEmpty()) {
            return 0;
        }

        return round($durations->avg(), 2);
    }

    private function customerOutstanding(?int $customerId): float
    {
        if (!$customerId) {
            return 0;
        }

        return (float) SalesOrder::where('customer_id', $customerId)
            ->whereIn('status', ['approved', 'in_progress'])
            ->sum('grand_total');
    }
}

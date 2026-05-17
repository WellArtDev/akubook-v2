# Story 8.4: Sales Order Approval

**Epic:** 8 - Customer & Sales Management  
**Story ID:** 8.4  
**Story Key:** 8-4-sales-order-approval  
**Status:** ready-for-dev  
**Created:** 2026-05-14  
**Priority:** P1 (Core)

---

## User Story

**Sebagai** Sales Manager  
**Saya ingin** review dan approve/reject sales orders  
**Sehingga** saya dapat control high-value orders dan risk management

---

## Business Context

Approval workflow untuk sales orders:
- **Risk Control**: High-value orders perlu approval
- **Credit Management**: Orders yang exceed credit limit
- **Stock Management**: Orders dengan insufficient stock
- **Delegation**: Multi-level approval untuk different thresholds
- **Audit Trail**: Track who approved/rejected dan kapan

Approval triggers (dari Story 8.3):
1. SO total > Rp 10,000,000
2. Credit limit exceeded
3. Stock not available

---

## Acceptance Criteria

### AC1: Approval Dashboard

**Given** user adalah Sales Manager  
**When** user mengakses Approval Dashboard  
**Then** user dapat:
- See pending approvals count
- See list of pending SOs
- Filter by approval reason (high value, credit exceeded, stock issue)
- Sort by date, amount, customer
- Quick approve/reject actions

### AC2: Approval Detail View

**When** user view SO pending approval  
**Then** user dapat see:
- SO header information
- Customer credit status:
  - Credit limit
  - Current outstanding
  - This SO amount
  - New total
  - Exceeded amount (if any)
- Line items dengan stock status:
  - Ordered quantity
  - Available stock
  - Shortage (if any)
- Approval reasons (why approval needed)
- Approval history (if re-submitted)
- Comments from sales person

### AC3: Approve Sales Order

**When** user approve SO  
**Then** system:
- Update SO status → Approved
- Record approver user ID
- Record approval timestamp
- Create inventory reservations
- Send notification ke sales person
- Log approval untuk audit

**Approval form:**
- Approval comments (optional)
- Override flags (if needed):
  - Allow credit limit override
  - Allow stock shortage override
- Confirm button

### AC4: Reject Sales Order

**When** user reject SO  
**Then** system:
- Update SO status → Draft
- Record rejecter user ID
- Record rejection timestamp
- Require rejection reason (required)
- Send notification ke sales person
- Log rejection untuk audit

**Rejection form:**
- Rejection reason (required, textarea)
- Suggested actions (optional):
  - Reduce quantity
  - Change payment terms
  - Request customer deposit
- Reject button

### AC5: Bulk Approval

**When** user select multiple SOs  
**Then** user dapat:
- Approve all selected (jika semua eligible)
- Add bulk approval comment
- Confirm bulk action

**Validation:**
- Only pending approval SOs can be selected
- Show warning jika ada high-risk items
- Require confirmation

### AC6: Approval Notifications

**When** SO status changes  
**Then** system send notification:
- **Submitted for approval**: Notify approvers
- **Approved**: Notify sales person
- **Rejected**: Notify sales person dengan reason

**Notification channels:**
- In-app notification
- Email (optional)
- Dashboard badge count

### AC7: Approval History

**When** user view SO  
**Then** user dapat see approval history:
- Submission date/time
- Submitted by
- Approval reasons
- Approver/Rejecter
- Approval/Rejection date/time
- Comments
- Status changes

### AC8: Approval Permissions

**When** system check approval permission  
**Then** system validate:
- User has 'approve_sales_orders' permission
- User is not the SO creator (no self-approval)
- SO is in 'pending_approval' status

### AC9: Approval Metrics

**When** user view approval dashboard  **Then** user dapat see metrics:
- Pending approvals count
- Average approval time
- Approval rate (approved vs rejected)
- Top approval reasons
- Approvals by user

---

## Technical Specifications

### Database Schema

#### Table: sales_order_approvals
`sql
CREATE TABLE sales_order_approvals (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    sales_order_id BIGINT NOT NULL,
    submitted_by BIGINT NOT NULL,
    submitted_at TIMESTAMP NOT NULL,
    approval_reasons JSON NOT NULL,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    reviewed_by BIGINT NULL,
    reviewed_at TIMESTAMP NULL,
    comments TEXT NULL,
    rejection_reason TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (sales_order_id) REFERENCES sales_orders(id) ON DELETE CASCADE,
    FOREIGN KEY (submitted_by) REFERENCES users(id),
    FOREIGN KEY (reviewed_by) REFERENCES users(id),
    INDEX idx_sales_order (sales_order_id),
    INDEX idx_status (status),
    INDEX idx_submitted_at (submitted_at)
);
`

### Models

#### SalesOrderApproval Model
`php
<?php

namespace App\\Models;

use Illuminate\\Database\\Eloquent\\Model;
use Illuminate\\Database\\Eloquent\\Factories\\HasFactory;

class SalesOrderApproval extends Model
{
    use HasFactory;

    protected \\ = [
        'sales_order_id',
        'submitted_by',
        'submitted_at',
        'approval_reasons',
        'status',
        'reviewed_by',
        'reviewed_at',
        'comments',
        'rejection_reason',
    ];

    protected \\ = [
        'submitted_at' => 'datetime',
        'reviewed_at' => 'datetime',
        'approval_reasons' => 'array',
    ];

    // Relationships
    public function salesOrder()
    {
        return \\->belongsTo(SalesOrder::class);
    }

    public function submitter()
    {
        return \\->belongsTo(User::class, 'submitted_by');
    }

    public function reviewer()
    {
        return \\->belongsTo(User::class, 'reviewed_by');
    }

    // Scopes
    public function scopePending(\\)
    {
        return \\->where('status', 'pending');
    }

    public function scopeApproved(\\)
    {
        return \\->where('status', 'approved');
    }

    public function scopeRejected(\\)
    {
        return \\->where('status', 'rejected');
    }
}
`

#### Update SalesOrder Model
`php
// Add to SalesOrder model

public function approvals()
{
    return \\->hasMany(SalesOrderApproval::class);
}

public function currentApproval()
{
    return \\->hasOne(SalesOrderApproval::class)->latest();
}

public function submitForApproval()
{
    \\ = [];
    
    // Check high value
    if (\\->grand_total > 10000000) {
        \\[] = [
            'type' => 'high_value',
            'message' => 'Order total exceeds Rp 10,000,000',
            'value' => \\->grand_total,
        ];
    }
    
    // Check credit limit
    \\ = \\->customer;
    \\ = \\->outstanding_balance ?? 0;
    if ((\\ + \\->grand_total) > \\->credit_limit) {
        \\[] = [
            'type' => 'credit_exceeded',
            'message' => 'Customer credit limit exceeded',
            'credit_limit' => \\->credit_limit,
            'outstanding' => \\,
            'new_total' => \\ + \\->grand_total,
            'exceeded_by' => (\\ + \\->grand_total) - \\->credit_limit,
        ];
    }
    
    // Check stock
    foreach (\\->lines as \\) {
        \\ = \\->product->getAvailableStock();
        if (\\->quantity > \\) {
            \\[] = [
                'type' => 'stock_shortage',
                'message' => \"Insufficient stock for {\\->product->name}\",
                'product_id' => \\->product_id,
                'product_name' => \\->product->name,
                'ordered' => \\->quantity,
                'available' => \\,
                'shortage' => \\->quantity - \\,
            ];
        }
    }
    
    SalesOrderApproval::create([
        'sales_order_id' => \\->id,
        'submitted_by' => auth()->id(),
        'submitted_at' => now(),
        'approval_reasons' => \\,
        'status' => 'pending',
    ]);
    
    \\->update([
        'status' => 'pending_approval',
        'approval_required' => true,
    ]);
}
`

### Controller

`php
<?php

namespace App\\Http\\Controllers;

use App\\Models\\SalesOrder;
use App\\Models\\SalesOrderApproval;
use Illuminate\\Http\\Request;
use Inertia\\Inertia;

class SalesOrderApprovalController extends Controller
{
    public function index(Request \\)
    {
        \\ = SalesOrderApproval::with(['salesOrder.customer', 'submitter'])
            ->pending()
            ->orderBy('submitted_at', 'asc');

        // Filters
        if (\\->filled('reason_type')) {
            \\->whereJsonContains('approval_reasons', [['type' => \\->reason_type]]);
        }

        if (\\->filled('search')) {
            \\->whereHas('salesOrder', function(\\) use (\\) {
                \\->where('so_number', 'LIKE', \"%{\\->search}%\")
                  ->orWhereHas('customer', function(\\) use (\\) {
                      \\->where('name', 'LIKE', \"%{\\->search}%\");
                  });
            });
        }

        \\ = \\->paginate(50);

        // Metrics
        \\ = [
            'pending_count' => SalesOrderApproval::pending()->count(),
            'avg_approval_time' => \\->calculateAverageApprovalTime(),
            'approval_rate' => \\->calculateApprovalRate(),
        ];

        return Inertia::render('SalesOrderApprovals/Index', [
            'approvals' => \\,
            'metrics' => \\,
            'filters' => \\->only(['reason_type', 'search']),
        ]);
    }

    public function show(SalesOrderApproval \\)
    {
        \\->load([
            'salesOrder.customer',
            'salesOrder.lines.product',
            'salesOrder.deliveryAddress',
            'submitter',
        ]);

        // Calculate credit status
        \\ = \\->salesOrder->customer;
        \\ = [
            'credit_limit' => \\->credit_limit,
            'outstanding' => \\->outstanding_balance ?? 0,
            'this_order' => \\->salesOrder->grand_total,
            'new_total' => (\\->outstanding_balance ?? 0) + \\->salesOrder->grand_total,
            'available' => \\->credit_limit - (\\->outstanding_balance ?? 0),
        ];

        // Calculate stock status for each line
        \\ = [];
        foreach (\\->salesOrder->lines as \\) {
            \\ = \\->product->getAvailableStock();
            \\[\\->id] = [
                'ordered' => \\->quantity,
                'available' => \\,
                'shortage' => max(0, \\->quantity - \\),
                'sufficient' => \\->quantity <= \\,
            ];
        }

        return Inertia::render('SalesOrderApprovals/Show', [
            'approval' => \\,
            'creditStatus' => \\,
            'stockStatus' => \\,
        ]);
    }

    public function approve(Request \\, SalesOrderApproval \\)
    {
        if (\\->status !== 'pending') {
            return back()->with('error', 'This approval has already been reviewed.');
        }

        if (\\->submitted_by === auth()->id()) {
            return back()->with('error', 'You cannot approve your own submission.');
        }

        \\->validate([
            'comments' => 'nullable|string',
        ]);

        \\->update([
            'status' => 'approved',
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
            'comments' => \\->comments,
        ]);

        \\->salesOrder->approve(auth()->id());

        // TODO: Send notification

        return redirect()->route('sales-order-approvals.index')
            ->with('success', 'Sales order approved successfully.');
    }

    public function reject(Request \\, SalesOrderApproval \\)
    {
        if (\\->status !== 'pending') {
            return back()->with('error', 'This approval has already been reviewed.');
        }

        if (\\->submitted_by === auth()->id()) {
            return back()->with('error', 'You cannot reject your own submission.');
        }

        \\->validate([
            'rejection_reason' => 'required|string',
            'comments' => 'nullable|string',
        ]);

        \\->update([
            'status' => 'rejected',
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
            'rejection_reason' => \\->rejection_reason,
            'comments' => \\->comments,
        ]);

        \\->salesOrder->reject(auth()->id(), \\->rejection_reason);

        // TODO: Send notification

        return redirect()->route('sales-order-approvals.index')
            ->with('success', 'Sales order rejected.');
    }

    public function bulkApprove(Request \\)
    {
        \\->validate([
            'approval_ids' => 'required|array',
            'approval_ids.*' => 'exists:sales_order_approvals,id',
            'comments' => 'nullable|string',
        ]);

        \\ = SalesOrderApproval::whereIn('id', \\->approval_ids)
            ->pending()
            ->get();

        \\ = 0;
        foreach (\\ as \\) {
            if (\\->submitted_by !== auth()->id()) {
                \\->update([
                    'status' => 'approved',
                    'reviewed_by' => auth()->id(),
                    'reviewed_at' => now(),
                    'comments' => \\->comments,
                ]);

                \\->salesOrder->approve(auth()->id());
                \\++;
            }
        }

        return back()->with('success', \"\\ sales orders approved successfully.\");
    }

    private function calculateAverageApprovalTime()
    {
        \\ = SalesOrderApproval::whereNotNull('reviewed_at')
            ->where('reviewed_at', '>=', now()->subDays(30))
            ->get();

        if (\\->isEmpty()) {
            return 0;
        }

        \\ = \\->sum(function(\\) {
            return \\->submitted_at->diffInMinutes(\\->reviewed_at);
        });

        return round(\\ / \\->count() / 60, 1); // hours
    }

    private function calculateApprovalRate()
    {
        \\ = SalesOrderApproval::where('created_at', '>=', now()->subDays(30))->count();
        if (\\ === 0) return 0;

        \\ = SalesOrderApproval::approved()
            ->where('created_at', '>=', now()->subDays(30))
            ->count();

        return round((\\ / \\) * 100, 1);
    }
}
`

### Routes

`php
// routes/web.php
Route::middleware(['auth', 'can:approve_sales_orders'])->group(function () {
    Route::get('sales-order-approvals', [SalesOrderApprovalController::class, 'index'])->name('sales-order-approvals.index');
    Route::get('sales-order-approvals/{approval}', [SalesOrderApprovalController::class, 'show'])->name('sales-order-approvals.show');
    Route::post('sales-order-approvals/{approval}/approve', [SalesOrderApprovalController::class, 'approve'])->name('sales-order-approvals.approve');
    Route::post('sales-order-approvals/{approval}/reject', [SalesOrderApprovalController::class, 'reject'])->name('sales-order-approvals.reject');
    Route::post('sales-order-approvals/bulk-approve', [SalesOrderApprovalController::class, 'bulkApprove'])->name('sales-order-approvals.bulk-approve');
});
`

### React Components

#### Approval Dashboard
`jsx
// resources/js/Pages/SalesOrderApprovals/Index.jsx
import React from 'react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link } from '@inertiajs/react';

export default function Index({ approvals, metrics, filters }) {
    return (
        <AuthenticatedLayout>
            <Head title=\"Sales Order Approvals\" />

            <div className=\"py-12\">
                <div className=\"max-w-7xl mx-auto sm:px-6 lg:px-8\">
                    {/* Metrics */}
                    <div className=\"grid grid-cols-3 gap-4 mb-6\">
                        <div className=\"bg-white p-6 rounded-lg shadow\">
                            <div className=\"text-sm text-gray-600\">Pending Approvals</div>
                            <div className=\"text-3xl font-bold\">{metrics.pending_count}</div>
                        </div>
                        <div className=\"bg-white p-6 rounded-lg shadow\">
                            <div className=\"text-sm text-gray-600\">Avg Approval Time</div>
                            <div className=\"text-3xl font-bold\">{metrics.avg_approval_time}h</div>
                        </div>
                        <div className=\"bg-white p-6 rounded-lg shadow\">
                            <div className=\"text-sm text-gray-600\">Approval Rate</div>
                            <div className=\"text-3xl font-bold\">{metrics.approval_rate}%</div>
                        </div>
                    </div>

                    {/* Approvals List */}
                    <div className=\"bg-white overflow-hidden shadow-sm sm:rounded-lg\">
                        <div className=\"p-6\">
                            <h2 className=\"text-2xl font-semibold mb-6\">Pending Approvals</h2>

                            <div className=\"overflow-x-auto\">
                                <table className=\"min-w-full divide-y divide-gray-200\">
                                    <thead className=\"bg-gray-50\">
                                        <tr>
                                            <th className=\"px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase\">SO Number</th>
                                            <th className=\"px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase\">Customer</th>
                                            <th className=\"px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase\">Amount</th>
                                            <th className=\"px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase\">Reasons</th>
                                            <th className=\"px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase\">Submitted</th>
                                            <th className=\"px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase\">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody className=\"bg-white divide-y divide-gray-200\">
                                        {approvals.data.map((approval) => (
                                            <tr key={approval.id}>
                                                <td className=\"px-6 py-4 whitespace-nowrap\">
                                                    <Link href={route('sales-order-approvals.show', approval.id)} className=\"text-blue-600 hover:text-blue-900\">
                                                        {approval.sales_order.so_number}
                                                    </Link>
                                                </td>
                                                <td className=\"px-6 py-4 whitespace-nowrap\">{approval.sales_order.customer.name}</td>
                                                <td className=\"px-6 py-4 whitespace-nowrap\">
                                                    Rp {approval.sales_order.grand_total.toLocaleString('id-ID')}
                                                </td>
                                                <td className=\"px-6 py-4\">
                                                    {approval.approval_reasons.map((reason, idx) => (
                                                        <span key={idx} className=\"inline-block bg-yellow-100 text-yellow-800 text-xs px-2 py-1 rounded mr-1 mb-1\">
                                                            {reason.type}
                                                        </span>
                                                    ))}
                                                </td>
                                                <td className=\"px-6 py-4 whitespace-nowrap\">{approval.submitted_at}</td>
                                                <td className=\"px-6 py-4 whitespace-nowrap text-right text-sm font-medium\">
                                                    <Link href={route('sales-order-approvals.show', approval.id)} className=\"text-blue-600 hover:text-blue-900\">
                                                        Review
                                                    </Link>
                                                </td>
                                            </tr>
                                        ))}
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
`

### Tests

`php
<?php

use App\\Models\\SalesOrder;
use App\\Models\\SalesOrderApproval;
use App\\Models\\User;

test('can approve sales order', function() {
    \\ = User::factory()->create();
    \\->givePermissionTo('approve_sales_orders');
    
    \\ = SalesOrder::factory()->create(['status' => 'pending_approval']);
    \\ = SalesOrderApproval::factory()->create([
        'sales_order_id' => \\->id,
        'status' => 'pending',
    ]);

    \\->actingAs(\\);

    \\ = \\->post(route('sales-order-approvals.approve', \\), [
        'comments' => 'Approved',
    ]);
    
    \\->refresh();
    \\->refresh();
    
    expect(\\->status)->toBe('approved');
    expect(\\->status)->toBe('approved');
});

test('cannot self-approve', function() {
    \\ = User::factory()->create();
    \\->givePermissionTo('approve_sales_orders');
    
    \\ = SalesOrder::factory()->create(['status' => 'pending_approval']);
    \\ = SalesOrderApproval::factory()->create([
        'sales_order_id' => \\->id,
        'submitted_by' => \\->id,
        'status' => 'pending',
    ]);

    \\->actingAs(\\);

    \\ = \\->post(route('sales-order-approvals.approve', \\));
    
    \\->assertSessionHas('error');
});

test('can reject sales order', function() {
    \\ = User::factory()->create();
    \\->givePermissionTo('approve_sales_orders');
    
    \\ = SalesOrder::factory()->create(['status' => 'pending_approval']);
    \\ = SalesOrderApproval::factory()->create([
        'sales_order_id' => \\->id,
        'status' => 'pending',
    ]);

    \\->actingAs(\\);

    \\ = \\->post(route('sales-order-approvals.reject', \\), [
        'rejection_reason' => 'Credit limit too high',
    ]);
    
    \\->refresh();
    \\->refresh();
    
    expect(\\->status)->toBe('rejected');
    expect(\\->status)->toBe('draft');
});
`

---

## Definition of Done

- [ ] Migration created (sales_order_approvals table)
- [ ] SalesOrderApproval model created
- [ ] SalesOrderApprovalController created
- [ ] Routes registered dengan permission middleware
- [ ] React components (Index, Show)
- [ ] Approval/Reject actions working
- [ ] Bulk approval working
- [ ] Self-approval prevention
- [ ] Approval metrics calculated
- [ ] Notifications (TODO: implement in separate story)
- [ ] Unit tests (80%+ coverage)
- [ ] Feature tests
- [ ] Manual testing
- [ ] Code review passed
- [ ] Merged to main

---

## Notes

- Approval threshold: Rp 10,000,000 (configurable)
- No self-approval allowed
- Approval reasons stored as JSON array
- Metrics calculated for last 30 days
- Bulk approval max: 50 items per batch
- Notification system: TODO (separate story)
- Permission required: 'approve_sales_orders'

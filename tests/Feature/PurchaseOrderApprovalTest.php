<?php

namespace Tests\Feature;

use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderApproval;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PurchaseOrderApprovalTest extends TestCase
{
    use RefreshDatabase;

    private User $submitter;
    private User $approver;
    private Supplier $supplier;

    protected function setUp(): void
    {
        parent::setUp();

        $this->submitter = User::factory()->create();
        $this->approver = User::factory()->create();
        $this->supplier = Supplier::factory()->create();
    }

    public function test_submit_for_approval_creates_pending_approval_record(): void
    {
        $purchaseOrder = $this->purchaseOrder(['grand_total' => 15000000, 'approval_required' => true]);

        $this->actingAs($this->submitter)
            ->post(route('purchase-orders.submit-approval', $purchaseOrder))
            ->assertRedirect();

        $purchaseOrder->refresh();
        $this->assertSame('pending_approval', $purchaseOrder->status);
        $this->assertDatabaseHas('purchase_order_approvals', [
            'purchase_order_id' => $purchaseOrder->id,
            'submitted_by' => $this->submitter->id,
            'status' => 'pending',
        ]);
        $this->assertSame('high_value', $purchaseOrder->approvals()->first()->approval_reasons[0]['type']);
    }

    public function test_approval_dashboard_shows_pending_approvals(): void
    {
        $purchaseOrder = $this->purchaseOrder(['status' => 'pending_approval']);
        PurchaseOrderApproval::factory()->create([
            'purchase_order_id' => $purchaseOrder->id,
            'submitted_by' => $this->submitter->id,
        ]);

        $this->actingAs($this->approver)
            ->get(route('purchase-order-approvals.index'))
            ->assertOk();
    }

    public function test_approver_can_approve_pending_purchase_order(): void
    {
        $purchaseOrder = $this->purchaseOrder(['status' => 'pending_approval']);
        $approval = PurchaseOrderApproval::factory()->create([
            'purchase_order_id' => $purchaseOrder->id,
            'submitted_by' => $this->submitter->id,
        ]);

        $this->actingAs($this->approver)
            ->post(route('purchase-order-approvals.approve', $approval), ['comments' => 'Approved'])
            ->assertRedirect(route('purchase-order-approvals.index'));

        $approval->refresh();
        $purchaseOrder->refresh();
        $this->assertSame('approved', $approval->status);
        $this->assertSame($this->approver->id, $approval->reviewed_by);
        $this->assertSame('approved', $purchaseOrder->status);
        $this->assertSame($this->approver->id, $purchaseOrder->approved_by);
    }

    public function test_submitter_cannot_self_approve(): void
    {
        $purchaseOrder = $this->purchaseOrder(['status' => 'pending_approval']);
        $approval = PurchaseOrderApproval::factory()->create([
            'purchase_order_id' => $purchaseOrder->id,
            'submitted_by' => $this->submitter->id,
        ]);

        $this->actingAs($this->submitter)
            ->post(route('purchase-order-approvals.approve', $approval))
            ->assertSessionHasErrors('error');

        $this->assertSame('pending', $approval->fresh()->status);
    }

    public function test_approver_can_reject_pending_purchase_order(): void
    {
        $purchaseOrder = $this->purchaseOrder(['status' => 'pending_approval']);
        $approval = PurchaseOrderApproval::factory()->create([
            'purchase_order_id' => $purchaseOrder->id,
            'submitted_by' => $this->submitter->id,
        ]);

        $this->actingAs($this->approver)
            ->post(route('purchase-order-approvals.reject', $approval), ['rejection_reason' => 'Budget over'])
            ->assertRedirect(route('purchase-order-approvals.index'));

        $approval->refresh();
        $purchaseOrder->refresh();
        $this->assertSame('rejected', $approval->status);
        $this->assertSame('Budget over', $approval->rejection_reason);
        $this->assertSame('draft', $purchaseOrder->status);
    }

    public function test_bulk_approve_approves_multiple_pending_orders(): void
    {
        $first = PurchaseOrderApproval::factory()->create([
            'purchase_order_id' => $this->purchaseOrder(['status' => 'pending_approval'])->id,
            'submitted_by' => $this->submitter->id,
        ]);
        $second = PurchaseOrderApproval::factory()->create([
            'purchase_order_id' => $this->purchaseOrder(['status' => 'pending_approval'])->id,
            'submitted_by' => $this->submitter->id,
        ]);

        $this->actingAs($this->approver)
            ->post(route('purchase-order-approvals.bulk-approve'), [
                'approval_ids' => [$first->id, $second->id],
                'comments' => 'Bulk approved',
            ])
            ->assertRedirect();

        $this->assertSame('approved', $first->fresh()->status);
        $this->assertSame('approved', $second->fresh()->status);
        $this->assertSame('approved', $first->purchaseOrder->fresh()->status);
        $this->assertSame('approved', $second->purchaseOrder->fresh()->status);
    }

    private function purchaseOrder(array $overrides = []): PurchaseOrder
    {
        return PurchaseOrder::factory()->create(array_merge([
            'supplier_id' => $this->supplier->id,
            'created_by' => $this->submitter->id,
            'status' => 'draft',
            'grand_total' => 15000000,
            'approval_required' => true,
        ], $overrides));
    }
}

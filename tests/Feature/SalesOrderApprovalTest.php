<?php

namespace Tests\Feature;

use App\Models\ApprovalWorkflow;
use App\Models\AuditLog;
use App\Models\Branch;
use App\Models\Customer;
use App\Models\Item;
use App\Models\SalesOrder;
use App\Models\SalesOrderApproval;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SalesOrderApprovalTest extends TestCase
{
    use RefreshDatabase;

    private User $submitter;
    private User $approver;
    private Customer $customer;
    private Branch $branch;
    private Item $item;

    protected function setUp(): void
    {
        parent::setUp();

        $this->submitter = User::factory()->create();
        $this->approver = User::factory()->create();
        $this->customer = Customer::factory()->create(['credit_limit' => 5000000]);
        $this->branch = Branch::factory()->create();
        $this->item = Item::factory()->create(['is_active' => true]);
    }

    public function test_submit_for_approval_creates_pending_approval_record(): void
    {
        $salesOrder = $this->salesOrder(['grand_total' => 15000000, 'approval_required' => true]);

        $this->actingAs($this->submitter)
            ->post(route('sales-orders.submit-approval', $salesOrder))
            ->assertRedirect(route('sales-orders.show', $salesOrder));

        $salesOrder->refresh();
        $this->assertSame('pending_approval', $salesOrder->status);
        $this->assertDatabaseHas('sales_order_approvals', [
            'sales_order_id' => $salesOrder->id,
            'submitted_by' => $this->submitter->id,
            'status' => 'pending',
        ]);
        $this->assertSame('high_value', $salesOrder->approvals()->first()->approval_reasons[0]['type']);
    }

    public function test_submit_enforces_workflow_and_logs_audit_when_rule_matches(): void
    {
        ApprovalWorkflow::factory()->create([
            'entity_type' => 'sales_order',
            'is_active' => true,
            'min_amount' => 10000000,
            'max_amount' => null,
            'required_level' => 2,
            'workflow_key' => 'SO-HV-001',
            'created_by' => $this->submitter->id,
        ]);

        $salesOrder = $this->salesOrder([
            'grand_total' => 15000000,
            'approval_required' => false,
            'status' => 'draft',
        ]);

        $this->actingAs($this->submitter)
            ->post(route('sales-orders.submit-approval', $salesOrder))
            ->assertRedirect(route('sales-orders.show', $salesOrder));

        $salesOrder->refresh();

        $this->assertSame('pending_approval', $salesOrder->status);
        $this->assertTrue($salesOrder->approval_required);
        $this->assertDatabaseHas('sales_order_approvals', [
            'sales_order_id' => $salesOrder->id,
            'status' => 'pending',
        ]);

        $approval = $salesOrder->approvals()->latest()->first();
        $this->assertNotNull($approval);
        $this->assertContains('workflow_enforcement', array_column($approval->approval_reasons, 'type'));

        $audit = AuditLog::query()->where('event_key', 'workflow.enforcement.evaluated')->latest()->first();
        $this->assertNotNull($audit);
        $this->assertTrue((bool) $audit->is_sensitive);
        $this->assertSame('enforced', $audit->action);
    }

    public function test_submit_auto_approves_when_no_workflow_rule_matches(): void
    {
        $this->customer->update(['credit_limit' => 5000000]);

        $salesOrder = $this->salesOrder([
            'grand_total' => 1000000,
            'approval_required' => false,
            'status' => 'draft',
        ]);

        $this->actingAs($this->submitter)
            ->post(route('sales-orders.submit-approval', $salesOrder))
            ->assertRedirect(route('sales-orders.show', $salesOrder));

        $salesOrder->refresh();

        $this->assertSame('approved', $salesOrder->status);
        $this->assertFalse($salesOrder->approval_required);
        $this->assertDatabaseMissing('sales_order_approvals', [
            'sales_order_id' => $salesOrder->id,
        ]);

        $audit = AuditLog::query()->where('event_key', 'workflow.enforcement.evaluated')->latest()->first();
        $this->assertNotNull($audit);
        $this->assertSame('not_enforced', $audit->action);
    }

    public function test_approval_dashboard_shows_pending_approvals(): void
    {
        $salesOrder = $this->salesOrder(['status' => 'pending_approval']);
        SalesOrderApproval::factory()->create([
            'sales_order_id' => $salesOrder->id,
            'submitted_by' => $this->submitter->id,
        ]);

        $this->actingAs($this->approver)
            ->get(route('sales-order-approvals.index'))
            ->assertOk();
    }

    public function test_approver_can_approve_pending_sales_order(): void
    {
        $salesOrder = $this->salesOrder(['status' => 'pending_approval']);
        $approval = SalesOrderApproval::factory()->create([
            'sales_order_id' => $salesOrder->id,
            'submitted_by' => $this->submitter->id,
        ]);

        $this->actingAs($this->approver)
            ->post(route('sales-order-approvals.approve', $approval), ['comments' => 'Approved'])
            ->assertRedirect(route('sales-order-approvals.index'));

        $approval->refresh();
        $salesOrder->refresh();
        $this->assertSame('approved', $approval->status);
        $this->assertSame($this->approver->id, $approval->reviewed_by);
        $this->assertSame('approved', $salesOrder->status);
        $this->assertSame($this->approver->id, $salesOrder->approved_by);
    }

    public function test_submitter_cannot_self_approve(): void
    {
        $salesOrder = $this->salesOrder(['status' => 'pending_approval']);
        $approval = SalesOrderApproval::factory()->create([
            'sales_order_id' => $salesOrder->id,
            'submitted_by' => $this->submitter->id,
        ]);

        $this->actingAs($this->submitter)
            ->post(route('sales-order-approvals.approve', $approval))
            ->assertSessionHasErrors('error');

        $this->assertSame('pending', $approval->refresh()->status);
    }

    public function test_approver_can_reject_pending_sales_order(): void
    {
        $salesOrder = $this->salesOrder(['status' => 'pending_approval']);
        $approval = SalesOrderApproval::factory()->create([
            'sales_order_id' => $salesOrder->id,
            'submitted_by' => $this->submitter->id,
        ]);

        $this->actingAs($this->approver)
            ->post(route('sales-order-approvals.reject', $approval), ['rejection_reason' => 'Revise payment terms'])
            ->assertRedirect(route('sales-order-approvals.index'));

        $approval->refresh();
        $salesOrder->refresh();
        $this->assertSame('rejected', $approval->status);
        $this->assertSame('Revise payment terms', $approval->rejection_reason);
        $this->assertSame('draft', $salesOrder->status);
        $this->assertSame('Revise payment terms', $salesOrder->rejection_reason);
    }

    public function test_bulk_approve_approves_multiple_pending_orders(): void
    {
        $first = SalesOrderApproval::factory()->create([
            'sales_order_id' => $this->salesOrder(['status' => 'pending_approval'])->id,
            'submitted_by' => $this->submitter->id,
        ]);
        $second = SalesOrderApproval::factory()->create([
            'sales_order_id' => $this->salesOrder(['status' => 'pending_approval'])->id,
            'submitted_by' => $this->submitter->id,
        ]);

        $this->actingAs($this->approver)
            ->post(route('sales-order-approvals.bulk-approve'), [
                'approval_ids' => [$first->id, $second->id],
                'comments' => 'Bulk approved',
            ])
            ->assertRedirect();

        $this->assertSame('approved', $first->refresh()->status);
        $this->assertSame('approved', $second->refresh()->status);
        $this->assertSame('approved', $first->salesOrder->refresh()->status);
        $this->assertSame('approved', $second->salesOrder->refresh()->status);
    }

    private function salesOrder(array $overrides = []): SalesOrder
    {
        $salesOrder = SalesOrder::factory()->create(array_merge([
            'customer_id' => $this->customer->id,
            'branch_id' => $this->branch->id,
            'delivery_address_id' => $this->branch->id,
            'sales_person_id' => $this->submitter->id,
            'created_by' => $this->submitter->id,
            'status' => 'draft',
            'grand_total' => 15000000,
            'approval_required' => true,
        ], $overrides));

        $salesOrder->lines()->create([
            'line_number' => 1,
            'item_id' => $this->item->id,
            'description' => 'Approval test item',
            'quantity' => 1,
            'unit' => 'pcs',
            'unit_price' => 15000000,
            'discount_percent' => 0,
            'discount_amount' => 0,
            'tax_amount' => 0,
            'line_total' => 15000000,
        ]);

        return $salesOrder;
    }
}

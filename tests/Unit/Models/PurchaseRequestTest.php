<?php

namespace Tests\Unit\Models;

use App\Models\PurchaseRequest;
use App\Models\PurchaseRequestLine;
use App\Models\User;
use App\Models\Department;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PurchaseRequestTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate');
    }

    public function test_it_generates_unique_pr_numbers()
    {
        $user = User::factory()->create();
        $department = Department::factory()->create();

        $pr1 = PurchaseRequest::create([
            'pr_number' => PurchaseRequest::generatePRNumber(),
            'pr_date' => now(),
            'department_id' => $department->id,
            'required_date' => now()->addDays(7),
            'created_by' => $user->id,
        ]);

        $pr2 = PurchaseRequest::create([
            'pr_number' => PurchaseRequest::generatePRNumber(),
            'pr_date' => now(),
            'department_id' => $department->id,
            'required_date' => now()->addDays(7),
            'created_by' => $user->id,
        ]);

        $this->assertNotEquals($pr1->pr_number, $pr2->pr_number);
        $this->assertStringStartsWith('PR-' . now()->year . '-', $pr1->pr_number);
        $this->assertStringStartsWith('PR-' . now()->year . '-', $pr2->pr_number);
    }

    public function test_it_calculates_total_from_lines()
    {
        $user = User::factory()->create();
        $department = Department::factory()->create();

        $pr = PurchaseRequest::create([
            'pr_number' => PurchaseRequest::generatePRNumber(),
            'pr_date' => now(),
            'department_id' => $department->id,
            'required_date' => now()->addDays(7),
            'created_by' => $user->id,
        ]);

        PurchaseRequestLine::create([
            'purchase_request_id' => $pr->id,
            'line_number' => 1,
            'product_name' => 'Product A',
            'quantity' => 10,
            'unit' => 'pcs',
            'estimated_price' => 100,
        ]);

        PurchaseRequestLine::create([
            'purchase_request_id' => $pr->id,
            'line_number' => 2,
            'product_name' => 'Product B',
            'quantity' => 5,
            'unit' => 'pcs',
            'estimated_price' => 200,
        ]);

        $pr->refresh();
        $this->assertEquals(2000, $pr->total_estimated_amount);
    }

    public function test_it_can_submit_draft_pr()
    {
        $user = User::factory()->create();
        $department = Department::factory()->create();

        $pr = PurchaseRequest::create([
            'pr_number' => PurchaseRequest::generatePRNumber(),
            'pr_date' => now(),
            'department_id' => $department->id,
            'required_date' => now()->addDays(7),
            'status' => 'draft',
            'created_by' => $user->id,
        ]);

        $pr->submit();

        $this->assertEquals('pending_approval', $pr->status);
    }

    public function test_it_cannot_submit_non_draft_pr()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Only draft PRs can be submitted');

        $user = User::factory()->create();
        $department = Department::factory()->create();

        $pr = PurchaseRequest::create([
            'pr_number' => PurchaseRequest::generatePRNumber(),
            'pr_date' => now(),
            'department_id' => $department->id,
            'required_date' => now()->addDays(7),
            'status' => 'approved',
            'created_by' => $user->id,
        ]);

        $pr->submit();
    }

    public function test_it_can_approve_pending_pr()
    {
        $user = User::factory()->create();
        $approver = User::factory()->create();
        $department = Department::factory()->create();

        $pr = PurchaseRequest::create([
            'pr_number' => PurchaseRequest::generatePRNumber(),
            'pr_date' => now(),
            'department_id' => $department->id,
            'required_date' => now()->addDays(7),
            'status' => 'pending_approval',
            'created_by' => $user->id,
        ]);

        $pr->approve($approver->id);

        $this->assertEquals('approved', $pr->status);
        $this->assertEquals($approver->id, $pr->approved_by);
        $this->assertNotNull($pr->approved_at);
    }

    public function test_it_can_reject_pending_pr()
    {
        $user = User::factory()->create();
        $approver = User::factory()->create();
        $department = Department::factory()->create();

        $pr = PurchaseRequest::create([
            'pr_number' => PurchaseRequest::generatePRNumber(),
            'pr_date' => now(),
            'department_id' => $department->id,
            'required_date' => now()->addDays(7),
            'status' => 'pending_approval',
            'created_by' => $user->id,
        ]);

        $pr->reject($approver->id);

        $this->assertEquals('rejected', $pr->status);
        $this->assertEquals($approver->id, $pr->approved_by);
        $this->assertNotNull($pr->approved_at);
    }

    public function test_it_can_cancel_pr()
    {
        $user = User::factory()->create();
        $department = Department::factory()->create();

        $pr = PurchaseRequest::create([
            'pr_number' => PurchaseRequest::generatePRNumber(),
            'pr_date' => now(),
            'department_id' => $department->id,
            'required_date' => now()->addDays(7),
            'status' => 'draft',
            'created_by' => $user->id,
        ]);

        $pr->cancel();

        $this->assertEquals('cancelled', $pr->status);
    }

    public function test_it_can_mark_as_converted()
    {
        $user = User::factory()->create();
        $department = Department::factory()->create();

        $pr = PurchaseRequest::create([
            'pr_number' => PurchaseRequest::generatePRNumber(),
            'pr_date' => now(),
            'department_id' => $department->id,
            'required_date' => now()->addDays(7),
            'status' => 'approved',
            'created_by' => $user->id,
        ]);

        $pr->markAsConverted();

        $this->assertEquals('converted', $pr->status);
    }
}

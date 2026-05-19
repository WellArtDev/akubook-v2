<?php

namespace Tests\Feature;

use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LeaveRequestTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Employee $employee;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();

        $this->user = User::factory()->create();
        $this->employee = Employee::factory()->create([
            'employment_status' => 'active',
            'created_by' => $this->user->id,
        ]);
    }

    public function test_user_can_create_leave_request(): void
    {
        $response = $this->actingAs($this->user)->post(route('leave-requests.store'), [
            'employee_id' => $this->employee->id,
            'leave_type' => 'annual',
            'start_date' => '2026-05-20',
            'end_date' => '2026-05-22',
            'reason' => 'Family event',
        ]);

        $response->assertRedirect(route('leave-requests.index'));
        $this->assertDatabaseHas('leave_requests', [
            'employee_id' => $this->employee->id,
            'leave_type' => 'annual',
            'total_days' => 3,
            'status' => 'pending',
            'created_by' => $this->user->id,
        ]);
    }

    public function test_leave_request_can_be_approved(): void
    {
        $leaveRequest = LeaveRequest::factory()->create([
            'employee_id' => $this->employee->id,
            'created_by' => $this->user->id,
            'status' => 'pending',
        ]);

        $this->actingAs($this->user)->post(route('leave-requests.approve', $leaveRequest))->assertRedirect();

        $leaveRequest->refresh();
        $this->assertSame('approved', $leaveRequest->status);
        $this->assertSame($this->user->id, $leaveRequest->approved_by);
        $this->assertNotNull($leaveRequest->approved_at);
    }

    public function test_leave_request_can_be_rejected(): void
    {
        $leaveRequest = LeaveRequest::factory()->create([
            'employee_id' => $this->employee->id,
            'created_by' => $this->user->id,
            'status' => 'pending',
        ]);

        $this->actingAs($this->user)->post(route('leave-requests.reject', $leaveRequest), [
            'rejection_reason' => 'Schedule conflict',
        ])->assertRedirect();

        $leaveRequest->refresh();
        $this->assertSame('rejected', $leaveRequest->status);
        $this->assertSame('Schedule conflict', $leaveRequest->rejection_reason);
        $this->assertSame($this->user->id, $leaveRequest->rejected_by);
    }

    public function test_leave_request_can_be_cancelled(): void
    {
        $leaveRequest = LeaveRequest::factory()->create([
            'employee_id' => $this->employee->id,
            'created_by' => $this->user->id,
            'status' => 'pending',
        ]);

        $this->actingAs($this->user)->post(route('leave-requests.cancel', $leaveRequest), [
            'cancellation_reason' => 'No longer needed',
        ])->assertRedirect();

        $leaveRequest->refresh();
        $this->assertSame('cancelled', $leaveRequest->status);
        $this->assertSame('No longer needed', $leaveRequest->cancellation_reason);
        $this->assertSame($this->user->id, $leaveRequest->cancelled_by);
    }

    public function test_index_can_filter_leave_requests(): void
    {
        LeaveRequest::factory()->create([
            'employee_id' => $this->employee->id,
            'created_by' => $this->user->id,
            'status' => 'approved',
            'start_date' => '2026-05-20',
            'end_date' => '2026-05-21',
        ]);

        $this->actingAs($this->user)->get(route('leave-requests.index', [
            'search' => $this->employee->employee_id,
            'status' => 'approved',
            'date_from' => '2026-05-01',
            'date_to' => '2026-05-31',
        ]))->assertOk();
    }
}

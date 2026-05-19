<?php

namespace Tests\Feature;

use App\Models\AttendanceRecord;
use App\Models\Employee;
use App\Models\OvertimeRecord;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OvertimeRecordTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Employee $employee;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();

        $this->user = User::factory()->create();
        $this->actingAs($this->user);
        $this->employee = Employee::factory()->create(['employment_status' => 'active']);
    }

    public function test_user_can_create_overtime_record_and_calculate_hours(): void
    {
        AttendanceRecord::factory()->create([
            'employee_id' => $this->employee->id,
            'attendance_date' => '2026-05-18',
            'check_in_at' => '2026-05-18 08:00:00',
            'check_out_at' => '2026-05-18 17:00:00',
            'status' => 'present',
        ]);

        $response = $this->post(route('overtime-records.store'), [
            'employee_id' => $this->employee->id,
            'overtime_date' => '2026-05-18',
            'start_at' => '2026-05-18 18:00:00',
            'end_at' => '2026-05-18 20:30:00',
            'reason' => 'Month end closing',
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('overtime_records', [
            'employee_id' => $this->employee->id,
            'overtime_date' => '2026-05-18 00:00:00',
            'hours' => '2.50',
            'status' => 'pending',
            'created_by' => $this->user->id,
        ]);
    }

    public function test_overlap_overtime_is_rejected(): void
    {
        OvertimeRecord::factory()->create([
            'employee_id' => $this->employee->id,
            'attendance_record_id' => null,
            'overtime_date' => '2026-05-18',
            'start_at' => '2026-05-18 18:00:00',
            'end_at' => '2026-05-18 20:00:00',
            'status' => 'pending',
        ]);

        $response = $this->post(route('overtime-records.store'), [
            'employee_id' => $this->employee->id,
            'overtime_date' => '2026-05-18',
            'start_at' => '2026-05-18 19:00:00',
            'end_at' => '2026-05-18 21:00:00',
            'reason' => 'Overlap',
        ]);

        $response->assertSessionHasErrors('start_at');
    }

    public function test_overtime_can_be_approved_rejected_and_cancelled(): void
    {
        $approve = OvertimeRecord::factory()->create(['employee_id' => $this->employee->id, 'attendance_record_id' => null, 'status' => 'pending']);
        $reject = OvertimeRecord::factory()->create(['employee_id' => $this->employee->id, 'attendance_record_id' => null, 'status' => 'pending']);
        $cancel = OvertimeRecord::factory()->create(['employee_id' => $this->employee->id, 'attendance_record_id' => null, 'status' => 'pending']);

        $this->post(route('overtime-records.approve', $approve))->assertRedirect();
        $this->post(route('overtime-records.reject', $reject), ['rejection_reason' => 'Not approved'])->assertRedirect();
        $this->post(route('overtime-records.cancel', $cancel), ['cancellation_reason' => 'Duplicate request'])->assertRedirect();

        $this->assertDatabaseHas('overtime_records', ['id' => $approve->id, 'status' => 'approved', 'approved_by' => $this->user->id]);
        $this->assertDatabaseHas('overtime_records', ['id' => $reject->id, 'status' => 'rejected', 'rejected_by' => $this->user->id, 'rejection_reason' => 'Not approved']);
        $this->assertDatabaseHas('overtime_records', ['id' => $cancel->id, 'status' => 'cancelled', 'cancelled_by' => $this->user->id, 'cancellation_reason' => 'Duplicate request']);
    }

    public function test_index_page_can_filter_records(): void
    {
        OvertimeRecord::factory()->create(['employee_id' => $this->employee->id, 'attendance_record_id' => null, 'status' => 'approved', 'overtime_date' => '2026-05-18']);

        $this->get(route('overtime-records.index', [
            'search' => $this->employee->employee_id,
            'status' => 'approved',
            'date_from' => '2026-05-01',
            'date_to' => '2026-05-31',
        ]))->assertOk();
    }
}

<?php

namespace Tests\Feature;

use App\Models\Employee;
use App\Models\EmployeeShiftAssignment;
use App\Models\User;
use App\Models\WorkShift;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WorkShiftTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();

        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    }

    public function test_shift_index_page_can_be_opened(): void
    {
        WorkShift::factory()->create();

        $this->get(route('work-shifts.index'))->assertOk();
    }

    public function test_user_can_create_work_shift(): void
    {
        $response = $this->post(route('work-shifts.store'), [
            'shift_code' => 'SHIFT-A',
            'name' => 'Shift A',
            'check_in_time' => '08:00',
            'check_out_time' => '17:00',
            'tolerance_minutes' => 15,
            'is_overnight' => false,
            'is_active' => true,
            'notes' => 'Regular shift',
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('work_shifts', [
            'shift_code' => 'SHIFT-A',
            'name' => 'Shift A',
            'created_by' => $this->user->id,
        ]);
    }

    public function test_shift_can_be_updated_and_deactivated(): void
    {
        $shift = WorkShift::factory()->create(['created_by' => $this->user->id]);

        $this->put(route('work-shifts.update', $shift), [
            'shift_code' => $shift->shift_code,
            'name' => 'Night Shift',
            'check_in_time' => '22:00',
            'check_out_time' => '06:00',
            'tolerance_minutes' => 30,
            'is_overnight' => true,
            'is_active' => true,
            'notes' => 'Updated',
        ])->assertRedirect();

        $this->assertDatabaseHas('work_shifts', [
            'id' => $shift->id,
            'name' => 'Night Shift',
            'is_overnight' => true,
            'updated_by' => $this->user->id,
        ]);

        $this->delete(route('work-shifts.destroy', $shift))->assertRedirect(route('work-shifts.index'));

        $this->assertDatabaseHas('work_shifts', [
            'id' => $shift->id,
            'is_active' => false,
            'updated_by' => $this->user->id,
        ]);
    }

    public function test_user_can_create_employee_shift_assignment(): void
    {
        $employee = Employee::factory()->create(['employment_status' => 'active']);
        $shift = WorkShift::factory()->create(['is_active' => true]);

        $response = $this->post(route('employee-shift-assignments.store'), [
            'employee_id' => $employee->id,
            'work_shift_id' => $shift->id,
            'effective_date' => '2026-05-18',
            'status' => 'active',
            'notes' => 'Default shift',
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('employee_shift_assignments', [
            'employee_id' => $employee->id,
            'work_shift_id' => $shift->id,
            'status' => 'active',
            'created_by' => $this->user->id,
        ]);
    }

    public function test_new_active_shift_assignment_deactivates_previous_active_assignment(): void
    {
        $employee = Employee::factory()->create(['employment_status' => 'active']);
        $oldShift = WorkShift::factory()->create(['is_active' => true]);
        $newShift = WorkShift::factory()->create(['is_active' => true]);

        $oldAssignment = EmployeeShiftAssignment::factory()->create([
            'employee_id' => $employee->id,
            'work_shift_id' => $oldShift->id,
            'status' => 'active',
            'created_by' => $this->user->id,
        ]);

        $this->post(route('employee-shift-assignments.store'), [
            'employee_id' => $employee->id,
            'work_shift_id' => $newShift->id,
            'effective_date' => '2026-05-19',
            'status' => 'active',
        ])->assertRedirect();

        $this->assertDatabaseHas('employee_shift_assignments', [
            'id' => $oldAssignment->id,
            'status' => 'inactive',
            'updated_by' => $this->user->id,
        ]);

        $this->assertEquals(1, EmployeeShiftAssignment::where('employee_id', $employee->id)->where('status', 'active')->count());
    }
}

<?php

namespace Tests\Feature;

use App\Models\AttendanceRecord;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceRecordTest extends TestCase
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

    public function test_user_can_check_in(): void
    {
        $response = $this->actingAs($this->user)->post(route('attendance-records.store'), [
            'employee_id' => $this->employee->id,
            'attendance_date' => '2026-05-18',
            'check_in_at' => '08:00',
            'notes' => 'Online check in',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('attendance_records', [
            'employee_id' => $this->employee->id,
            'attendance_date' => '2026-05-18 00:00:00',
            'status' => 'incomplete',
            'created_by' => $this->user->id,
        ]);
    }

    public function test_duplicate_open_check_in_same_day_is_rejected(): void
    {
        AttendanceRecord::factory()->create([
            'employee_id' => $this->employee->id,
            'attendance_date' => '2026-05-18',
            'check_in_at' => '2026-05-18 08:00:00',
            'check_out_at' => null,
            'created_by' => $this->user->id,
        ]);

        $this->actingAs($this->user)->post(route('attendance-records.store'), [
            'employee_id' => $this->employee->id,
            'attendance_date' => '2026-05-18',
            'check_in_at' => '09:00',
        ])->assertSessionHasErrors(['employee_id']);
    }

    public function test_user_can_check_out_and_work_hours_are_calculated(): void
    {
        $record = AttendanceRecord::factory()->create([
            'employee_id' => $this->employee->id,
            'attendance_date' => '2026-05-18',
            'check_in_at' => '2026-05-18 08:00:00',
            'check_out_at' => null,
            'created_by' => $this->user->id,
        ]);

        $this->actingAs($this->user)->post(route('attendance-records.check-out', $record), [
            'check_out_at' => '17:00',
        ])->assertRedirect();

        $record->refresh();
        $this->assertSame('present', $record->status);
        $this->assertSame('9.00', $record->work_hours);
        $this->assertSame($this->user->id, $record->updated_by);
    }

    public function test_index_can_filter_attendance_records(): void
    {
        AttendanceRecord::factory()->create([
            'employee_id' => $this->employee->id,
            'attendance_date' => '2026-05-18',
            'status' => 'present',
            'created_by' => $this->user->id,
        ]);

        $this->actingAs($this->user)->get(route('attendance-records.index', [
            'search' => $this->employee->employee_id,
            'status' => 'present',
            'date_from' => '2026-05-01',
            'date_to' => '2026-05-31',
        ]))->assertOk();
    }
}

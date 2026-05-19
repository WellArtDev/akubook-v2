<?php

namespace Tests\Feature;

use App\Models\AttendanceRecord;
use App\Models\Employee;
use App\Models\EmployeeDocument;
use App\Models\EmployeeShiftAssignment;
use App\Models\LeaveRequest;
use App\Models\OvertimeRecord;
use App\Models\User;
use App\Models\WorkShift;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class HrReportTest extends TestCase
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

    public function test_hr_report_page_can_be_opened(): void
    {
        $this->get(route('hr-reports.index'))->assertOk();
    }

    public function test_hr_report_summarizes_employee_attendance_leave_overtime_and_documents(): void
    {
        $employee = Employee::factory()->create([
            'employee_id' => 'EMP-HR-001',
            'full_name' => 'Alpha HR',
            'employment_status' => 'active',
        ]);
        Employee::factory()->create(['employment_status' => 'resigned']);
        $shift = WorkShift::factory()->create(['is_active' => true]);
        EmployeeShiftAssignment::factory()->create([
            'employee_id' => $employee->id,
            'work_shift_id' => $shift->id,
            'status' => 'active',
        ]);

        AttendanceRecord::factory()->create([
            'employee_id' => $employee->id,
            'attendance_date' => '2026-05-01',
            'status' => 'present',
            'work_hours' => 8,
        ]);
        AttendanceRecord::factory()->create([
            'employee_id' => $employee->id,
            'attendance_date' => '2026-05-02',
            'status' => 'incomplete',
            'work_hours' => 0,
        ]);
        AttendanceRecord::factory()->create([
            'employee_id' => $employee->id,
            'attendance_date' => '2026-05-03',
            'status' => 'absent',
            'work_hours' => 0,
        ]);

        LeaveRequest::factory()->create([
            'employee_id' => $employee->id,
            'start_date' => '2026-05-10',
            'end_date' => '2026-05-11',
            'total_days' => 2,
            'status' => 'approved',
        ]);

        OvertimeRecord::factory()->create([
            'employee_id' => $employee->id,
            'attendance_record_id' => null,
            'overtime_date' => '2026-05-12',
            'hours' => 2.5,
            'status' => 'approved',
        ]);

        EmployeeDocument::factory()->create([
            'employee_id' => $employee->id,
            'status' => 'active',
            'expiry_date' => '2026-05-20',
        ]);
        EmployeeDocument::factory()->create([
            'employee_id' => $employee->id,
            'status' => 'active',
            'expiry_date' => '2026-04-01',
        ]);

        $this->get(route('hr-reports.index', [
            'date_from' => '2026-05-01',
            'date_to' => '2026-05-31',
            'search' => 'Alpha',
        ]))->assertOk()->assertInertia(fn (Assert $page) => $page
            ->component('HrReports/Index')
            ->where('employee_summary.active_employees', 1)
            ->where('employee_summary.inactive_or_resigned', 0)
            ->where('employee_summary.with_active_assignment', 1)
            ->where('attendance_summary.present_count', 1)
            ->where('attendance_summary.incomplete_count', 1)
            ->where('attendance_summary.absent_count', 1)
            ->where('attendance_summary.work_hours_total', 8)
            ->where('leave_summary.approved_count', 1)
            ->where('leave_summary.leave_days_total', 2)
            ->where('overtime_summary.approved_count', 1)
            ->where('overtime_summary.approved_hours_total', 2.5)
            ->where('document_summary.active_documents', 2)
            ->where('document_summary.expired_documents', 1)
            ->where('employee_rows.0.employee_id', 'EMP-HR-001')
        );
    }

    public function test_hr_report_period_filter_excludes_outside_attendance(): void
    {
        $employee = Employee::factory()->create(['employee_id' => 'EMP-PERIOD', 'employment_status' => 'active']);
        AttendanceRecord::factory()->create([
            'employee_id' => $employee->id,
            'attendance_date' => '2026-04-01',
            'status' => 'present',
            'work_hours' => 8,
        ]);

        $this->get(route('hr-reports.index', [
            'date_from' => '2026-05-01',
            'date_to' => '2026-05-31',
            'search' => 'EMP-PERIOD',
        ]))->assertOk()->assertInertia(fn (Assert $page) => $page
            ->where('attendance_summary.present_count', 0)
            ->where('attendance_summary.work_hours_total', 0)
        );
    }
}

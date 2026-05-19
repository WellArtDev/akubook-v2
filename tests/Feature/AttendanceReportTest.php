<?php

namespace Tests\Feature;

use App\Models\AttendanceRecord;
use App\Models\Employee;
use App\Models\OvertimeRecord;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class AttendanceReportTest extends TestCase
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
        $this->employee = Employee::factory()->create([
            'employment_status' => 'active',
            'employee_id' => 'EMP-RPT-01',
            'full_name' => 'Report Employee',
        ]);
    }

    public function test_attendance_report_page_can_be_opened(): void
    {
        $this->get(route('attendance-reports.index'))->assertOk();
    }

    public function test_attendance_report_summary_and_overtime_totals(): void
    {
        AttendanceRecord::factory()->create([
            'employee_id' => $this->employee->id,
            'attendance_date' => '2026-05-18',
            'work_hours' => 8,
            'status' => 'present',
            'check_in_at' => '2026-05-18 08:00:00',
            'check_out_at' => '2026-05-18 16:00:00',
        ]);

        AttendanceRecord::factory()->create([
            'employee_id' => $this->employee->id,
            'attendance_date' => '2026-05-19',
            'work_hours' => 0,
            'status' => 'incomplete',
            'check_in_at' => '2026-05-19 08:00:00',
            'check_out_at' => null,
        ]);

        OvertimeRecord::factory()->create([
            'employee_id' => $this->employee->id,
            'attendance_record_id' => null,
            'overtime_date' => '2026-05-18',
            'hours' => 2.5,
            'status' => 'approved',
        ]);

        OvertimeRecord::factory()->create([
            'employee_id' => $this->employee->id,
            'attendance_record_id' => null,
            'overtime_date' => '2026-05-18',
            'hours' => 1,
            'status' => 'pending',
        ]);

        $this->get(route('attendance-reports.index', [
            'date_from' => '2026-05-01',
            'date_to' => '2026-05-31',
        ]))->assertInertia(fn (Assert $page) => $page
            ->component('AttendanceReports/Index')
            ->where('summary.total_records', 2)
            ->where('summary.present_count', 1)
            ->where('summary.incomplete_count', 1)
            ->where('summary.absent_count', 0)
            ->where('summary.total_work_hours', 8)
            ->where('summary.total_overtime_hours', 2.5)
        );
    }

    public function test_attendance_report_can_filter_by_status_and_search(): void
    {
        $other = Employee::factory()->create([
            'employment_status' => 'active',
            'employee_id' => 'EMP-RPT-99',
            'full_name' => 'Other Employee',
        ]);

        AttendanceRecord::factory()->create([
            'employee_id' => $this->employee->id,
            'attendance_date' => '2026-05-18',
            'status' => 'present',
        ]);

        AttendanceRecord::factory()->create([
            'employee_id' => $other->id,
            'attendance_date' => '2026-05-18',
            'status' => 'absent',
        ]);

        $this->get(route('attendance-reports.index', [
            'search' => 'EMP-RPT-99',
            'status' => 'absent',
            'date_from' => '2026-05-01',
            'date_to' => '2026-05-31',
        ]))->assertInertia(fn (Assert $page) => $page
            ->component('AttendanceReports/Index')
            ->where('summary.total_records', 1)
            ->where('rows.0.employee_id', 'EMP-RPT-99')
            ->where('rows.0.status', 'absent')
        );
    }
}

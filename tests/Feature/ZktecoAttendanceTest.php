<?php

namespace Tests\Feature;

use App\Models\AttendanceRecord;
use App\Models\Employee;
use App\Models\User;
use App\Models\ZktecoAttendanceLog;
use App\Models\ZktecoDevice;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ZktecoAttendanceTest extends TestCase
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

    public function test_zkteco_log_index_can_be_opened(): void
    {
        $device = ZktecoDevice::factory()->create(['created_by' => $this->user->id]);
        ZktecoAttendanceLog::factory()->create([
            'zkteco_device_id' => $device->id,
            'employee_identifier' => 'EMP-0001',
            'source_key' => hash('sha256', 'a'),
            'created_by' => $this->user->id,
        ]);

        $this->get(route('zkteco-attendance.index'))
            ->assertOk();
    }

    public function test_import_check_in_creates_mapped_attendance_record(): void
    {
        $employee = Employee::factory()->create(['employee_id' => 'EMP-1001', 'employment_status' => 'active', 'created_by' => $this->user->id]);
        $device = ZktecoDevice::factory()->create(['created_by' => $this->user->id]);

        $this->post(route('zkteco-attendance.store'), [
            'zkteco_device_id' => $device->id,
            'employee_identifier' => 'EMP-1001',
            'punch_at' => '2026-05-18 08:00:00',
            'punch_type' => 'check_in',
        ])->assertRedirect();

        $this->assertDatabaseHas('zkteco_attendance_logs', [
            'zkteco_device_id' => $device->id,
            'employee_identifier' => 'EMP-1001',
            'employee_id' => $employee->id,
            'is_mapped' => true,
            'punch_type' => 'check_in',
        ]);

        $this->assertDatabaseHas('attendance_records', [
            'employee_id' => $employee->id,
            'attendance_date' => '2026-05-18 00:00:00',
            'status' => 'incomplete',
        ]);
    }

    public function test_import_check_out_updates_existing_attendance_record(): void
    {
        $employee = Employee::factory()->create(['employee_id' => 'EMP-2001', 'employment_status' => 'active', 'created_by' => $this->user->id]);
        $device = ZktecoDevice::factory()->create(['created_by' => $this->user->id]);

        $attendance = AttendanceRecord::create([
            'employee_id' => $employee->id,
            'attendance_date' => '2026-05-18',
            'check_in_at' => '2026-05-18 08:00:00',
            'status' => 'incomplete',
            'created_by' => $this->user->id,
        ]);

        $this->post(route('zkteco-attendance.store'), [
            'zkteco_device_id' => $device->id,
            'employee_identifier' => 'EMP-2001',
            'punch_at' => '2026-05-18 17:00:00',
            'punch_type' => 'check_out',
        ])->assertRedirect();

        $attendance->refresh();

        $this->assertNotNull($attendance->check_out_at);
        $this->assertSame('present', $attendance->status);
        $this->assertSame('9.00', $attendance->work_hours);
    }

    public function test_duplicate_source_log_is_ignored(): void
    {
        $device = ZktecoDevice::factory()->create(['created_by' => $this->user->id]);

        $payload = [
            'zkteco_device_id' => $device->id,
            'employee_identifier' => 'EMP-9999',
            'punch_at' => '2026-05-18 08:30:00',
            'punch_type' => 'check_in',
        ];

        $this->post(route('zkteco-attendance.store'), $payload)->assertRedirect();
        $this->post(route('zkteco-attendance.store'), $payload)->assertRedirect();

        $this->assertSame(1, ZktecoAttendanceLog::count());
    }
}

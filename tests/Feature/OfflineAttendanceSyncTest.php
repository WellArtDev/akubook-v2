<?php

namespace Tests\Feature;

use App\Models\AttendanceRecord;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OfflineAttendanceSyncTest extends TestCase
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

    public function test_sync_endpoint_creates_attendance_record(): void
    {
        $employee = Employee::factory()->create([
            'employee_id' => 'EMP-OFF-001',
            'employment_status' => 'active',
        ]);

        $payload = [
            'events' => [
                [
                    'employee_identifier' => 'EMP-OFF-001',
                    'clock_type' => 'check_in',
                    'clock_at' => '2026-05-19 08:00:00',
                    'source' => 'offline_form',
                ],
                [
                    'employee_identifier' => 'EMP-OFF-001',
                    'clock_type' => 'check_out',
                    'clock_at' => '2026-05-19 17:00:00',
                    'source' => 'offline_form',
                ],
            ],
        ];

        $response = $this->postJson(route('offline-attendance-sync.sync'), $payload);

        $response->assertOk()->assertJsonPath('processed', 2);

        $record = AttendanceRecord::where('employee_id', $employee->id)->whereDate('attendance_date', '2026-05-19')->first();

        $this->assertNotNull($record);
        $this->assertEquals('present', $record->status);
        $this->assertEquals('9.00', (string) $record->work_hours);

        $this->assertDatabaseCount('offline_attendance_syncs', 2);
    }

    public function test_sync_endpoint_is_idempotent_by_sync_key(): void
    {
        Employee::factory()->create([
            'employee_id' => 'EMP-OFF-002',
            'employment_status' => 'active',
        ]);

        $payload = [
            'events' => [
                [
                    'employee_identifier' => 'EMP-OFF-002',
                    'clock_type' => 'check_in',
                    'clock_at' => '2026-05-19 08:30:00',
                    'source' => 'offline_form',
                ],
            ],
        ];

        $this->postJson(route('offline-attendance-sync.sync'), $payload)->assertOk();
        $second = $this->postJson(route('offline-attendance-sync.sync'), $payload);

        $second->assertOk()->assertJsonPath('results.0.status', 'duplicate');

        $this->assertDatabaseCount('offline_attendance_syncs', 1);
    }

    public function test_sync_marks_failed_when_employee_not_found(): void
    {
        $payload = [
            'events' => [
                [
                    'employee_identifier' => 'EMP-NOT-FOUND',
                    'clock_type' => 'check_in',
                    'clock_at' => '2026-05-19 08:00:00',
                    'source' => 'offline_form',
                ],
            ],
        ];

        $response = $this->postJson(route('offline-attendance-sync.sync'), $payload);

        $response->assertOk()->assertJsonPath('results.0.status', 'failed');

        $this->assertDatabaseHas('offline_attendance_syncs', [
            'employee_identifier' => 'EMP-NOT-FOUND',
            'status' => 'failed',
        ]);
    }
}

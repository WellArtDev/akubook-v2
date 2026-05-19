<?php

namespace Tests\Feature;

use App\Models\AttendanceRecord;
use App\Models\Employee;
use App\Models\OfflineSyncEvent;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OfflineSyncTest extends TestCase
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
            'employee_id' => 'EMP-SYNC-001',
            'employment_status' => 'active',
        ]);

        $response = $this->postJson(route('offline-sync.sync'), [
            'events' => [
                [
                    'client_event_id' => 'evt-sync-1',
                    'entity' => 'attendance',
                    'action' => 'check_in',
                    'payload' => [
                        'employee_identifier' => 'EMP-SYNC-001',
                        'clock_type' => 'check_in',
                        'clock_at' => '2026-05-19 08:00:00',
                    ],
                ],
                [
                    'client_event_id' => 'evt-sync-2',
                    'entity' => 'attendance',
                    'action' => 'check_out',
                    'payload' => [
                        'employee_identifier' => 'EMP-SYNC-001',
                        'clock_type' => 'check_out',
                        'clock_at' => '2026-05-19 17:00:00',
                    ],
                ],
            ],
        ]);

        $response->assertOk()->assertJson(['processed' => 2]);

        $this->assertDatabaseHas('attendance_records', [
            'employee_id' => $employee->id,
            'attendance_date' => '2026-05-19 00:00:00',
            'status' => 'present',
            'work_hours' => '9.00',
        ]);

        $this->assertEquals(2, OfflineSyncEvent::where('status', 'synced')->count());
    }

    public function test_sync_endpoint_is_idempotent_by_client_event_id(): void
    {
        Employee::factory()->create([
            'employee_id' => 'EMP-SYNC-002',
            'employment_status' => 'active',
        ]);

        $payload = [
            'events' => [[
                'client_event_id' => 'evt-duplicate-1',
                'entity' => 'attendance',
                'action' => 'check_in',
                'payload' => [
                    'employee_identifier' => 'EMP-SYNC-002',
                    'clock_type' => 'check_in',
                    'clock_at' => '2026-05-19 08:15:00',
                ],
            ]],
        ];

        $this->postJson(route('offline-sync.sync'), $payload)->assertOk();
        $this->postJson(route('offline-sync.sync'), $payload)
            ->assertOk()
            ->assertJsonPath('results.0.status', 'duplicate');

        $this->assertEquals(1, OfflineSyncEvent::where('client_event_id', 'evt-duplicate-1')->count());

        $event = OfflineSyncEvent::where('client_event_id', 'evt-duplicate-1')->firstOrFail();
        $this->assertSame('EMP-SYNC-002', $event->encrypted_payload['employee_identifier']);
        $this->assertStringNotContainsString('EMP-SYNC-002', $event->getRawOriginal('encrypted_payload'));
    }

    public function test_sync_marks_failed_when_employee_not_found(): void
    {
        $response = $this->postJson(route('offline-sync.sync'), [
            'events' => [[
                'client_event_id' => 'evt-failed-1',
                'entity' => 'attendance',
                'action' => 'check_in',
                'payload' => [
                    'employee_identifier' => 'EMP-NOT-FOUND',
                    'clock_type' => 'check_in',
                    'clock_at' => '2026-05-19 07:50:00',
                ],
            ]],
        ]);

        $response->assertOk()->assertJsonPath('results.0.status', 'failed');

        $this->assertDatabaseHas('offline_sync_events', [
            'client_event_id' => 'evt-failed-1',
            'status' => 'failed',
        ]);
        $this->assertEquals(0, AttendanceRecord::count());
    }
}

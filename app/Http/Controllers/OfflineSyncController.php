<?php

namespace App\Http\Controllers;

use App\Models\AttendanceRecord;
use App\Models\Employee;
use App\Models\OfflineSyncEvent;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class OfflineSyncController extends Controller
{
    public function index()
    {
        return Inertia::render('OfflineSync/Index');
    }

    public function sync(Request $request)
    {
        $data = $request->validate([
            'events' => ['required', 'array', 'min:1'],
            'events.*.client_event_id' => ['required', 'string', 'max:191'],
            'events.*.entity' => ['required', 'in:attendance'],
            'events.*.action' => ['required', 'in:check_in,check_out'],
            'events.*.payload' => ['required', 'array'],
            'events.*.payload.employee_identifier' => ['required', 'string'],
            'events.*.payload.clock_at' => ['required', 'date'],
            'events.*.payload.clock_type' => ['required', 'in:check_in,check_out'],
        ]);

        $results = [];

        foreach ($data['events'] as $event) {
            $existing = OfflineSyncEvent::where('client_event_id', $event['client_event_id'])->first();
            if ($existing) {
                $results[] = ['client_event_id' => $event['client_event_id'], 'status' => 'duplicate'];
                continue;
            }

            if ($event['entity'] !== 'attendance') {
                OfflineSyncEvent::create([
                    'client_event_id' => $event['client_event_id'],
                    'entity' => $event['entity'],
                    'action' => $event['action'],
                    'payload' => $event['payload'],
                    'status' => 'failed',
                    'failure_reason' => 'Unsupported entity',
                    'created_by' => Auth::id(),
                ]);
                $results[] = ['client_event_id' => $event['client_event_id'], 'status' => 'failed'];
                continue;
            }

            $result = DB::transaction(function () use ($event) {
                $employee = Employee::where('employee_id', $event['payload']['employee_identifier'])
                    ->where('employment_status', 'active')
                    ->first();

                if (! $employee) {
                    OfflineSyncEvent::create([
                        'client_event_id' => $event['client_event_id'],
                        'entity' => 'attendance',
                        'action' => $event['action'],
                        'payload' => [
                            'clock_type' => $event['payload']['clock_type'] ?? null,
                            'clock_at' => $event['payload']['clock_at'] ?? null,
                        ],
                        'encrypted_payload' => $event['payload'],
                        'status' => 'failed',
                        'failure_reason' => 'Employee not found',
                        'created_by' => Auth::id(),
                    ]);
                    return ['client_event_id' => $event['client_event_id'], 'status' => 'failed'];
                }

                $clockAt = Carbon::parse($event['payload']['clock_at']);
                $record = AttendanceRecord::where('employee_id', $employee->id)
                    ->whereDate('attendance_date', $clockAt->toDateString())
                    ->first();

                if (! $record) {
                    $record = AttendanceRecord::create([
                        'employee_id' => $employee->id,
                        'attendance_date' => $clockAt->toDateString(),
                        'status' => 'incomplete',
                        'created_by' => Auth::id(),
                    ]);
                }

                if ($event['action'] === 'check_in') {
                    if (! $record->check_in_at || $clockAt->lt($record->check_in_at)) {
                        $record->check_in_at = $clockAt;
                    }
                }

                if ($event['action'] === 'check_out') {
                    if (! $record->check_out_at || $clockAt->gt($record->check_out_at)) {
                        $record->check_out_at = $clockAt;
                    }
                }

                if ($record->check_in_at && $record->check_out_at && $record->check_out_at->gt($record->check_in_at)) {
                    $record->work_hours = round($record->check_in_at->diffInMinutes($record->check_out_at) / 60, 2);
                    $record->status = 'present';
                }

                $record->updated_by = Auth::id();
                $record->save();

                OfflineSyncEvent::create([
                    'client_event_id' => $event['client_event_id'],
                    'entity' => 'attendance',
                    'action' => $event['action'],
                    'payload' => [
                        'clock_type' => $event['payload']['clock_type'],
                        'clock_at' => $event['payload']['clock_at'],
                    ],
                    'encrypted_payload' => $event['payload'],
                    'status' => 'synced',
                    'source_type' => 'attendance_record',
                    'source_id' => $record->id,
                    'created_by' => Auth::id(),
                ]);

                return ['client_event_id' => $event['client_event_id'], 'status' => 'synced'];
            });

            $results[] = $result;
        }

        return response()->json([
            'processed' => count($results),
            'results' => $results,
        ]);
    }
}

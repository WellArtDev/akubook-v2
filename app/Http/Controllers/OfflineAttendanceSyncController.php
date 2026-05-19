<?php

namespace App\Http\Controllers;

use App\Models\AttendanceRecord;
use App\Models\Employee;
use App\Models\OfflineAttendanceSync;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class OfflineAttendanceSyncController extends Controller
{
    public function sync(Request $request)
    {
        $validated = $request->validate([
            'events' => ['required', 'array', 'min:1'],
            'events.*.employee_identifier' => ['required', 'string', 'max:50'],
            'events.*.clock_type' => ['required', 'in:check_in,check_out'],
            'events.*.clock_at' => ['required', 'date'],
            'events.*.source' => ['nullable', 'string', 'max:20'],
        ]);

        $results = [];

        foreach ($validated['events'] as $event) {
            $clockAt = Carbon::parse($event['clock_at']);
            $key = hash('sha256', implode('|', [
                $event['employee_identifier'],
                $clockAt->toDateTimeString(),
                $event['clock_type'],
                $event['source'] ?? 'offline',
            ]));

            $existing = OfflineAttendanceSync::where('sync_key', $key)->first();
            if ($existing) {
                $results[] = ['sync_key' => $key, 'status' => 'duplicate'];
                continue;
            }

            $employee = Employee::where('employee_id', $event['employee_identifier'])
                ->where('employment_status', 'active')
                ->first();

            if (! $employee) {
                OfflineAttendanceSync::create([
                    'sync_key' => $key,
                    'employee_identifier' => $event['employee_identifier'],
                    'clock_type' => $event['clock_type'],
                    'clock_at' => $clockAt,
                    'status' => 'failed',
                    'failure_reason' => 'Employee not found or inactive',
                    'created_by' => $request->user()?->id,
                ]);

                $results[] = ['sync_key' => $key, 'status' => 'failed'];
                continue;
            }

            $record = AttendanceRecord::where('employee_id', $employee->id)
                ->whereDate('attendance_date', $clockAt->toDateString())
                ->first();

            if (! $record) {
                $record = AttendanceRecord::create([
                    'employee_id' => $employee->id,
                    'attendance_date' => $clockAt->toDateString(),
                    'status' => 'incomplete',
                    'created_by' => $request->user()?->id,
                ]);
            }

            if ($event['clock_type'] === 'check_in') {
                if (! $record->check_in_at || $clockAt->lt($record->check_in_at)) {
                    $record->check_in_at = $clockAt;
                }
            } else {
                if (! $record->check_out_at || $clockAt->gt($record->check_out_at)) {
                    $record->check_out_at = $clockAt;
                }
            }

            if ($record->check_in_at && $record->check_out_at && $record->check_out_at->gt($record->check_in_at)) {
                $record->work_hours = round($record->check_in_at->diffInMinutes($record->check_out_at) / 60, 2);
                $record->status = 'present';
            }

            $record->updated_by = $request->user()?->id;
            $record->save();

            OfflineAttendanceSync::create([
                'sync_key' => $key,
                'employee_id' => $employee->id,
                'employee_identifier' => $event['employee_identifier'],
                'clock_type' => $event['clock_type'],
                'clock_at' => $clockAt,
                'status' => 'synced',
                'source_type' => 'attendance_record',
                'source_id' => $record->id,
                'created_by' => $request->user()?->id,
            ]);

            $results[] = ['sync_key' => $key, 'status' => 'synced'];
        }

        return response()->json([
            'processed' => count($results),
            'results' => $results,
        ]);
    }
}

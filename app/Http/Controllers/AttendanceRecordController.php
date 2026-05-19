<?php

namespace App\Http\Controllers;

use App\Models\AttendanceRecord;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class AttendanceRecordController extends Controller
{
    public function index(Request $request)
    {
        $query = AttendanceRecord::with(['employee', 'creator', 'updater']);

        if ($request->filled('search')) {
            $search = $request->string('search')->value();
            $query->whereHas('employee', function ($q) use ($search) {
                $q->where('employee_id', 'like', "%{$search}%")
                    ->orWhere('full_name', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->string('status')->value());
        }

        if ($request->filled('date_from')) {
            $query->whereDate('attendance_date', '>=', $request->string('date_from')->value());
        }

        if ($request->filled('date_to')) {
            $query->whereDate('attendance_date', '<=', $request->string('date_to')->value());
        }

        return Inertia::render('AttendanceRecords/Index', [
            'attendanceRecords' => $query->orderByDesc('attendance_date')->orderByDesc('id')->paginate(50)->withQueryString(),
            'filters' => $request->only(['search', 'status', 'date_from', 'date_to']),
            'statuses' => AttendanceRecord::STATUSES,
        ]);
    }

    public function create()
    {
        return Inertia::render('AttendanceRecords/Create', [
            'employees' => Employee::query()
                ->where('employment_status', 'active')
                ->orderBy('employee_id')
                ->get(['id', 'employee_id', 'full_name']),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => ['required', 'exists:employees,id'],
            'attendance_date' => ['required', 'date'],
            'check_in_at' => ['required', 'date_format:H:i'],
            'notes' => ['nullable', 'string'],
        ]);

        $exists = AttendanceRecord::query()
            ->where('employee_id', $validated['employee_id'])
            ->whereDate('attendance_date', $validated['attendance_date'])
            ->whereNull('check_out_at')
            ->exists();

        if ($exists) {
            return back()->withErrors(['employee_id' => 'Employee already has pending check-in for this date.']);
        }

        $checkIn = "{$validated['attendance_date']} {$validated['check_in_at']}:00";

        $record = AttendanceRecord::create([
            'employee_id' => $validated['employee_id'],
            'attendance_date' => $validated['attendance_date'],
            'check_in_at' => $checkIn,
            'status' => 'incomplete',
            'notes' => $validated['notes'] ?? null,
            'created_by' => Auth::id(),
        ]);

        return redirect()->route('attendance-records.show', $record)->with('success', 'Check-in recorded.');
    }

    public function show(AttendanceRecord $attendanceRecord)
    {
        $attendanceRecord->load(['employee', 'creator', 'updater']);

        return Inertia::render('AttendanceRecords/Show', [
            'record' => $attendanceRecord,
        ]);
    }

    public function checkOut(Request $request, AttendanceRecord $attendanceRecord)
    {
        if ($attendanceRecord->check_out_at) {
            return back()->withErrors(['status' => 'Record already checked out.']);
        }

        $validated = $request->validate([
            'check_out_at' => ['required', 'date_format:H:i'],
            'notes' => ['nullable', 'string'],
        ]);

        $checkOut = \Illuminate\Support\Carbon::parse("{$attendanceRecord->attendance_date->toDateString()} {$validated['check_out_at']}:00");

        if ($checkOut->lessThanOrEqualTo($attendanceRecord->check_in_at)) {
            return back()->withErrors(['check_out_at' => 'Check-out must be after check-in.']);
        }

        $hours = round($attendanceRecord->check_in_at->diffInMinutes($checkOut) / 60, 2);

        $attendanceRecord->update([
            'check_out_at' => $checkOut,
            'work_hours' => $hours,
            'status' => 'present',
            'notes' => $validated['notes'] ?? $attendanceRecord->notes,
            'updated_by' => Auth::id(),
        ]);

        return back()->with('success', 'Check-out recorded.');
    }
}

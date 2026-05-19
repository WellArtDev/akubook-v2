<?php

namespace App\Http\Controllers;

use App\Models\AttendanceRecord;
use App\Models\Employee;
use App\Models\OvertimeRecord;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class OvertimeRecordController extends Controller
{
    public function index(Request $request)
    {
        $records = OvertimeRecord::query()
            ->with(['employee', 'attendanceRecord'])
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = '%' . str_replace(['%', '_'], ['\\%', '\\_'], $request->search) . '%';
                $query->whereHas('employee', fn ($employee) => $employee->where('employee_id', 'like', $search)->orWhere('full_name', 'like', $search));
            })
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->status))
            ->when($request->filled('date_from'), fn ($query) => $query->whereDate('overtime_date', '>=', $request->date_from))
            ->when($request->filled('date_to'), fn ($query) => $query->whereDate('overtime_date', '<=', $request->date_to))
            ->latest('overtime_date')
            ->paginate(50)
            ->withQueryString();

        return Inertia::render('OvertimeRecords/Index', [
            'records' => $records,
            'statuses' => OvertimeRecord::STATUSES,
            'filters' => $request->only(['search', 'status', 'date_from', 'date_to']),
        ]);
    }

    public function create()
    {
        return Inertia::render('OvertimeRecords/Create', [
            'employees' => $this->employees(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validateRecord($request);
        $start = Carbon::parse($data['start_at']);
        $end = Carbon::parse($data['end_at']);

        if ($end->lessThanOrEqualTo($start)) {
            throw ValidationException::withMessages(['end_at' => 'End time must be after start time.']);
        }

        $attendance = AttendanceRecord::query()
            ->where('employee_id', $data['employee_id'])
            ->whereDate('attendance_date', $data['overtime_date'])
            ->first();

        $this->ensureNoOverlap((int) $data['employee_id'], $data['overtime_date'], $start, $end);

        $record = OvertimeRecord::create([
            ...$data,
            'attendance_record_id' => $attendance?->id,
            'hours' => round($start->diffInMinutes($end) / 60, 2),
            'status' => 'pending',
            'created_by' => Auth::id(),
        ]);

        return redirect()->route('overtime-records.show', $record)->with('success', 'Overtime dibuat.');
    }

    public function show(OvertimeRecord $overtimeRecord)
    {
        $overtimeRecord->load(['employee', 'attendanceRecord', 'creator', 'approver']);

        return Inertia::render('OvertimeRecords/Show', [
            'record' => $overtimeRecord,
        ]);
    }

    public function approve(OvertimeRecord $overtimeRecord)
    {
        if ($overtimeRecord->status !== 'pending') {
            return back()->withErrors(['status' => 'Hanya overtime pending bisa disetujui.']);
        }

        $overtimeRecord->update([
            'status' => 'approved',
            'approved_by' => Auth::id(),
            'approved_at' => now(),
        ]);

        return back()->with('success', 'Overtime disetujui.');
    }

    public function reject(Request $request, OvertimeRecord $overtimeRecord)
    {
        if ($overtimeRecord->status !== 'pending') {
            return back()->withErrors(['status' => 'Hanya overtime pending bisa ditolak.']);
        }

        $data = $request->validate(['rejection_reason' => ['required', 'string', 'max:1000']]);

        $overtimeRecord->update([
            'status' => 'rejected',
            'rejected_by' => Auth::id(),
            'rejected_at' => now(),
            'rejection_reason' => $data['rejection_reason'],
        ]);

        return back()->with('success', 'Overtime ditolak.');
    }

    public function cancel(Request $request, OvertimeRecord $overtimeRecord)
    {
        if ($overtimeRecord->status !== 'pending') {
            return back()->withErrors(['status' => 'Hanya overtime pending bisa dibatalkan.']);
        }

        $data = $request->validate(['cancellation_reason' => ['required', 'string', 'max:1000']]);

        $overtimeRecord->update([
            'status' => 'cancelled',
            'cancelled_by' => Auth::id(),
            'cancelled_at' => now(),
            'cancellation_reason' => $data['cancellation_reason'],
        ]);

        return back()->with('success', 'Overtime dibatalkan.');
    }

    private function validateRecord(Request $request): array
    {
        return $request->validate([
            'employee_id' => ['required', Rule::exists('employees', 'id')->where('employment_status', 'active')],
            'overtime_date' => ['required', 'date'],
            'start_at' => ['required', 'date'],
            'end_at' => ['required', 'date'],
            'reason' => ['nullable', 'string', 'max:1000'],
        ]);
    }

    private function ensureNoOverlap(int $employeeId, string $date, Carbon $start, Carbon $end): void
    {
        $overlap = OvertimeRecord::query()
            ->where('employee_id', $employeeId)
            ->whereDate('overtime_date', $date)
            ->whereIn('status', ['pending', 'approved'])
            ->where('start_at', '<', $end)
            ->where('end_at', '>', $start)
            ->exists();

        if ($overlap) {
            throw ValidationException::withMessages(['start_at' => 'Overtime overlaps existing record.']);
        }
    }

    private function employees()
    {
        return Employee::where('employment_status', 'active')->orderBy('employee_id')->get(['id', 'employee_id', 'full_name']);
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\AttendanceRecord;
use App\Models\Employee;
use App\Models\EmployeeDocument;
use App\Models\EmployeeShiftAssignment;
use App\Models\LeaveRequest;
use App\Models\OvertimeRecord;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Inertia\Inertia;

class HrReportController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
            'search' => ['nullable', 'string'],
        ]);

        $dateFrom = $request->date_from ?: Carbon::now()->startOfMonth()->toDateString();
        $dateTo = $request->date_to ?: Carbon::now()->toDateString();
        $search = $request->search;

        $employeeBase = Employee::query()
            ->when($search, function ($query) use ($search) {
                $value = '%' . str_replace(['%', '_'], ['\\%', '\\_'], strtolower($search)) . '%';
                $query->whereRaw('LOWER(employee_id) LIKE ?', [$value])->orWhereRaw('LOWER(full_name) LIKE ?', [$value]);
            });

        $employeeIds = (clone $employeeBase)->pluck('id');

        $employeeSummary = [
            'active_employees' => (clone $employeeBase)->where('employment_status', 'active')->count(),
            'inactive_or_resigned' => (clone $employeeBase)->whereIn('employment_status', ['inactive', 'resigned'])->count(),
            'with_active_assignment' => EmployeeShiftAssignment::where('status', 'active')->whereIn('employee_id', $employeeIds)->distinct('employee_id')->count('employee_id'),
        ];

        $attendanceBase = AttendanceRecord::query()
            ->whereIn('employee_id', $employeeIds)
            ->whereDate('attendance_date', '>=', $dateFrom)
            ->whereDate('attendance_date', '<=', $dateTo);

        $attendanceSummary = [
            'present_count' => (clone $attendanceBase)->where('status', 'present')->count(),
            'incomplete_count' => (clone $attendanceBase)->where('status', 'incomplete')->count(),
            'absent_count' => (clone $attendanceBase)->where('status', 'absent')->count(),
            'work_hours_total' => round((float) (clone $attendanceBase)->sum('work_hours'), 2),
        ];

        $leaveBase = LeaveRequest::query()
            ->whereIn('employee_id', $employeeIds)
            ->whereDate('start_date', '>=', $dateFrom)
            ->whereDate('start_date', '<=', $dateTo);

        $leaveSummary = [
            'pending_count' => (clone $leaveBase)->where('status', 'pending')->count(),
            'approved_count' => (clone $leaveBase)->where('status', 'approved')->count(),
            'rejected_count' => (clone $leaveBase)->where('status', 'rejected')->count(),
            'cancelled_count' => (clone $leaveBase)->where('status', 'cancelled')->count(),
            'leave_days_total' => (int) (clone $leaveBase)->sum('total_days'),
        ];

        $overtimeBase = OvertimeRecord::query()
            ->whereIn('employee_id', $employeeIds)
            ->whereDate('overtime_date', '>=', $dateFrom)
            ->whereDate('overtime_date', '<=', $dateTo);

        $overtimeSummary = [
            'pending_count' => (clone $overtimeBase)->where('status', 'pending')->count(),
            'approved_count' => (clone $overtimeBase)->where('status', 'approved')->count(),
            'rejected_count' => (clone $overtimeBase)->where('status', 'rejected')->count(),
            'cancelled_count' => (clone $overtimeBase)->where('status', 'cancelled')->count(),
            'approved_hours_total' => round((float) (clone $overtimeBase)->where('status', 'approved')->sum('hours'), 2),
        ];

        $today = Carbon::today();
        $soon = Carbon::today()->addDays(30);

        $documentBase = EmployeeDocument::query()->whereIn('employee_id', $employeeIds)->where('status', 'active');

        $documentSummary = [
            'active_documents' => (clone $documentBase)->count(),
            'expired_documents' => (clone $documentBase)->whereDate('expiry_date', '<', $today)->count(),
            'expiring_soon_documents' => (clone $documentBase)->whereDate('expiry_date', '>=', $today)->whereDate('expiry_date', '<=', $soon)->count(),
        ];

        $employeeRows = (clone $employeeBase)
            ->orderBy('employee_id')
            ->limit(100)
            ->get(['employee_id', 'full_name', 'employment_status', 'department', 'position']);

        return Inertia::render('HrReports/Index', [
            'filters' => [
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
                'search' => $search,
            ],
            'generated_at' => now()->toDateTimeString(),
            'employee_summary' => $employeeSummary,
            'attendance_summary' => $attendanceSummary,
            'leave_summary' => $leaveSummary,
            'overtime_summary' => $overtimeSummary,
            'document_summary' => $documentSummary,
            'employee_rows' => $employeeRows,
        ]);
    }
}

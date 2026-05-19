<?php

namespace App\Http\Controllers;

use App\Models\AttendanceRecord;
use App\Models\OvertimeRecord;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AttendanceReportController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', 'in:present,incomplete,absent'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
        ]);

        $dateFrom = $filters['date_from'] ?? now()->startOfMonth()->toDateString();
        $dateTo = $filters['date_to'] ?? now()->toDateString();

        $records = AttendanceRecord::query()
            ->with('employee')
            ->whereBetween('attendance_date', [$dateFrom, $dateTo])
            ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->when($filters['search'] ?? null, function ($query, $search) {
                $term = '%' . str_replace(['%', '_'], ['\\%', '\\_'], $search) . '%';
                $query->whereHas('employee', fn ($employee) => $employee->where('employee_id', 'like', $term)->orWhere('full_name', 'like', $term));
            })
            ->orderBy('attendance_date')
            ->get();

        $employeeIds = $records->pluck('employee_id')->unique()->values();

        $overtimeByEmployeeDate = OvertimeRecord::query()
            ->whereIn('employee_id', $employeeIds)
            ->where('status', 'approved')
            ->whereBetween('overtime_date', [$dateFrom, $dateTo])
            ->get()
            ->groupBy(fn ($record) => $record->employee_id . '|' . $record->overtime_date->toDateString())
            ->map(fn ($items) => (float) $items->sum('hours'));

        $rows = $records->map(function (AttendanceRecord $record) use ($overtimeByEmployeeDate) {
            $key = $record->employee_id . '|' . $record->attendance_date->toDateString();

            return [
                'id' => $record->id,
                'employee_id' => $record->employee?->employee_id,
                'employee_name' => $record->employee?->full_name,
                'attendance_date' => $record->attendance_date->toDateString(),
                'check_in_at' => $record->check_in_at?->format('Y-m-d H:i:s'),
                'check_out_at' => $record->check_out_at?->format('Y-m-d H:i:s'),
                'work_hours' => (float) $record->work_hours,
                'status' => $record->status,
                'overtime_hours' => $overtimeByEmployeeDate->get($key, 0),
            ];
        });

        return Inertia::render('AttendanceReports/Index', [
            'rows' => $rows,
            'summary' => [
                'total_records' => $rows->count(),
                'present_count' => $rows->where('status', 'present')->count(),
                'incomplete_count' => $rows->where('status', 'incomplete')->count(),
                'absent_count' => $rows->where('status', 'absent')->count(),
                'total_work_hours' => round($rows->sum('work_hours'), 2),
                'total_overtime_hours' => round($rows->sum('overtime_hours'), 2),
            ],
            'filters' => [
                'search' => $filters['search'] ?? '',
                'status' => $filters['status'] ?? '',
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
            ],
            'statuses' => AttendanceRecord::STATUSES,
        ]);
    }
}

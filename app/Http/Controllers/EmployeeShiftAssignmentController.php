<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\EmployeeShiftAssignment;
use App\Models\WorkShift;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class EmployeeShiftAssignmentController extends Controller
{
    public function index(Request $request)
    {
        $assignments = EmployeeShiftAssignment::query()
            ->with(['employee', 'shift'])
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = '%' . str_replace(['%', '_'], ['\\%', '\\_'], $request->search) . '%';
                $query->whereHas('employee', fn ($employee) => $employee->where('employee_id', 'like', $search)->orWhere('full_name', 'like', $search));
            })
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->status))
            ->when($request->filled('work_shift_id'), fn ($query) => $query->where('work_shift_id', $request->work_shift_id))
            ->latest('effective_date')
            ->paginate(50)
            ->withQueryString();

        return Inertia::render('EmployeeShiftAssignments/Index', [
            'assignments' => $assignments,
            'shifts' => $this->shifts(),
            'statuses' => EmployeeShiftAssignment::STATUSES,
            'filters' => $request->only(['search', 'status', 'work_shift_id']),
        ]);
    }

    public function create()
    {
        return Inertia::render('EmployeeShiftAssignments/Create', $this->formData());
    }

    public function store(Request $request)
    {
        $data = $this->validateAssignment($request);

        $assignment = DB::transaction(function () use ($data) {
            if ($data['status'] === 'active') {
                EmployeeShiftAssignment::where('employee_id', $data['employee_id'])->where('status', 'active')->update(['status' => 'inactive', 'updated_by' => Auth::id()]);
            }

            $data['created_by'] = Auth::id();

            return EmployeeShiftAssignment::create($data);
        });

        return redirect()->route('employee-shift-assignments.show', $assignment)->with('success', 'Assignment shift dibuat.');
    }

    public function show(EmployeeShiftAssignment $employeeShiftAssignment)
    {
        $employeeShiftAssignment->load(['employee', 'shift', 'creator']);

        return Inertia::render('EmployeeShiftAssignments/Show', [
            'assignment' => $employeeShiftAssignment,
        ]);
    }

    public function edit(EmployeeShiftAssignment $employeeShiftAssignment)
    {
        return Inertia::render('EmployeeShiftAssignments/Edit', [
            ...$this->formData(),
            'assignment' => $employeeShiftAssignment,
        ]);
    }

    public function update(Request $request, EmployeeShiftAssignment $employeeShiftAssignment)
    {
        $data = $this->validateAssignment($request);

        DB::transaction(function () use ($data, $employeeShiftAssignment) {
            if ($data['status'] === 'active') {
                EmployeeShiftAssignment::where('employee_id', $data['employee_id'])
                    ->where('id', '!=', $employeeShiftAssignment->id)
                    ->where('status', 'active')
                    ->update(['status' => 'inactive', 'updated_by' => Auth::id()]);
            }

            $data['updated_by'] = Auth::id();
            $employeeShiftAssignment->update($data);
        });

        return redirect()->route('employee-shift-assignments.show', $employeeShiftAssignment)->with('success', 'Assignment shift diperbarui.');
    }

    public function destroy(EmployeeShiftAssignment $employeeShiftAssignment)
    {
        $employeeShiftAssignment->update(['status' => 'inactive', 'updated_by' => Auth::id()]);

        return redirect()->route('employee-shift-assignments.index')->with('success', 'Assignment shift dinonaktifkan.');
    }

    private function validateAssignment(Request $request): array
    {
        return $request->validate([
            'employee_id' => ['required', Rule::exists('employees', 'id')->where('employment_status', 'active')],
            'work_shift_id' => ['required', Rule::exists('work_shifts', 'id')->where('is_active', true)],
            'effective_date' => ['required', 'date'],
            'status' => ['required', Rule::in(EmployeeShiftAssignment::STATUSES)],
            'notes' => ['nullable', 'string'],
        ]);
    }

    private function formData(): array
    {
        return [
            'employees' => Employee::where('employment_status', 'active')->orderBy('employee_id')->get(['id', 'employee_id', 'full_name']),
            'shifts' => $this->shifts(),
            'statuses' => EmployeeShiftAssignment::STATUSES,
        ];
    }

    private function shifts()
    {
        return WorkShift::where('is_active', true)->orderBy('shift_code')->get(['id', 'shift_code', 'name']);
    }
}

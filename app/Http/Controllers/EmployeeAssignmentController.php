<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Employee;
use App\Models\EmployeeAssignment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class EmployeeAssignmentController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->input('search', ''));

        $assignments = EmployeeAssignment::with('employee', 'branch')
            ->when($search !== '', function ($q) use ($search) {
                $escaped = str_replace(['%', '_'], ['\\%', '\\_'], $search);
                $q->whereHas('employee', function ($sub) use ($escaped) {
                    $sub->where('employee_id', 'like', "%{$escaped}%")
                        ->orWhere('full_name', 'like', "%{$escaped}%");
                });
            })
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->input('status')))
            ->when($request->filled('branch_id'), fn ($q) => $q->where('branch_id', $request->input('branch_id')))
            ->latest('effective_date')
            ->paginate(50)
            ->withQueryString();

        return Inertia::render('EmployeeAssignments/Index', [
            'assignments' => $assignments,
            'branches' => $this->branches(),
            'statuses' => EmployeeAssignment::STATUSES,
            'filters' => [
                'search' => $search,
                'status' => $request->input('status', ''),
                'branch_id' => $request->input('branch_id', ''),
            ],
        ]);
    }

    public function create()
    {
        return Inertia::render('EmployeeAssignments/Create', [
            'employees' => $this->employees(),
            'branches' => $this->branches(),
            'statuses' => EmployeeAssignment::STATUSES,
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validateAssignment($request);
        $data['created_by'] = Auth::id();

        $assignment = DB::transaction(function () use ($data) {
            if ($data['status'] === 'active') {
                EmployeeAssignment::where('employee_id', $data['employee_id'])
                    ->where('status', 'active')
                    ->update(['status' => 'inactive', 'updated_by' => Auth::id()]);
            }

            return EmployeeAssignment::create($data);
        });

        return redirect()->route('employee-assignments.show', $assignment)->with('success', 'Employee assignment created.');
    }

    public function show(EmployeeAssignment $employeeAssignment)
    {
        $employeeAssignment->load('employee', 'branch', 'creator', 'updater');

        return Inertia::render('EmployeeAssignments/Show', [
            'assignment' => $employeeAssignment,
        ]);
    }

    public function edit(EmployeeAssignment $employeeAssignment)
    {
        return Inertia::render('EmployeeAssignments/Edit', [
            'assignment' => $employeeAssignment->load('employee', 'branch'),
            'employees' => $this->employees(),
            'branches' => $this->branches(),
            'statuses' => EmployeeAssignment::STATUSES,
        ]);
    }

    public function update(Request $request, EmployeeAssignment $employeeAssignment)
    {
        $data = $this->validateAssignment($request);
        $data['updated_by'] = Auth::id();

        DB::transaction(function () use ($employeeAssignment, $data) {
            if ($data['status'] === 'active') {
                EmployeeAssignment::where('employee_id', $data['employee_id'])
                    ->where('id', '!=', $employeeAssignment->id)
                    ->where('status', 'active')
                    ->update(['status' => 'inactive', 'updated_by' => Auth::id()]);
            }

            $employeeAssignment->update($data);
        });

        return redirect()->route('employee-assignments.show', $employeeAssignment)->with('success', 'Employee assignment updated.');
    }

    public function destroy(EmployeeAssignment $employeeAssignment)
    {
        $employeeAssignment->update([
            'status' => 'inactive',
            'updated_by' => Auth::id(),
        ]);

        return redirect()->route('employee-assignments.index')->with('success', 'Employee assignment deactivated.');
    }

    private function validateAssignment(Request $request): array
    {
        return $request->validate([
            'employee_id' => [
                'required',
                Rule::exists('employees', 'id')->where(fn ($q) => $q->where('employment_status', 'active')),
            ],
            'branch_id' => [
                'required',
                Rule::exists('branches', 'id')->where(fn ($q) => $q->where('is_active', true)),
            ],
            'department' => ['nullable', 'string', 'max:100'],
            'position' => ['nullable', 'string', 'max:100'],
            'effective_date' => ['required', 'date'],
            'status' => ['required', Rule::in(EmployeeAssignment::STATUSES)],
            'notes' => ['nullable', 'string'],
        ]);
    }

    private function employees()
    {
        return Employee::query()
            ->where('employment_status', 'active')
            ->orderBy('employee_id')
            ->get(['id', 'employee_id', 'full_name', 'department', 'position']);
    }

    private function branches()
    {
        return Branch::query()
            ->where('is_active', true)
            ->orderBy('code')
            ->get(['id', 'code', 'name']);
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class EmployeeController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->input('search', ''));

        $employees = Employee::query()
            ->when($search !== '', function ($q) use ($search) {
                $escaped = str_replace(['%', '_'], ['\\%', '\\_'], $search);
                $q->where(function ($sub) use ($escaped) {
                    $sub->where('employee_id', 'like', "%{$escaped}%")
                        ->orWhere('full_name', 'like', "%{$escaped}%")
                        ->orWhere('email', 'like', "%{$escaped}%")
                        ->orWhere('department', 'like', "%{$escaped}%")
                        ->orWhere('position', 'like', "%{$escaped}%");
                });
            })
            ->when($request->filled('employment_status'), fn ($q) => $q->where('employment_status', $request->input('employment_status')))
            ->orderBy('employee_id')
            ->paginate(50)
            ->withQueryString();

        return Inertia::render('Employees/Index', [
            'employees' => $employees,
            'filters' => [
                'search' => $search,
                'employment_status' => $request->input('employment_status', ''),
            ],
            'statuses' => Employee::STATUSES,
        ]);
    }

    public function create()
    {
        return Inertia::render('Employees/Create', [
            'statuses' => Employee::STATUSES,
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validateEmployee($request);
        $data['created_by'] = Auth::id();

        $employee = Employee::create($data);

        return redirect()->route('employees.show', $employee)->with('success', 'Employee created.');
    }

    public function show(Employee $employee)
    {
        $employee->load('creator', 'updater');

        return Inertia::render('Employees/Show', [
            'employee' => $employee,
        ]);
    }

    public function edit(Employee $employee)
    {
        return Inertia::render('Employees/Edit', [
            'employee' => $employee,
            'statuses' => Employee::STATUSES,
        ]);
    }

    public function update(Request $request, Employee $employee)
    {
        $data = $this->validateEmployee($request, $employee);
        $data['updated_by'] = Auth::id();

        $employee->update($data);

        return redirect()->route('employees.show', $employee)->with('success', 'Employee updated.');
    }

    public function destroy(Employee $employee)
    {
        $employee->update([
            'employment_status' => 'inactive',
            'updated_by' => Auth::id(),
        ]);

        return redirect()->route('employees.index')->with('success', 'Employee deactivated.');
    }

    private function validateEmployee(Request $request, ?Employee $employee = null): array
    {
        return $request->validate([
            'employee_id' => ['required', 'string', 'max:50', Rule::unique('employees', 'employee_id')->ignore($employee?->id)],
            'full_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('employees', 'email')->ignore($employee?->id)],
            'phone' => ['nullable', 'string', 'max:30'],
            'join_date' => ['required', 'date'],
            'employment_status' => ['required', Rule::in(Employee::STATUSES)],
            'department' => ['nullable', 'string', 'max:100'],
            'position' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string'],
        ]);
    }
}

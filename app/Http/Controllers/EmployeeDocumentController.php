<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\EmployeeDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class EmployeeDocumentController extends Controller
{
    public function index(Request $request)
    {
        $query = EmployeeDocument::with(['employee', 'creator', 'updater']);

        if ($request->filled('search')) {
            $search = $request->string('search')->value();
            $query->where(function ($q) use ($search) {
                $q->where('document_number', 'like', "%{$search}%")
                    ->orWhereHas('employee', function ($employeeQuery) use ($search) {
                        $employeeQuery->where('employee_id', 'like', "%{$search}%")
                            ->orWhere('full_name', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->filled('document_type')) {
            $query->where('document_type', $request->string('document_type')->value());
        }

        if ($request->filled('status')) {
            $query->where('status', $request->string('status')->value());
        }

        if ($request->filled('expiry_from')) {
            $query->whereDate('expiry_date', '>=', $request->string('expiry_from')->value());
        }

        if ($request->filled('expiry_to')) {
            $query->whereDate('expiry_date', '<=', $request->string('expiry_to')->value());
        }

        return Inertia::render('EmployeeDocuments/Index', [
            'documents' => $query->orderByDesc('id')->paginate(50)->withQueryString(),
            'filters' => $request->only(['search', 'document_type', 'status', 'expiry_from', 'expiry_to']),
            'statuses' => EmployeeDocument::STATUSES,
            'documentTypes' => $this->documentTypes(),
        ]);
    }

    public function create()
    {
        return Inertia::render('EmployeeDocuments/Create', [
            'employees' => $this->employees(),
            'documentTypes' => $this->documentTypes(),
            'statuses' => EmployeeDocument::STATUSES,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $this->validateDocument($request);

        $document = EmployeeDocument::create([
            ...$validated,
            'created_by' => Auth::id(),
        ]);

        return redirect()->route('employee-documents.show', $document)->with('success', 'Employee document created.');
    }

    public function show(EmployeeDocument $employeeDocument)
    {
        $employeeDocument->load(['employee', 'creator', 'updater']);

        return Inertia::render('EmployeeDocuments/Show', [
            'document' => $employeeDocument,
            'isExpired' => $employeeDocument->expiry_date?->isPast() ?? false,
        ]);
    }

    public function edit(EmployeeDocument $employeeDocument)
    {
        return Inertia::render('EmployeeDocuments/Edit', [
            'document' => $employeeDocument,
            'employees' => $this->employees(),
            'documentTypes' => $this->documentTypes(),
            'statuses' => EmployeeDocument::STATUSES,
        ]);
    }

    public function update(Request $request, EmployeeDocument $employeeDocument)
    {
        $validated = $this->validateDocument($request, $employeeDocument);

        $employeeDocument->update([
            ...$validated,
            'updated_by' => Auth::id(),
        ]);

        return redirect()->route('employee-documents.show', $employeeDocument)->with('success', 'Employee document updated.');
    }

    public function destroy(EmployeeDocument $employeeDocument)
    {
        $employeeDocument->update([
            'status' => 'inactive',
            'updated_by' => Auth::id(),
        ]);

        return redirect()->route('employee-documents.index')->with('success', 'Employee document deactivated.');
    }

    private function validateDocument(Request $request, ?EmployeeDocument $employeeDocument = null): array
    {
        return $request->validate([
            'employee_id' => ['required', 'exists:employees,id'],
            'document_type' => ['required', 'string', 'max:50'],
            'document_number' => [
                'required',
                'string',
                'max:100',
                Rule::unique('employee_documents', 'document_number')
                    ->where('employee_id', $request->input('employee_id'))
                    ->where('document_type', $request->input('document_type'))
                    ->ignore($employeeDocument?->id),
            ],
            'issue_date' => ['required', 'date'],
            'expiry_date' => ['nullable', 'date', 'after_or_equal:issue_date'],
            'status' => ['required', Rule::in(EmployeeDocument::STATUSES)],
            'notes' => ['nullable', 'string'],
        ]);
    }

    private function employees()
    {
        return Employee::query()
            ->where('employment_status', 'active')
            ->orderBy('employee_id')
            ->get(['id', 'employee_id', 'full_name']);
    }

    private function documentTypes(): array
    {
        return ['id_card', 'contract', 'certificate', 'tax', 'other'];
    }
}

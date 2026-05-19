<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\SalaryComponent;
use App\Services\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class SalaryComponentController extends Controller
{
    public function __construct(private AuditLogger $auditLogger)
    {
    }

    public function index(Request $request)
    {
        $components = SalaryComponent::query()
            ->with('account')
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = str_replace(['%', '_'], ['\\%', '\\_'], $request->search);
                $query->where(function ($sub) use ($search) {
                    $sub->where('code', 'like', "%{$search}%")
                        ->orWhere('name', 'like', "%{$search}%");
                });
            })
            ->when($request->filled('component_type'), fn ($q) => $q->where('component_type', $request->component_type))
            ->when($request->filled('is_active'), fn ($q) => $q->where('is_active', (bool) $request->boolean('is_active')))
            ->orderBy('component_type')
            ->orderBy('code')
            ->paginate(50)
            ->withQueryString();

        return Inertia::render('SalaryComponents/Index', [
            'components' => $components,
            'filters' => $request->only(['search', 'component_type', 'is_active']),
            'componentTypes' => SalaryComponent::COMPONENT_TYPES,
            'calculationMethods' => SalaryComponent::CALCULATION_METHODS,
        ]);
    }

    public function create()
    {
        return Inertia::render('SalaryComponents/Create', $this->formData());
    }

    public function store(Request $request)
    {
        $validated = $this->validateComponent($request);
        $component = SalaryComponent::create($validated + ['created_by' => Auth::id()]);

        $this->auditLogger->log(
            eventKey: 'salary_component.created',
            entityType: 'salary_component',
            entityId: $component->id,
            action: 'create',
            actorUserId: Auth::id(),
            oldValues: null,
            newValues: $component->toArray(),
            metadata: null,
            request: $request
        );

        return redirect()->route('salary-components.show', $component)->with('success', 'Salary component created.');
    }

    public function show(SalaryComponent $salaryComponent)
    {
        $salaryComponent->load(['account', 'creator', 'updater']);

        return Inertia::render('SalaryComponents/Show', [
            'component' => $salaryComponent,
        ]);
    }

    public function edit(SalaryComponent $salaryComponent)
    {
        return Inertia::render('SalaryComponents/Edit', $this->formData() + [
            'component' => $salaryComponent->load('account'),
        ]);
    }

    public function update(Request $request, SalaryComponent $salaryComponent)
    {
        $validated = $this->validateComponent($request, $salaryComponent);
        $oldValues = $salaryComponent->toArray();
        $salaryComponent->update($validated + ['updated_by' => Auth::id()]);

        $this->auditLogger->log(
            eventKey: 'salary_component.updated',
            entityType: 'salary_component',
            entityId: $salaryComponent->id,
            action: 'update',
            actorUserId: Auth::id(),
            oldValues: $oldValues,
            newValues: $salaryComponent->fresh()->toArray(),
            metadata: null,
            request: $request
        );

        return redirect()->route('salary-components.show', $salaryComponent)->with('success', 'Salary component updated.');
    }

    public function destroy(Request $request, SalaryComponent $salaryComponent)
    {
        $oldValues = $salaryComponent->toArray();
        $salaryComponent->delete();

        $this->auditLogger->log(
            eventKey: 'salary_component.deleted',
            entityType: 'salary_component',
            entityId: $salaryComponent->id,
            action: 'delete',
            actorUserId: Auth::id(),
            oldValues: $oldValues,
            newValues: null,
            metadata: ['code' => $oldValues['code'] ?? null],
            request: $request,
            isSensitive: true,
            sensitivityLevel: 'high',
            sensitivityReason: 'master_data_deletion'
        );

        return redirect()->route('salary-components.index')->with('success', 'Salary component deleted.');
    }

    private function validateComponent(Request $request, ?SalaryComponent $component = null): array
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:50', Rule::unique('salary_components', 'code')->ignore($component?->id)],
            'name' => ['required', 'string', 'max:255'],
            'component_type' => ['required', Rule::in(SalaryComponent::COMPONENT_TYPES)],
            'calculation_method' => ['required', Rule::in(SalaryComponent::CALCULATION_METHODS)],
            'default_amount' => ['nullable', 'numeric', 'min:0'],
            'default_percentage' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'is_taxable' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
            'account_id' => ['nullable', Rule::exists('accounts', 'id')->where('is_active', true)->where('is_header', false)],
            'description' => ['nullable', 'string'],
        ]);

        if (($validated['calculation_method'] ?? 'fixed') === 'fixed') {
            $validated['default_percentage'] = 0;
        }

        if (($validated['calculation_method'] ?? 'fixed') === 'percentage') {
            $validated['default_amount'] = 0;
        }

        return $validated;
    }

    private function formData(): array
    {
        return [
            'accounts' => Account::query()
                ->where('is_active', true)
                ->where('is_header', false)
                ->orderBy('code')
                ->get(['id', 'code', 'name', 'type', 'category']),
            'componentTypes' => SalaryComponent::COMPONENT_TYPES,
            'calculationMethods' => SalaryComponent::CALCULATION_METHODS,
        ];
    }
}

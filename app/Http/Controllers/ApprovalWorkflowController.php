<?php

namespace App\Http\Controllers;

use App\Models\ApprovalWorkflow;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class ApprovalWorkflowController extends Controller
{
    public function index(Request $request)
    {
        $workflows = ApprovalWorkflow::query()
            ->with(['creator:id,name', 'updater:id,name'])
            ->when($request->filled('entity_type'), fn ($query) => $query->where('entity_type', $request->entity_type))
            ->when($request->filled('is_active'), fn ($query) => $query->where('is_active', $request->boolean('is_active')))
            ->orderBy('entity_type')
            ->orderBy('min_amount')
            ->paginate(50)
            ->withQueryString();

        return Inertia::render('ApprovalWorkflows/Index', [
            'workflows' => $workflows,
            'filters' => $request->only(['entity_type', 'is_active']),
        ]);
    }

    public function create()
    {
        return Inertia::render('ApprovalWorkflows/Create');
    }

    public function store(Request $request)
    {
        $validated = $this->validateWorkflow($request);
        $this->ensureNoOverlap($validated);

        $workflow = ApprovalWorkflow::create($validated + ['created_by' => Auth::id()]);

        return redirect()->route('approval-workflows.show', $workflow)->with('success', 'Approval workflow created.');
    }

    public function show(ApprovalWorkflow $approvalWorkflow)
    {
        $approvalWorkflow->load(['creator:id,name', 'updater:id,name']);

        return Inertia::render('ApprovalWorkflows/Show', [
            'workflow' => $approvalWorkflow,
        ]);
    }

    public function edit(ApprovalWorkflow $approvalWorkflow)
    {
        return Inertia::render('ApprovalWorkflows/Edit', [
            'workflow' => $approvalWorkflow,
        ]);
    }

    public function update(Request $request, ApprovalWorkflow $approvalWorkflow)
    {
        $validated = $this->validateWorkflow($request, $approvalWorkflow);
        $this->ensureNoOverlap($validated, $approvalWorkflow->id);

        $approvalWorkflow->update($validated + ['updated_by' => Auth::id()]);

        return redirect()->route('approval-workflows.show', $approvalWorkflow)->with('success', 'Approval workflow updated.');
    }

    public function destroy(ApprovalWorkflow $approvalWorkflow)
    {
        $approvalWorkflow->update(['is_active' => false, 'updated_by' => Auth::id()]);

        return redirect()->route('approval-workflows.index')->with('success', 'Approval workflow deactivated.');
    }

    public function evaluate(Request $request)
    {
        $validated = $request->validate([
            'entity_type' => ['required', 'string', 'max:100'],
            'amount' => ['required', 'numeric', 'min:0'],
        ]);

        $workflow = ApprovalWorkflow::query()
            ->where('entity_type', $validated['entity_type'])
            ->where('is_active', true)
            ->where('min_amount', '<=', $validated['amount'])
            ->where(function ($query) use ($validated) {
                $query->whereNull('max_amount')
                    ->orWhere('max_amount', '>=', $validated['amount']);
            })
            ->orderByDesc('required_level')
            ->orderBy('min_amount')
            ->first();

        return response()->json([
            'matched' => (bool) $workflow,
            'workflow' => $workflow ? [
                'id' => $workflow->id,
                'workflow_key' => $workflow->workflow_key,
                'entity_type' => $workflow->entity_type,
                'min_amount' => (float) $workflow->min_amount,
                'max_amount' => $workflow->max_amount !== null ? (float) $workflow->max_amount : null,
                'required_level' => $workflow->required_level,
            ] : null,
        ]);
    }

    private function validateWorkflow(Request $request, ?ApprovalWorkflow $workflow = null): array
    {
        $validated = $request->validate([
            'workflow_key' => ['required', 'string', 'max:100', Rule::unique('approval_workflows', 'workflow_key')->ignore($workflow?->id)],
            'entity_type' => ['required', 'string', 'max:100'],
            'min_amount' => ['required', 'numeric', 'min:0'],
            'max_amount' => ['nullable', 'numeric', 'gte:min_amount'],
            'required_level' => ['required', 'integer', 'min:1', 'max:10'],
            'is_active' => ['nullable', 'boolean'],
            'description' => ['nullable', 'string'],
        ]);

        $validated['is_active'] = (bool) ($validated['is_active'] ?? false);

        return $validated;
    }

    private function ensureNoOverlap(array $validated, ?int $ignoreId = null): void
    {
        if (! ($validated['is_active'] ?? false)) {
            return;
        }

        $newMin = (float) $validated['min_amount'];
        $newMax = $validated['max_amount'] !== null ? (float) $validated['max_amount'] : INF;

        $conflict = ApprovalWorkflow::query()
            ->where('entity_type', $validated['entity_type'])
            ->where('is_active', true)
            ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
            ->get()
            ->contains(function (ApprovalWorkflow $existing) use ($newMin, $newMax) {
                $existingMin = (float) $existing->min_amount;
                $existingMax = $existing->max_amount !== null ? (float) $existing->max_amount : INF;

                return $newMin <= $existingMax && $newMax >= $existingMin;
            });

        if ($conflict) {
            abort(422, 'Active workflow range overlaps existing active workflow for this entity type.');
        }
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\DataRetentionPolicy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class DataRetentionPolicyController extends Controller
{
    private const ENTITIES = [
        'audit_log' => ['table' => 'audit_logs', 'date_column' => 'occurred_at', 'label' => 'Audit Logs'],
        'offline_sync_event' => ['table' => 'offline_sync_events', 'date_column' => 'created_at', 'label' => 'Offline Sync Events'],
        'attendance_record' => ['table' => 'attendance_records', 'date_column' => 'attendance_date', 'label' => 'Attendance Records'],
        'employee_document' => ['table' => 'employee_documents', 'date_column' => 'expiry_date', 'label' => 'Employee Documents'],
    ];

    public function index(Request $request)
    {
        $policies = DataRetentionPolicy::query()
            ->with(['creator:id,name', 'updater:id,name'])
            ->when($request->filled('entity_type'), fn ($query) => $query->where('entity_type', $request->entity_type))
            ->when($request->filled('action'), fn ($query) => $query->where('action', $request->action))
            ->when($request->filled('is_active'), fn ($query) => $query->where('is_active', $request->boolean('is_active')))
            ->orderBy('entity_type')
            ->orderBy('policy_key')
            ->paginate(50)
            ->withQueryString();

        return Inertia::render('DataRetentionPolicies/Index', [
            'policies' => $policies,
            'filters' => $request->only(['entity_type', 'action', 'is_active']),
            'entities' => self::ENTITIES,
            'actions' => DataRetentionPolicy::ACTIONS,
        ]);
    }

    public function create()
    {
        return Inertia::render('DataRetentionPolicies/Create', $this->formData());
    }

    public function store(Request $request)
    {
        $validated = $this->validatePolicy($request);
        $policy = DataRetentionPolicy::create($validated + ['created_by' => Auth::id()]);

        return redirect()->route('data-retention-policies.show', $policy)->with('success', 'Retention policy created.');
    }

    public function show(DataRetentionPolicy $dataRetentionPolicy)
    {
        $dataRetentionPolicy->load(['creator:id,name', 'updater:id,name']);

        return Inertia::render('DataRetentionPolicies/Show', [
            'policy' => $dataRetentionPolicy,
            'preview' => $this->preview($dataRetentionPolicy),
            'entities' => self::ENTITIES,
        ]);
    }

    public function edit(DataRetentionPolicy $dataRetentionPolicy)
    {
        return Inertia::render('DataRetentionPolicies/Edit', $this->formData() + [
            'policy' => $dataRetentionPolicy,
        ]);
    }

    public function update(Request $request, DataRetentionPolicy $dataRetentionPolicy)
    {
        $validated = $this->validatePolicy($request, $dataRetentionPolicy);
        $dataRetentionPolicy->update($validated + ['updated_by' => Auth::id()]);

        return redirect()->route('data-retention-policies.show', $dataRetentionPolicy)->with('success', 'Retention policy updated.');
    }

    public function destroy(DataRetentionPolicy $dataRetentionPolicy)
    {
        $dataRetentionPolicy->update(['is_active' => false, 'updated_by' => Auth::id()]);

        return redirect()->route('data-retention-policies.index')->with('success', 'Retention policy deactivated.');
    }

    private function validatePolicy(Request $request, ?DataRetentionPolicy $policy = null): array
    {
        return $request->validate([
            'policy_key' => ['required', 'string', 'max:100', Rule::unique('data_retention_policies', 'policy_key')->ignore($policy?->id)],
            'entity_type' => ['required', Rule::in(array_keys(self::ENTITIES))],
            'retention_days' => ['required', 'integer', 'min:1', 'max:36500'],
            'action' => ['required', Rule::in(DataRetentionPolicy::ACTIONS)],
            'is_active' => ['nullable', 'boolean'],
            'description' => ['nullable', 'string'],
        ]);
    }

    private function formData(): array
    {
        return [
            'entities' => self::ENTITIES,
            'actions' => DataRetentionPolicy::ACTIONS,
        ];
    }

    private function preview(DataRetentionPolicy $policy): array
    {
        $entity = self::ENTITIES[$policy->entity_type] ?? null;

        if (! $entity) {
            return ['cutoff_date' => null, 'candidate_count' => 0];
        }

        $cutoff = now()->subDays($policy->retention_days)->toDateString();
        $candidateCount = DB::table($entity['table'])
            ->whereDate($entity['date_column'], '<=', $cutoff)
            ->count();

        return [
            'cutoff_date' => $cutoff,
            'candidate_count' => $candidateCount,
        ];
    }
}

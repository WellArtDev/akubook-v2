<?php

namespace App\Services;

use App\Models\ApprovalWorkflow;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class WorkflowEnforcementService
{
    public function __construct(private readonly AuditLogger $auditLogger)
    {
    }

    public function evaluate(string $entityType, float $amount): array
    {
        $workflow = ApprovalWorkflow::query()
            ->where('entity_type', $entityType)
            ->where('is_active', true)
            ->where('min_amount', '<=', $amount)
            ->where(function ($query) use ($amount) {
                $query->whereNull('max_amount')
                    ->orWhere('max_amount', '>=', $amount);
            })
            ->orderByDesc('required_level')
            ->orderBy('min_amount')
            ->first();

        return [
            'enforced' => (bool) $workflow,
            'workflow' => $workflow,
            'reason' => $workflow ? $this->reason($workflow, $amount) : null,
        ];
    }

    public function enforce(Model $entity, string $entityType, float $amount, ?int $actorId, ?Request $request = null): array
    {
        $result = $this->evaluate($entityType, $amount);
        $workflow = $result['workflow'];

        $metadata = [
            'entity_type' => $entityType,
            'entity_id' => $entity->getKey(),
            'amount' => $amount,
            'enforced' => $result['enforced'],
            'workflow_id' => $workflow?->id,
            'workflow_key' => $workflow?->workflow_key,
            'required_level' => $workflow?->required_level,
            'reason' => $result['reason'],
        ];

        $this->auditLogger->log(
            'workflow.enforcement.evaluated',
            $entityType,
            (string) $entity->getKey(),
            $result['enforced'] ? 'enforced' : 'not_enforced',
            $actorId,
            null,
            ['status' => $result['enforced'] ? 'pending_approval' : 'normal'],
            $metadata,
            $request,
            true,
            $result['enforced'] ? 'high' : 'medium',
            'Workflow enforcement decision for sensitive transaction'
        );

        return $result;
    }

    private function reason(ApprovalWorkflow $workflow, float $amount): array
    {
        return [
            'type' => 'workflow_enforcement',
            'rule' => $workflow->workflow_key,
            'workflow_id' => $workflow->id,
            'required_level' => $workflow->required_level,
            'amount' => $amount,
            'message' => "Workflow {$workflow->workflow_key} requires approval level {$workflow->required_level}.",
        ];
    }
}

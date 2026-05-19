<?php

namespace Tests\Feature;

use App\Models\ApprovalWorkflow;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class ApprovalWorkflowTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->actingAs($this->user);
        $this->withoutVite();
    }

    public function test_approval_workflow_index_can_be_opened(): void
    {
        ApprovalWorkflow::factory()->create(['created_by' => $this->user->id]);

        $this->get(route('approval-workflows.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('ApprovalWorkflows/Index')
                ->has('workflows.data')
            );
    }

    public function test_user_can_create_approval_workflow(): void
    {
        $this->post(route('approval-workflows.store'), [
            'workflow_key' => 'PO-LOW',
            'entity_type' => 'purchase_order',
            'min_amount' => 0,
            'max_amount' => 10000000,
            'required_level' => 1,
            'is_active' => true,
            'description' => 'Low value PO approval',
        ])->assertRedirect();

        $this->assertDatabaseHas('approval_workflows', [
            'workflow_key' => 'PO-LOW',
            'entity_type' => 'purchase_order',
            'required_level' => 1,
            'created_by' => $this->user->id,
        ]);
    }

    public function test_active_workflow_ranges_cannot_overlap(): void
    {
        ApprovalWorkflow::factory()->create([
            'workflow_key' => 'PO-BASE',
            'entity_type' => 'purchase_order',
            'min_amount' => 0,
            'max_amount' => 10000000,
            'is_active' => true,
            'created_by' => $this->user->id,
        ]);

        $this->post(route('approval-workflows.store'), [
            'workflow_key' => 'PO-OVERLAP',
            'entity_type' => 'purchase_order',
            'min_amount' => 5000000,
            'max_amount' => 15000000,
            'required_level' => 2,
            'is_active' => true,
        ])->assertStatus(422);
    }

    public function test_evaluator_returns_matching_workflow(): void
    {
        ApprovalWorkflow::factory()->create([
            'workflow_key' => 'PO-HIGH',
            'entity_type' => 'purchase_order',
            'min_amount' => 10000000,
            'max_amount' => null,
            'required_level' => 3,
            'is_active' => true,
            'created_by' => $this->user->id,
        ]);

        $this->postJson(route('approval-workflows.evaluate'), [
            'entity_type' => 'purchase_order',
            'amount' => 25000000,
        ])->assertOk()
            ->assertJsonPath('matched', true)
            ->assertJsonPath('workflow.workflow_key', 'PO-HIGH')
            ->assertJsonPath('workflow.required_level', 3);
    }
}

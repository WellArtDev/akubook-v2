<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Employee;
use App\Models\EmployeeAssignment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmployeeAssignmentTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();

        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    }

    public function test_index_page_can_be_opened(): void
    {
        EmployeeAssignment::factory()->create();

        $this->get(route('employee-assignments.index'))->assertOk();
    }

    public function test_user_can_create_assignment(): void
    {
        $employee = Employee::factory()->create(['employment_status' => 'active']);
        $branch = Branch::factory()->create(['is_active' => true]);

        $response = $this->post(route('employee-assignments.store'), [
            'employee_id' => $employee->id,
            'branch_id' => $branch->id,
            'department' => 'Finance',
            'position' => 'Senior Analyst',
            'effective_date' => '2026-05-18',
            'status' => 'active',
            'notes' => 'Main office assignment',
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('employee_assignments', [
            'employee_id' => $employee->id,
            'branch_id' => $branch->id,
            'status' => 'active',
            'created_by' => $this->user->id,
        ]);
    }

    public function test_new_active_assignment_deactivates_previous_active_assignment(): void
    {
        $employee = Employee::factory()->create(['employment_status' => 'active']);
        $oldBranch = Branch::factory()->create(['is_active' => true]);
        $newBranch = Branch::factory()->create(['is_active' => true]);

        $oldAssignment = EmployeeAssignment::factory()->create([
            'employee_id' => $employee->id,
            'branch_id' => $oldBranch->id,
            'status' => 'active',
            'created_by' => $this->user->id,
        ]);

        $this->post(route('employee-assignments.store'), [
            'employee_id' => $employee->id,
            'branch_id' => $newBranch->id,
            'department' => 'Sales',
            'position' => 'Supervisor',
            'effective_date' => '2026-05-20',
            'status' => 'active',
        ])->assertRedirect();

        $this->assertDatabaseHas('employee_assignments', [
            'id' => $oldAssignment->id,
            'status' => 'inactive',
        ]);

        $this->assertEquals(1, EmployeeAssignment::where('employee_id', $employee->id)->where('status', 'active')->count());
    }

    public function test_assignment_can_be_deactivated(): void
    {
        $assignment = EmployeeAssignment::factory()->create([
            'status' => 'active',
            'created_by' => $this->user->id,
        ]);

        $this->delete(route('employee-assignments.destroy', $assignment))->assertRedirect(route('employee-assignments.index'));

        $this->assertDatabaseHas('employee_assignments', [
            'id' => $assignment->id,
            'status' => 'inactive',
            'updated_by' => $this->user->id,
        ]);
    }
}

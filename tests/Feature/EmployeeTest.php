<?php

namespace Tests\Feature;

use App\Models\Employee;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmployeeTest extends TestCase
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
        Employee::factory()->create();

        $this->get(route('employees.index'))->assertOk();
    }

    public function test_user_can_create_employee(): void
    {
        $response = $this->post(route('employees.store'), [
            'employee_id' => 'EMP-0001',
            'full_name' => 'John Doe',
            'email' => 'john@company.test',
            'phone' => '08123456789',
            'join_date' => '2026-05-18',
            'employment_status' => 'active',
            'department' => 'Finance',
            'position' => 'Analyst',
            'notes' => 'New joiner',
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('employees', [
            'employee_id' => 'EMP-0001',
            'full_name' => 'John Doe',
            'email' => 'john@company.test',
            'employment_status' => 'active',
            'created_by' => $this->user->id,
        ]);
    }

    public function test_employee_id_must_be_unique(): void
    {
        Employee::factory()->create(['employee_id' => 'EMP-0001', 'email' => 'a@company.test']);

        $response = $this->post(route('employees.store'), [
            'employee_id' => 'EMP-0001',
            'full_name' => 'Jane Doe',
            'email' => 'jane@company.test',
            'join_date' => '2026-05-18',
            'employment_status' => 'active',
        ]);

        $response->assertSessionHasErrors('employee_id');
    }

    public function test_employee_can_be_filtered_by_status(): void
    {
        Employee::factory()->create(['employment_status' => 'active']);
        Employee::factory()->create(['employment_status' => 'inactive']);

        $this->get(route('employees.index', ['employment_status' => 'inactive']))->assertOk();
    }

    public function test_employee_can_be_deactivated_from_destroy(): void
    {
        $employee = Employee::factory()->create(['employment_status' => 'active']);

        $this->delete(route('employees.destroy', $employee))->assertRedirect(route('employees.index'));

        $this->assertDatabaseHas('employees', [
            'id' => $employee->id,
            'employment_status' => 'inactive',
            'updated_by' => $this->user->id,
        ]);
    }
}

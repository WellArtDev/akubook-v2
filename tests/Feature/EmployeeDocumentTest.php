<?php

namespace Tests\Feature;

use App\Models\Employee;
use App\Models\EmployeeDocument;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmployeeDocumentTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Employee $employee;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();

        $this->user = User::factory()->create();
        $this->employee = Employee::factory()->create([
            'employment_status' => 'active',
            'created_by' => $this->user->id,
        ]);
    }

    public function test_index_page_can_be_opened(): void
    {
        EmployeeDocument::factory()->create([
            'employee_id' => $this->employee->id,
            'created_by' => $this->user->id,
        ]);

        $this->actingAs($this->user)->get(route('employee-documents.index'))->assertOk();
    }

    public function test_user_can_create_employee_document(): void
    {
        $response = $this->actingAs($this->user)->post(route('employee-documents.store'), [
            'employee_id' => $this->employee->id,
            'document_type' => 'contract',
            'document_number' => 'CON-2026-001',
            'issue_date' => '2026-05-01',
            'expiry_date' => '2027-05-01',
            'status' => 'active',
            'notes' => 'Initial contract',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('employee_documents', [
            'employee_id' => $this->employee->id,
            'document_type' => 'contract',
            'document_number' => 'CON-2026-001',
            'status' => 'active',
            'created_by' => $this->user->id,
        ]);
    }

    public function test_document_unique_per_employee_type_number(): void
    {
        EmployeeDocument::factory()->create([
            'employee_id' => $this->employee->id,
            'document_type' => 'id_card',
            'document_number' => 'ID-001',
            'created_by' => $this->user->id,
        ]);

        $this->actingAs($this->user)->post(route('employee-documents.store'), [
            'employee_id' => $this->employee->id,
            'document_type' => 'id_card',
            'document_number' => 'ID-001',
            'issue_date' => '2026-05-01',
            'status' => 'active',
        ])->assertSessionHasErrors(['document_number']);
    }

    public function test_document_can_be_deactivated(): void
    {
        $document = EmployeeDocument::factory()->create([
            'employee_id' => $this->employee->id,
            'status' => 'active',
            'created_by' => $this->user->id,
        ]);

        $this->actingAs($this->user)->delete(route('employee-documents.destroy', $document))->assertRedirect(route('employee-documents.index'));

        $document->refresh();
        $this->assertSame('inactive', $document->status);
        $this->assertSame($this->user->id, $document->updated_by);
    }
}

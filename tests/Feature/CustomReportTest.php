<?php

namespace Tests\Feature;

use App\Models\CustomReport;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomReportTest extends TestCase
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
        CustomReport::factory()->create();

        $this->get(route('custom-reports.index'))->assertOk();
    }

    public function test_user_can_create_custom_report(): void
    {
        $response = $this->post(route('custom-reports.store'), [
            'code' => 'EMP-ACTIVE',
            'name' => 'Active Employee Report',
            'source_key' => 'employees',
            'selected_columns' => ['employee_id', 'full_name', 'employment_status'],
            'default_filters' => ['employment_status' => 'active'],
            'is_active' => true,
            'description' => 'Employee status report',
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('custom_reports', [
            'code' => 'EMP-ACTIVE',
            'source_key' => 'employees',
            'created_by' => $this->user->id,
        ]);
    }

    public function test_custom_report_code_must_be_unique(): void
    {
        CustomReport::factory()->create(['code' => 'DUP-REPORT']);

        $this->post(route('custom-reports.store'), [
            'code' => 'DUP-REPORT',
            'name' => 'Duplicate Report',
            'source_key' => 'employees',
            'selected_columns' => ['employee_id'],
            'is_active' => true,
        ])->assertSessionHasErrors('code');
    }

    public function test_custom_report_preview_uses_selected_columns_and_filters(): void
    {
        Employee::factory()->create([
            'employee_id' => 'EMP-RPT-001',
            'full_name' => 'Alpha Report',
            'employment_status' => 'active',
        ]);
        Employee::factory()->create([
            'employee_id' => 'EMP-RPT-002',
            'full_name' => 'Beta Report',
            'employment_status' => 'inactive',
        ]);

        $report = CustomReport::factory()->create([
            'source_key' => 'employees',
            'selected_columns' => ['employee_id', 'full_name', 'employment_status'],
            'default_filters' => ['employment_status' => 'active'],
            'created_by' => $this->user->id,
        ]);

        $this->get(route('custom-reports.show', ['custom_report' => $report, 'search' => 'Alpha']))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('CustomReports/Show')
                ->where('preview.columns.0', 'employee_id')
                ->where('preview.rows.0.employee_id', 'EMP-RPT-001')
                ->has('preview.rows', 1)
            );
    }
}

<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\SalaryComponent;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SalaryComponentTest extends TestCase
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
        SalaryComponent::factory()->create();

        $this->get(route('salary-components.index'))->assertOk();
    }

    public function test_user_can_create_salary_component(): void
    {
        $response = $this->post(route('salary-components.store'), [
            'code' => 'SC-BASIC',
            'name' => 'Basic Salary',
            'component_type' => 'earning',
            'calculation_method' => 'fixed',
            'default_amount' => 5000000,
            'default_percentage' => 0,
            'is_taxable' => true,
            'is_active' => true,
            'description' => 'Monthly base salary',
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('salary_components', [
            'code' => 'SC-BASIC',
            'component_type' => 'earning',
            'calculation_method' => 'fixed',
            'created_by' => $this->user->id,
        ]);
    }

    public function test_code_must_be_unique(): void
    {
        SalaryComponent::factory()->create(['code' => 'SC-UNIQ']);

        $response = $this->post(route('salary-components.store'), [
            'code' => 'SC-UNIQ',
            'name' => 'Transport',
            'component_type' => 'earning',
            'calculation_method' => 'fixed',
            'default_amount' => 200000,
            'default_percentage' => 0,
            'is_taxable' => false,
            'is_active' => true,
        ]);

        $response->assertSessionHasErrors('code');
    }

    public function test_percentage_component_stores_amount_as_zero(): void
    {
        $response = $this->post(route('salary-components.store'), [
            'code' => 'SC-PCT',
            'name' => 'Tax Deduction',
            'component_type' => 'deduction',
            'calculation_method' => 'percentage',
            'default_amount' => 900000,
            'default_percentage' => 5,
            'is_taxable' => false,
            'is_active' => true,
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('salary_components', [
            'code' => 'SC-PCT',
            'default_amount' => 0,
            'default_percentage' => 5,
        ]);
    }
}

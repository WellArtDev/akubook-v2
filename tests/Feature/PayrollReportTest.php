<?php

namespace Tests\Feature;

use App\Models\Employee;
use App\Models\PayrollRun;
use App\Models\PayrollRunLine;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class PayrollReportTest extends TestCase
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

    public function test_payroll_report_page_can_be_opened(): void
    {
        $this->get(route('payroll-reports.index'))->assertOk();
    }

    public function test_payroll_report_summarizes_period_totals(): void
    {
        $run = PayrollRun::factory()->create([
            'period' => '2026-05',
            'status' => 'calculated',
        ]);

        $employeeA = Employee::factory()->create(['employee_id' => 'EMP-A', 'full_name' => 'Alpha']);
        $employeeB = Employee::factory()->create(['employee_id' => 'EMP-B', 'full_name' => 'Beta']);

        PayrollRunLine::factory()->create([
            'payroll_run_id' => $run->id,
            'employee_id' => $employeeA->id,
            'earning_total' => 5000000,
            'deduction_total' => 500000,
            'gross_pay' => 5000000,
            'net_pay' => 4500000,
            'pph21_amount' => 250000,
            'net_pay_after_pph21' => 4250000,
            'status' => 'calculated',
        ]);

        PayrollRunLine::factory()->create([
            'payroll_run_id' => $run->id,
            'employee_id' => $employeeB->id,
            'earning_total' => 4000000,
            'deduction_total' => 250000,
            'gross_pay' => 4000000,
            'net_pay' => 3750000,
            'pph21_amount' => 200000,
            'net_pay_after_pph21' => 3550000,
            'status' => 'calculated',
        ]);

        $this->get(route('payroll-reports.index', ['period' => '2026-05']))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('PayrollReports/Index')
                ->where('summary.employee_count', 2)
                ->where('summary.total_earnings', 9000000)
                ->where('summary.total_deductions', 750000)
                ->where('summary.total_pph21', 450000)
                ->where('summary.total_net_pay_after_pph21', 7800000)
                ->has('rows', 2)
            );
    }

    public function test_payroll_report_can_filter_employee_search_and_status(): void
    {
        $run = PayrollRun::factory()->create([
            'period' => '2026-06',
            'status' => 'calculated',
        ]);

        $alpha = Employee::factory()->create(['employee_id' => 'EMP-ALPHA', 'full_name' => 'Alpha Employee']);
        $beta = Employee::factory()->create(['employee_id' => 'EMP-BETA', 'full_name' => 'Beta Employee']);

        PayrollRunLine::factory()->create([
            'payroll_run_id' => $run->id,
            'employee_id' => $alpha->id,
            'net_pay_after_pph21' => 1000000,
            'status' => 'calculated',
        ]);

        PayrollRunLine::factory()->create([
            'payroll_run_id' => $run->id,
            'employee_id' => $beta->id,
            'net_pay_after_pph21' => 2000000,
            'status' => 'draft',
        ]);

        $this->get(route('payroll-reports.index', ['period' => '2026-06', 'search' => 'alpha', 'status' => 'calculated']))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('PayrollReports/Index')
                ->where('summary.employee_count', 1)
                ->where('rows.0.employee_id', 'EMP-ALPHA')
            );
    }
}

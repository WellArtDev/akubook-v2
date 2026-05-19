<?php

namespace Tests\Feature;

use App\Models\AttendanceRecord;
use App\Models\Employee;
use App\Models\OvertimeRecord;
use App\Models\PayrollRunLine;
use App\Models\SalaryComponent;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PayrollRunTest extends TestCase
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

    public function test_payroll_run_index_can_be_opened(): void
    {
        $this->get(route('payroll-runs.index'))->assertOk();
    }

    public function test_payroll_run_calculates_fixed_and_percentage_components(): void
    {
        Employee::factory()->count(2)->create(['employment_status' => 'active']);

        SalaryComponent::factory()->create([
            'component_type' => 'earning',
            'calculation_method' => 'fixed',
            'default_amount' => 5000000,
            'default_percentage' => 0,
        ]);

        SalaryComponent::factory()->create([
            'component_type' => 'deduction',
            'calculation_method' => 'percentage',
            'default_amount' => 0,
            'default_percentage' => 10,
        ]);

        $this->get(route('payroll-runs.index', ['period' => '2026-05', 'run' => 1]))->assertOk();

        $this->assertDatabaseHas('payroll_runs', [
            'period' => '2026-05',
            'status' => 'calculated',
            'total_gross_pay' => 10000000,
            'total_deductions' => 1000000,
            'total_net_pay' => 9000000,
        ]);

        $this->assertEquals(2, \App\Models\PayrollRunLine::count());
    }

    public function test_payroll_run_integrates_attendance_and_overtime(): void
    {
        $employee = Employee::factory()->create(['employment_status' => 'active']);

        SalaryComponent::factory()->create([
            'code' => 'BASIC',
            'component_type' => 'earning',
            'calculation_method' => 'fixed',
            'default_amount' => 5000000,
            'default_percentage' => 0,
        ]);

        SalaryComponent::factory()->create([
            'code' => 'OVERTIME',
            'component_type' => 'earning',
            'calculation_method' => 'fixed',
            'default_amount' => 50000,
            'default_percentage' => 0,
        ]);

        SalaryComponent::factory()->create([
            'code' => 'ABSENCE',
            'component_type' => 'deduction',
            'calculation_method' => 'fixed',
            'default_amount' => 100000,
            'default_percentage' => 0,
        ]);

        AttendanceRecord::factory()->create([
            'employee_id' => $employee->id,
            'attendance_date' => '2026-05-01',
            'work_hours' => 8,
            'status' => 'present',
        ]);

        AttendanceRecord::factory()->create([
            'employee_id' => $employee->id,
            'attendance_date' => '2026-05-02',
            'work_hours' => 0,
            'status' => 'incomplete',
        ]);

        OvertimeRecord::factory()->create([
            'employee_id' => $employee->id,
            'attendance_record_id' => null,
            'overtime_date' => '2026-05-01',
            'hours' => 2.5,
            'status' => 'approved',
        ]);

        OvertimeRecord::factory()->create([
            'employee_id' => $employee->id,
            'attendance_record_id' => null,
            'overtime_date' => '2026-05-03',
            'hours' => 4,
            'status' => 'pending',
        ]);

        $this->get(route('payroll-runs.index', ['period' => '2026-05', 'run' => 1]))->assertOk();

        $line = PayrollRunLine::where('employee_id', $employee->id)->firstOrFail();

        $this->assertSame(1, $line->present_days);
        $this->assertSame(1, $line->incomplete_days);
        $this->assertSame('8.00', $line->attendance_work_hours);
        $this->assertSame('2.50', $line->approved_overtime_hours);
        $this->assertSame('5175000.00', $line->earning_total);
        $this->assertSame('200000.00', $line->deduction_total);
        $this->assertSame('4975000.00', $line->net_pay);
    }

    public function test_payroll_run_calculates_pph21_progressive_tax(): void
    {
        $employee = Employee::factory()->create(['employment_status' => 'active']);

        SalaryComponent::factory()->create([
            'code' => 'BASIC',
            'component_type' => 'earning',
            'calculation_method' => 'fixed',
            'default_amount' => 10000000,
            'default_percentage' => 0,
        ]);

        $this->get(route('payroll-runs.index', ['period' => '2026-07', 'run' => 1]))->assertOk();

        $line = PayrollRunLine::where('employee_id', $employee->id)->firstOrFail();

        $this->assertSame('10000000.00', $line->pph21_taxable_income);
        $this->assertSame('750000.00', $line->pph21_amount);
        $this->assertSame('9250000.00', $line->net_pay_after_pph21);
        $this->assertContains('PPH21', collect($line->component_snapshot)->pluck('code')->all());
    }

    public function test_payroll_rerun_is_idempotent_for_same_period(): void
    {
        Employee::factory()->create(['employment_status' => 'active']);

        SalaryComponent::factory()->create([
            'component_type' => 'earning',
            'calculation_method' => 'fixed',
            'default_amount' => 5000000,
            'default_percentage' => 0,
        ]);

        $this->get(route('payroll-runs.index', ['period' => '2026-06', 'run' => 1]))->assertOk();
        $this->get(route('payroll-runs.index', ['period' => '2026-06', 'run' => 1]))->assertOk();

        $this->assertEquals(1, \App\Models\PayrollRun::where('period', '2026-06')->count());
        $this->assertEquals(1, PayrollRunLine::count());
    }
}

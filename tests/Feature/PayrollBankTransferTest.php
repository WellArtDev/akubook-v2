<?php

namespace Tests\Feature;

use App\Models\Employee;
use App\Models\PayrollBankTransfer;
use App\Models\PayrollRun;
use App\Models\PayrollRunLine;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PayrollBankTransferTest extends TestCase
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
        PayrollBankTransfer::factory()->create(['created_by' => $this->user->id]);

        $this->get(route('payroll-bank-transfers.index'))->assertOk();
    }

    public function test_user_can_generate_bank_transfer_file_from_payroll_run(): void
    {
        $run = PayrollRun::factory()->create(['period' => '2026-05', 'status' => 'calculated']);
        $employee = Employee::factory()->create([
            'employee_id' => 'EMP-BANK-1',
            'full_name' => 'Bank Employee',
            'bank_name' => 'BCA',
            'bank_account_number' => '1234567890',
            'bank_account_holder' => 'Bank Employee',
        ]);
        PayrollRunLine::factory()->create([
            'payroll_run_id' => $run->id,
            'employee_id' => $employee->id,
            'net_pay' => 5000000,
            'net_pay_after_pph21' => 4750000,
            'status' => 'calculated',
        ]);

        $this->post(route('payroll-bank-transfers.store'), ['period' => '2026-05'])->assertRedirect();

        $this->assertDatabaseHas('payroll_bank_transfers', [
            'period' => '2026-05',
            'row_count' => 1,
            'success_count' => 1,
            'failed_count' => 0,
            'total_amount' => 4750000,
            'created_by' => $this->user->id,
        ]);

        $this->assertDatabaseHas('payroll_bank_transfer_lines', [
            'employee_id' => $employee->id,
            'employee_code' => 'EMP-BANK-1',
            'status' => 'success',
            'amount' => 4750000,
        ]);
    }

    public function test_employee_without_bank_data_is_marked_failed(): void
    {
        $run = PayrollRun::factory()->create(['period' => '2026-06', 'status' => 'calculated']);
        $employee = Employee::factory()->create(['employee_id' => 'EMP-NOBANK']);
        PayrollRunLine::factory()->create([
            'payroll_run_id' => $run->id,
            'employee_id' => $employee->id,
            'net_pay_after_pph21' => 3000000,
            'status' => 'calculated',
        ]);

        $this->post(route('payroll-bank-transfers.store'), ['period' => '2026-06'])->assertRedirect();

        $this->assertDatabaseHas('payroll_bank_transfers', [
            'period' => '2026-06',
            'success_count' => 0,
            'failed_count' => 1,
            'total_amount' => 0,
        ]);

        $this->assertDatabaseHas('payroll_bank_transfer_lines', [
            'employee_id' => $employee->id,
            'status' => 'failed',
            'failure_reason' => 'Employee bank data incomplete',
        ]);
    }

    public function test_download_returns_csv_content(): void
    {
        $transfer = PayrollBankTransfer::factory()->create([
            'csv_content' => "employee_code,amount\nEMP-1,1000",
            'created_by' => $this->user->id,
        ]);

        $response = $this->get(route('payroll-bank-transfers.download', $transfer));

        $response->assertOk();
        $this->assertStringStartsWith('text/csv', $response->headers->get('Content-Type'));
        $this->assertStringContainsString('EMP-1', $response->getContent());
    }
}

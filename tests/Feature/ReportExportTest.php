<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\CustomReport;
use App\Models\Employee;
use App\Models\FiscalPeriod;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportExportTest extends TestCase
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

    public function test_custom_report_can_be_exported_to_csv(): void
    {
        Employee::factory()->create([
            'employee_id' => 'EMP-CSV-001',
            'full_name' => 'CSV Alpha',
            'employment_status' => 'active',
        ]);
        Employee::factory()->create([
            'employee_id' => 'EMP-CSV-002',
            'full_name' => 'CSV Beta',
            'employment_status' => 'inactive',
        ]);

        $report = CustomReport::factory()->create([
            'source_key' => 'employees',
            'selected_columns' => ['employee_id', 'full_name', 'employment_status'],
            'default_filters' => ['employment_status' => 'active'],
        ]);

        $response = $this->get(route('report-exports.custom-report', ['custom_report' => $report, 'search' => 'Alpha']));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
        $this->assertStringContainsString('employee_id,full_name,employment_status', $response->getContent());
        $this->assertStringContainsString('EMP-CSV-001', $response->getContent());
        $this->assertStringNotContainsString('EMP-CSV-002', $response->getContent());
    }

    public function test_financial_report_can_be_exported_to_csv(): void
    {
        $period = FiscalPeriod::create([
            'name' => 'May 2026',
            'start_date' => '2026-05-01',
            'end_date' => '2026-05-31',
            'status' => 'open',
            'is_current' => true,
        ]);

        $cash = $this->account('1-1000', 'Cash', 'asset', 'current_asset');
        $revenue = $this->account('4-1000', 'Revenue', 'revenue', 'operating_revenue');

        $journal = JournalEntry::create([
            'journal_number' => 'JV-CSV-001',
            'journal_date' => '2026-05-10',
            'fiscal_period_id' => $period->id,
            'type' => 'manual',
            'description' => 'CSV export journal',
            'total_debit' => 100000,
            'total_credit' => 100000,
            'status' => 'posted',
            'created_by' => $this->user->id,
            'posted_by' => $this->user->id,
            'posted_at' => now(),
        ]);

        JournalEntryLine::create([
            'journal_entry_id' => $journal->id,
            'account_id' => $cash->id,
            'line_number' => 1,
            'description' => 'Debit cash',
            'debit' => 100000,
            'credit' => 0,
        ]);
        JournalEntryLine::create([
            'journal_entry_id' => $journal->id,
            'account_id' => $revenue->id,
            'line_number' => 2,
            'description' => 'Credit revenue',
            'debit' => 0,
            'credit' => 100000,
        ]);

        $response = $this->get(route('report-exports.financial', [
            'date_from' => '2026-05-01',
            'date_to' => '2026-05-31',
        ]));

        $response->assertOk();
        $this->assertStringContainsString('account_code,account_name,type,debit,credit', $response->getContent());
        $this->assertStringContainsString('1-1000,Cash,asset,100000,0', $response->getContent());
        $this->assertStringContainsString('4-1000,Revenue,revenue,0,100000', $response->getContent());
    }

    private function account(string $code, string $name, string $type, string $category): Account
    {
        return Account::create([
            'code' => $code,
            'name' => $name,
            'type' => $type,
            'category' => $category,
            'level' => 1,
            'is_header' => false,
            'is_active' => true,
            'balance' => 0,
        ]);
    }
}

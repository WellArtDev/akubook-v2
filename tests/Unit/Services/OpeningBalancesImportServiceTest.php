<?php

namespace Tests\Unit\Services;

use App\Models\Account;
use App\Models\FiscalPeriod;
use App\Models\JournalEntry;
use App\Services\OpeningBalancesImportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OpeningBalancesImportServiceTest extends TestCase
{
    use RefreshDatabase;

    private FiscalPeriod $period;

    private Account $cashAccount;

    private Account $capitalAccount;

    protected function setUp(): void
    {
        parent::setUp();

        $this->period = FiscalPeriod::create([
            'name' => 'January 2026',
            'start_date' => '2026-01-01',
            'end_date' => '2026-01-31',
            'status' => 'open',
            'is_current' => true,
        ]);

        $this->cashAccount = Account::create([
            'code' => '1-1000',
            'name' => 'Kas',
            'type' => 'asset',
            'category' => 'current_asset',
            'level' => 2,
            'is_header' => false,
            'is_active' => true,
            'balance' => 0,
        ]);

        $this->capitalAccount = Account::create([
            'code' => '3-1000',
            'name' => 'Modal',
            'type' => 'equity',
            'category' => 'equity',
            'level' => 2,
            'is_header' => false,
            'is_active' => true,
            'balance' => 0,
        ]);
    }

    public function test_preview_balanced_opening_balances_successfully(): void
    {
        $service = new OpeningBalancesImportService();

        $result = $service->preview($this->payload());

        $this->assertFalse($result['executed']);
        $this->assertSame(2, $result['total']);
        $this->assertSame(2, $result['valid']);
        $this->assertSame(1000.0, $result['total_debit']);
        $this->assertSame(1000.0, $result['total_credit']);
        $this->assertSame(0.0, $result['difference']);
    }

    public function test_import_balanced_rows_creates_posted_journal_entry_and_lines(): void
    {
        $service = new OpeningBalancesImportService();

        $result = $service->import($this->payload());

        $this->assertTrue($result['executed']);
        $this->assertSame(2, $result['imported']);
        $this->assertDatabaseHas('journal_entries', [
            'id' => $result['journal_entry_id'],
            'type' => 'manual',
            'reference_type' => 'opening_balance',
            'status' => 'posted',
            'total_debit' => 1000,
            'total_credit' => 1000,
        ]);
        $this->assertDatabaseHas('journal_entry_lines', [
            'account_id' => $this->cashAccount->id,
            'debit' => 1000,
            'credit' => 0,
        ]);
        $this->assertDatabaseHas('journal_entry_lines', [
            'account_id' => $this->capitalAccount->id,
            'debit' => 0,
            'credit' => 1000,
        ]);
    }

    public function test_rejects_unbalanced_payload_without_writing_journal(): void
    {
        $service = new OpeningBalancesImportService();
        $payload = $this->payload();
        $payload['opening_balances'][1]['credit'] = 900;

        $result = $service->import($payload);

        $this->assertFalse($result['executed']);
        $this->assertTrue($result['rejected']);
        $this->assertSame(100.0, $result['difference']);
        $this->assertSame(0, JournalEntry::count());
    }

    public function test_invalid_account_is_rejected_without_writing_journal(): void
    {
        $service = new OpeningBalancesImportService();
        $payload = $this->payload();
        $payload['opening_balances'][0]['account_code'] = '9-9999';

        $result = $service->import($payload);

        $this->assertFalse($result['executed']);
        $this->assertTrue($result['rejected']);
        $this->assertSame(1, $result['skipped']);
        $this->assertSame(0, JournalEntry::count());
    }

    private function payload(): array
    {
        return [
            'fiscal_period_id' => $this->period->id,
            'balance_date' => '2026-01-01',
            'opening_balances' => [
                ['account_code' => '1-1000', 'debit' => 1000, 'credit' => 0],
                ['account_code' => '3-1000', 'debit' => 0, 'credit' => 1000],
            ],
        ];
    }
}

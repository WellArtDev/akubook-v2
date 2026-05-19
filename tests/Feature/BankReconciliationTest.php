<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\BankAccount;
use App\Models\BankReconciliation;
use App\Models\BankReconciliationLine;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BankReconciliationTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private BankAccount $bankAccount;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        $this->user = User::factory()->create();
        $this->actingAs($this->user);

        $account = Account::create([
            'code' => '1-1100',
            'name' => 'Bank BCA',
            'type' => 'asset',
            'category' => 'current_asset',
            'level' => 1,
            'is_header' => false,
            'is_active' => true,
            'balance' => 0,
        ]);

        $this->bankAccount = BankAccount::factory()->create([
            'account_id' => $account->id,
            'opening_balance' => 100000,
            'created_by' => $this->user->id,
        ]);
    }

    public function test_user_can_create_bank_reconciliation(): void
    {
        $response = $this->post(route('bank-reconciliations.store'), $this->payload());

        $response->assertRedirect();
        $this->assertDatabaseHas('bank_reconciliations', [
            'bank_account_id' => $this->bankAccount->id,
            'statement_opening_balance' => 100000,
            'statement_closing_balance' => 125000,
            'system_balance' => 100000,
            'difference' => 25000,
            'status' => 'draft',
            'created_by' => $this->user->id,
        ]);
        $this->assertDatabaseCount('bank_reconciliation_lines', 2);
    }

    public function test_statement_line_can_be_matched_and_unmatched(): void
    {
        $reconciliation = BankReconciliation::factory()->create([
            'bank_account_id' => $this->bankAccount->id,
            'statement_opening_balance' => 100000,
            'statement_closing_balance' => 110000,
            'created_by' => $this->user->id,
        ]);
        $line = BankReconciliationLine::factory()->create([
            'bank_reconciliation_id' => $reconciliation->id,
            'debit' => 10000,
            'credit' => 0,
        ]);

        $this->post(route('bank-reconciliation-lines.match', $line), [
            'matched_reference_type' => 'manual',
            'matched_reference_id' => 123,
        ])->assertRedirect();

        $this->assertDatabaseHas('bank_reconciliation_lines', [
            'id' => $line->id,
            'is_matched' => true,
            'matched_reference_type' => 'manual',
            'matched_reference_id' => 123,
            'matched_by' => $this->user->id,
        ]);
        $this->assertDatabaseHas('bank_reconciliations', [
            'id' => $reconciliation->id,
            'system_balance' => 110000,
            'difference' => 0,
        ]);

        $this->post(route('bank-reconciliation-lines.unmatch', $line))->assertRedirect();
        $this->assertDatabaseHas('bank_reconciliation_lines', [
            'id' => $line->id,
            'is_matched' => false,
            'matched_reference_type' => null,
        ]);
    }

    public function test_reconciliation_can_be_marked_reconciled(): void
    {
        $reconciliation = BankReconciliation::factory()->create([
            'bank_account_id' => $this->bankAccount->id,
            'statement_opening_balance' => 100000,
            'statement_closing_balance' => 110000,
            'created_by' => $this->user->id,
        ]);
        BankReconciliationLine::factory()->create([
            'bank_reconciliation_id' => $reconciliation->id,
            'debit' => 10000,
            'credit' => 0,
            'is_matched' => true,
            'matched_by' => $this->user->id,
            'matched_at' => now(),
        ]);

        $this->post(route('bank-reconciliations.reconcile', $reconciliation))->assertRedirect();

        $this->assertDatabaseHas('bank_reconciliations', [
            'id' => $reconciliation->id,
            'status' => 'reconciled',
            'reconciled_by' => $this->user->id,
            'system_balance' => 110000,
            'difference' => 0,
        ]);
    }

    public function test_index_can_filter_by_bank_and_status(): void
    {
        BankReconciliation::factory()->create([
            'bank_account_id' => $this->bankAccount->id,
            'status' => 'draft',
            'created_by' => $this->user->id,
        ]);

        $this->get(route('bank-reconciliations.index', [
            'bank_account_id' => $this->bankAccount->id,
            'status' => 'draft',
        ]))->assertOk();
    }

    private function payload(): array
    {
        return [
            'bank_account_id' => $this->bankAccount->id,
            'statement_start_date' => '2026-05-01',
            'statement_end_date' => '2026-05-31',
            'reconciliation_date' => '2026-05-31',
            'statement_opening_balance' => 100000,
            'statement_closing_balance' => 125000,
            'notes' => 'May reconciliation',
            'lines' => [
                [
                    'transaction_date' => '2026-05-10',
                    'description' => 'Customer receipt',
                    'debit' => 50000,
                    'credit' => 0,
                    'reference_number' => 'CR-001',
                ],
                [
                    'transaction_date' => '2026-05-12',
                    'description' => 'Bank charge',
                    'debit' => 0,
                    'credit' => 25000,
                    'reference_number' => 'BC-001',
                ],
            ],
        ];
    }
}

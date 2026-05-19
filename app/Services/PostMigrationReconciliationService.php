<?php

namespace App\Services;

use App\Models\Account;
use App\Models\Customer;
use App\Models\CustomerPayment;
use App\Models\Item;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use App\Models\PurchaseOrder;
use App\Models\SalesOrder;
use App\Models\Supplier;

class PostMigrationReconciliationService
{
    public function run(): array
    {
        $startedAt = microtime(true);

        $summary = [
            'accounts' => Account::count(),
            'customers' => Customer::count(),
            'suppliers' => Supplier::count(),
            'items' => Item::count(),
            'sales_orders' => SalesOrder::count(),
            'purchase_orders' => PurchaseOrder::count(),
            'customer_payments' => CustomerPayment::count(),
            'journal_entries' => JournalEntry::count(),
            'journal_lines' => JournalEntryLine::count(),
        ];

        $checkResults = [
            'posted_journal_balance' => $this->validatePostedJournalBalance(),
            'trial_balance' => $this->validateTrialBalance(),
            'header_account_usage' => $this->validateHeaderAccountsUsage(),
            'inactive_account_usage' => $this->validateInactiveAccountsUsage(),
        ];

        $issues = array_merge(...array_values($checkResults));

        $severityCounts = [
            'critical' => count(array_filter($issues, fn (array $issue) => $issue['severity'] === 'critical')),
            'warning' => count(array_filter($issues, fn (array $issue) => $issue['severity'] === 'warning')),
            'info' => count(array_filter($issues, fn (array $issue) => $issue['severity'] === 'info')),
        ];

        $totalChecks = count($checkResults);
        $failedChecks = count(array_filter($checkResults, fn (array $result) => $result !== []));

        return [
            'timestamp' => now()->toDateTimeString(),
            'duration_ms' => round((microtime(true) - $startedAt) * 1000, 2),
            'summary' => $summary,
            'issues' => $issues,
            'severity_counts' => $severityCounts,
            'total_checks' => $totalChecks,
            'passed_checks' => $totalChecks - $failedChecks,
            'failed_checks' => $failedChecks,
            'export_ready' => true,
        ];
    }

    private function validatePostedJournalBalance(): array
    {
        $issues = [];

        $journals = JournalEntry::query()
            ->where('status', 'posted')
            ->get();

        foreach ($journals as $journal) {
            if (round((float) $journal->total_debit, 2) !== round((float) $journal->total_credit, 2)) {
                $issues[] = [
                    'severity' => 'critical',
                    'module' => 'journal_entries',
                    'identifier' => $journal->journal_number,
                    'message' => 'Posted journal tidak balance.',
                    'recommended_action' => 'Periksa line debit/credit dan lakukan koreksi jurnal.',
                ];
            }
        }

        return $issues;
    }

    private function validateTrialBalance(): array
    {
        $debit = (float) JournalEntryLine::query()
            ->join('journal_entries', 'journal_entries.id', '=', 'journal_entry_lines.journal_entry_id')
            ->where('journal_entries.status', 'posted')
            ->sum('journal_entry_lines.debit');

        $credit = (float) JournalEntryLine::query()
            ->join('journal_entries', 'journal_entries.id', '=', 'journal_entry_lines.journal_entry_id')
            ->where('journal_entries.status', 'posted')
            ->sum('journal_entry_lines.credit');

        if (round($debit, 2) === round($credit, 2)) {
            return [];
        }

        return [[
            'severity' => 'critical',
            'module' => 'trial_balance',
            'identifier' => 'global',
            'message' => 'Trial balance tidak seimbang.',
            'recommended_action' => 'Telusuri jurnal sumber selisih dan lakukan penyesuaian.',
        ]];
    }

    private function validateHeaderAccountsUsage(): array
    {
        $count = JournalEntryLine::query()
            ->join('accounts', 'accounts.id', '=', 'journal_entry_lines.account_id')
            ->where('accounts.is_header', true)
            ->count();

        if ($count === 0) {
            return [];
        }

        return [[
            'severity' => 'warning',
            'module' => 'accounts',
            'identifier' => 'header-account-usage',
            'message' => "Terdapat {$count} journal line memakai header account.",
            'recommended_action' => 'Pindahkan posting ke detail account non-header.',
        ]];
    }

    private function validateInactiveAccountsUsage(): array
    {
        $count = JournalEntryLine::query()
            ->join('accounts', 'accounts.id', '=', 'journal_entry_lines.account_id')
            ->where('accounts.is_active', false)
            ->count();

        if ($count === 0) {
            return [];
        }

        return [[
            'severity' => 'warning',
            'module' => 'accounts',
            'identifier' => 'inactive-account-usage',
            'message' => "Terdapat {$count} journal line memakai account inactive.",
            'recommended_action' => 'Aktifkan account atau remap posting ke account aktif.',
        ]];
    }
}

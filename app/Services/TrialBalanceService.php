<?php

namespace App\Services;

use App\Models\Account;
use App\Models\JournalLine;
use App\Models\FiscalPeriod;

class TrialBalanceService
{
    public function generate(
        int $fiscalPeriodId,
        string $fromDate,
        string $toDate,
        array $accountTypes = [],
        bool $showZeroBalance = false
    ): array {
        // 1. Get all accounts (filtered by type if specified)
        $accounts = Account::query()
            ->when($accountTypes, fn($q) => $q->whereIn('type', $accountTypes))
            ->orderBy('code')
            ->get();

        // 2. Calculate balances for each account
        $trialBalance = [];
        foreach ($accounts as $account) {
            $balance = $this->calculateAccountBalance(
                $account,
                $fiscalPeriodId,
                $fromDate,
                $toDate
            );

            if ($showZeroBalance || $balance['ending_balance'] != 0) {
                $trialBalance[] = [
                    'account_code' => $account->code,
                    'account_name' => $account->name,
                    'account_type' => $account->type,
                    'debit' => $balance['debit'],
                    'credit' => $balance['credit'],
                ];
            }
        }

        // 3. Group by account type and calculate subtotals
        return $this->groupAndSubtotal($trialBalance);
    }

    private function calculateAccountBalance(
        Account $account,
        int $fiscalPeriodId,
        string $fromDate,
        string $toDate
    ): array {
        // Get all posted journal lines for this account in period
        $lines = JournalLine::query()
            ->whereHas('journalEntry', function($q) use ($fiscalPeriodId, $fromDate, $toDate) {
                $q->where('fiscal_period_id', $fiscalPeriodId)
                  ->where('status', 'posted')
                  ->whereBetween('journal_date', [$fromDate, $toDate]);
            })
            ->where('account_id', $account->id)
            ->selectRaw('SUM(debit) as total_debit, SUM(credit) as total_credit')
            ->first();

        $totalDebit = $lines->total_debit ?? 0;
        $totalCredit = $lines->total_credit ?? 0;
        $endingBalance = $totalDebit - $totalCredit;

        // Determine display column based on normal balance
        $normalBalance = $account->getNormalBalance(); // 'debit' or 'credit'
        
        if ($normalBalance === 'debit') {
            return [
                'debit' => $endingBalance >= 0 ? $endingBalance : 0,
                'credit' => $endingBalance < 0 ? abs($endingBalance) : 0,
                'ending_balance' => $endingBalance,
            ];
        } else {
            return [
                'debit' => $endingBalance < 0 ? abs($endingBalance) : 0,
                'credit' => $endingBalance >= 0 ? $endingBalance : 0,
                'ending_balance' => $endingBalance,
            ];
        }
    }

    private function groupAndSubtotal(array $trialBalance): array
    {
        $grouped = [];
        $grandTotalDebit = 0;
        $grandTotalCredit = 0;

        // Group by account type
        $types = ['asset', 'liability', 'equity', 'revenue', 'expense'];
        
        foreach ($types as $type) {
            $accounts = array_filter($trialBalance, fn($item) => $item['account_type'] === $type);
            
            if (empty($accounts)) {
                continue;
            }

            $totalDebit = array_sum(array_column($accounts, 'debit'));
            $totalCredit = array_sum(array_column($accounts, 'credit'));

            $grouped[] = [
                'type' => $type,
                'accounts' => array_values($accounts),
                'total_debit' => $totalDebit,
                'total_credit' => $totalCredit,
            ];

            $grandTotalDebit += $totalDebit;
            $grandTotalCredit += $totalCredit;
        }

        $isBalanced = abs($grandTotalDebit - $grandTotalCredit) < 0.01; // Allow for rounding
        $difference = $grandTotalDebit - $grandTotalCredit;

        return [
            'groups' => $grouped,
            'grand_total_debit' => $grandTotalDebit,
            'grand_total_credit' => $grandTotalCredit,
            'is_balanced' => $isBalanced,
            'difference' => $difference,
        ];
    }

    public function exportToExcel(array $trialBalance, array $params): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        // TODO: Implementation using Laravel Excel
        throw new \Exception('Excel export not implemented yet');
    }

    public function exportToPdf(array $trialBalance, array $params): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        // TODO: Implementation using dompdf or snappy
        throw new \Exception('PDF export not implemented yet');
    }
}

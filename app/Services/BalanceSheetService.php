<?php

namespace App\Services;

use App\Models\Account;
use App\Models\FiscalPeriod;

class BalanceSheetService
{
    public function __construct(
        private ProfitLossService $profitLossService
    ) {}

    public function generate(
        string $asOfDate,
        int $fiscalPeriodId,
        string $detailLevel = 'summary'
    ): array {
        $currentDate = $this->calculateBalanceSheet($asOfDate, $fiscalPeriodId);

        return [
            'current_date' => $currentDate,
            'detail_level' => $detailLevel,
        ];
    }

    private function calculateBalanceSheet(string $asOfDate, int $fiscalPeriodId): array
    {
        // 1. Get Assets
        $assets = $this->getAssets($asOfDate, $fiscalPeriodId);
        $currentAssets = array_filter($assets, fn($a) => str_contains(strtolower($a['category'] ?? ''), 'current'));
        $nonCurrentAssets = array_filter($assets, fn($a) => !str_contains(strtolower($a['category'] ?? ''), 'current'));
        
        $totalCurrentAssets = array_sum(array_column($currentAssets, 'balance'));
        $totalNonCurrentAssets = array_sum(array_column($nonCurrentAssets, 'balance'));
        $totalAssets = $totalCurrentAssets + $totalNonCurrentAssets;

        // 2. Get Liabilities
        $liabilities = $this->getLiabilities($asOfDate, $fiscalPeriodId);
        $currentLiabilities = array_filter($liabilities, fn($l) => str_contains(strtolower($l['category'] ?? ''), 'current'));
        $nonCurrentLiabilities = array_filter($liabilities, fn($l) => !str_contains(strtolower($l['category'] ?? ''), 'current'));
        
        $totalCurrentLiabilities = array_sum(array_column($currentLiabilities, 'balance'));
        $totalNonCurrentLiabilities = array_sum(array_column($nonCurrentLiabilities, 'balance'));
        $totalLiabilities = $totalCurrentLiabilities + $totalNonCurrentLiabilities;

        // 3. Get Equity
        $equity = $this->getEquity($asOfDate, $fiscalPeriodId);
        
        // 4. Calculate Current Year Profit/Loss
        $fiscalPeriod = FiscalPeriod::findOrFail($fiscalPeriodId);
        $profitLoss = $this->profitLossService->generate(
            $fiscalPeriodId,
            $fiscalPeriod->start_date,
            $asOfDate
        );
        $currentYearProfitLoss = $profitLoss['current_period']['net_profit_after_tax'];
        
        $totalEquity = array_sum(array_column($equity, 'balance')) + $currentYearProfitLoss;

        // 5. Calculate Financial Ratios
        $currentRatio = $totalCurrentLiabilities > 0 
            ? $totalCurrentAssets / $totalCurrentLiabilities 
            : 0;
        
        $debtToEquityRatio = $totalEquity > 0 
            ? $totalLiabilities / $totalEquity 
            : 0;
        
        $workingCapital = $totalCurrentAssets - $totalCurrentLiabilities;

        // 6. Balance Check
        $totalLiabilitiesEquity = $totalLiabilities + $totalEquity;
        $isBalanced = abs($totalAssets - $totalLiabilitiesEquity) < 0.01; // Allow for rounding

        return [
            'as_of_date' => $asOfDate,
            'assets' => [
                'current' => array_values($currentAssets),
                'non_current' => array_values($nonCurrentAssets),
                'total_current' => $totalCurrentAssets,
                'total_non_current' => $totalNonCurrentAssets,
                'total' => $totalAssets,
            ],
            'liabilities' => [
                'current' => array_values($currentLiabilities),
                'non_current' => array_values($nonCurrentLiabilities),
                'total_current' => $totalCurrentLiabilities,
                'total_non_current' => $totalNonCurrentLiabilities,
                'total' => $totalLiabilities,
            ],
            'equity' => [
                'accounts' => $equity,
                'current_year_profit_loss' => $currentYearProfitLoss,
                'total' => $totalEquity,
            ],
            'total_liabilities_equity' => $totalLiabilitiesEquity,
            'is_balanced' => $isBalanced,
            'difference' => $totalAssets - $totalLiabilitiesEquity,
            'ratios' => [
                'current_ratio' => $currentRatio,
                'debt_to_equity_ratio' => $debtToEquityRatio,
                'working_capital' => $workingCapital,
            ],
        ];
    }

    private function getAssets(string $asOfDate, int $fiscalPeriodId): array
    {
        return Account::where('type', 'asset')
            ->with(['journalLines' => function($q) use ($asOfDate, $fiscalPeriodId) {
                $q->whereHas('journalEntry', function($jq) use ($asOfDate, $fiscalPeriodId) {
                    $jq->where('fiscal_period_id', $fiscalPeriodId)
                       ->where('status', 'posted')
                       ->where('journal_date', '<=', $asOfDate);
                });
            }])
            ->get()
            ->map(function($account) {
                $balance = $account->journalLines->sum('debit') - $account->journalLines->sum('credit');
                return [
                    'account_code' => $account->code,
                    'account_name' => $account->name,
                    'category' => $account->category,
                    'balance' => $balance,
                ];
            })
            ->filter(fn($item) => $item['balance'] != 0)
            ->values()
            ->toArray();
    }

    private function getLiabilities(string $asOfDate, int $fiscalPeriodId): array
    {
        return Account::where('type', 'liability')
            ->with(['journalLines' => function($q) use ($asOfDate, $fiscalPeriodId) {
                $q->whereHas('journalEntry', function($jq) use ($asOfDate, $fiscalPeriodId) {
                    $jq->where('fiscal_period_id', $fiscalPeriodId)
                       ->where('status', 'posted')
                       ->where('journal_date', '<=', $asOfDate);
                });
            }])
            ->get()
            ->map(function($account) {
                $balance = $account->journalLines->sum('credit') - $account->journalLines->sum('debit');
                return [
                    'account_code' => $account->code,
                    'account_name' => $account->name,
                    'category' => $account->category,
                    'balance' => $balance,
                ];
            })
            ->filter(fn($item) => $item['balance'] != 0)
            ->values()
            ->toArray();
    }

    private function getEquity(string $asOfDate, int $fiscalPeriodId): array
    {
        return Account::where('type', 'equity')
            ->with(['journalLines' => function($q) use ($asOfDate, $fiscalPeriodId) {
                $q->whereHas('journalEntry', function($jq) use ($asOfDate, $fiscalPeriodId) {
                    $jq->where('fiscal_period_id', $fiscalPeriodId)
                       ->where('status', 'posted')
                       ->where('journal_date', '<=', $asOfDate);
                });
            }])
            ->get()
            ->map(function($account) {
                $balance = $account->journalLines->sum('credit') - $account->journalLines->sum('debit');
                return [
                    'account_code' => $account->code,
                    'account_name' => $account->name,
                    'balance' => $balance,
                ];
            })
            ->filter(fn($item) => $item['balance'] != 0)
            ->values()
            ->toArray();
    }

    public function exportToExcel(array $balanceSheet, array $params): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        // TODO: Implementation using Laravel Excel
        throw new \Exception('Excel export not implemented yet');
    }

    public function exportToPdf(array $balanceSheet, array $params): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        // TODO: Implementation using dompdf or snappy
        throw new \Exception('PDF export not implemented yet');
    }
}

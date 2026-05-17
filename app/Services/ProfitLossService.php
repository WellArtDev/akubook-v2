<?php

namespace App\Services;

use App\Models\Account;
use App\Models\FiscalPeriod;

class ProfitLossService
{
    public function generate(
        int $fiscalPeriodId,
        string $fromDate,
        string $toDate,
        string $detailLevel = 'summary'
    ): array {
        $currentPeriod = $this->calculatePeriod($fiscalPeriodId, $fromDate, $toDate);

        return [
            'current_period' => $currentPeriod,
            'detail_level' => $detailLevel,
        ];
    }

    private function calculatePeriod(int $fiscalPeriodId, string $fromDate, string $toDate): array
    {
        // 1. Get Revenue
        $revenue = $this->getRevenue($fiscalPeriodId, $fromDate, $toDate);
        $totalRevenue = array_sum(array_column($revenue, 'amount'));

        // 2. Get COGS
        $cogs = $this->getCOGS($fiscalPeriodId, $fromDate, $toDate);
        $totalCOGS = array_sum(array_column($cogs, 'amount'));

        // 3. Calculate Gross Profit
        $grossProfit = $totalRevenue - $totalCOGS;
        $grossProfitMargin = $totalRevenue > 0 ? ($grossProfit / $totalRevenue) * 100 : 0;

        // 4. Get Operating Expenses
        $operatingExpenses = $this->getOperatingExpenses($fiscalPeriodId, $fromDate, $toDate);
        $totalOperatingExpenses = array_sum(array_column($operatingExpenses, 'amount'));

        // 5. Calculate Operating Profit
        $operatingProfit = $grossProfit - $totalOperatingExpenses;
        $operatingProfitMargin = $totalRevenue > 0 ? ($operatingProfit / $totalRevenue) * 100 : 0;

        // 6. Get Other Income/Expenses
        $otherIncomeExpenses = $this->getOtherIncomeExpenses($fiscalPeriodId, $fromDate, $toDate);
        $totalOtherIncomeExpenses = array_sum(array_column($otherIncomeExpenses, 'amount'));

        // 7. Calculate Net Profit Before Tax
        $netProfitBeforeTax = $operatingProfit + $totalOtherIncomeExpenses;

        // 8. Get Tax Expense
        $taxExpense = $this->getTaxExpense($fiscalPeriodId, $fromDate, $toDate);
        $totalTaxExpense = array_sum(array_column($taxExpense, 'amount'));

        // 9. Calculate Net Profit After Tax
        $netProfitAfterTax = $netProfitBeforeTax - $totalTaxExpense;
        $netProfitMargin = $totalRevenue > 0 ? ($netProfitAfterTax / $totalRevenue) * 100 : 0;

        return [
            'revenue' => $revenue,
            'total_revenue' => $totalRevenue,
            'cogs' => $cogs,
            'total_cogs' => $totalCOGS,
            'gross_profit' => $grossProfit,
            'gross_profit_margin' => $grossProfitMargin,
            'operating_expenses' => $operatingExpenses,
            'total_operating_expenses' => $totalOperatingExpenses,
            'operating_profit' => $operatingProfit,
            'operating_profit_margin' => $operatingProfitMargin,
            'other_income_expenses' => $otherIncomeExpenses,
            'total_other_income_expenses' => $totalOtherIncomeExpenses,
            'net_profit_before_tax' => $netProfitBeforeTax,
            'tax_expense' => $taxExpense,
            'total_tax_expense' => $totalTaxExpense,
            'net_profit_after_tax' => $netProfitAfterTax,
            'net_profit_margin' => $netProfitMargin,
        ];
    }

    private function getRevenue(int $fiscalPeriodId, string $fromDate, string $toDate): array
    {
        return Account::where('type', 'revenue')
            ->with(['journalLines' => function($q) use ($fiscalPeriodId, $fromDate, $toDate) {
                $q->whereHas('journalEntry', function($jq) use ($fiscalPeriodId, $fromDate, $toDate) {
                    $jq->where('fiscal_period_id', $fiscalPeriodId)
                       ->where('status', 'posted')
                       ->whereBetween('journal_date', [$fromDate, $toDate]);
                });
            }])
            ->get()
            ->map(function($account) {
                $amount = $account->journalLines->sum('credit') - $account->journalLines->sum('debit');
                return [
                    'account_code' => $account->code,
                    'account_name' => $account->name,
                    'category' => $account->category,
                    'amount' => $amount,
                ];
            })
            ->filter(fn($item) => $item['amount'] != 0)
            ->values()
            ->toArray();
    }

    private function getCOGS(int $fiscalPeriodId, string $fromDate, string $toDate): array
    {
        return Account::where('type', 'expense')
            ->where(function($q) {
                $q->where('category', 'LIKE', '%cogs%')
                  ->orWhere('category', 'LIKE', '%cost_of_goods%');
            })
            ->with(['journalLines' => function($q) use ($fiscalPeriodId, $fromDate, $toDate) {
                $q->whereHas('journalEntry', function($jq) use ($fiscalPeriodId, $fromDate, $toDate) {
                    $jq->where('fiscal_period_id', $fiscalPeriodId)
                       ->where('status', 'posted')
                       ->whereBetween('journal_date', [$fromDate, $toDate]);
                });
            }])
            ->get()
            ->map(function($account) {
                $amount = $account->journalLines->sum('debit') - $account->journalLines->sum('credit');
                return [
                    'account_code' => $account->code,
                    'account_name' => $account->name,
                    'amount' => $amount,
                ];
            })
            ->filter(fn($item) => $item['amount'] != 0)
            ->values()
            ->toArray();
    }

    private function getOperatingExpenses(int $fiscalPeriodId, string $fromDate, string $toDate): array
    {
        return Account::where('type', 'expense')
            ->where('category', 'LIKE', '%operating%')
            ->with(['journalLines' => function($q) use ($fiscalPeriodId, $fromDate, $toDate) {
                $q->whereHas('journalEntry', function($jq) use ($fiscalPeriodId, $fromDate, $toDate) {
                    $jq->where('fiscal_period_id', $fiscalPeriodId)
                       ->where('status', 'posted')
                       ->whereBetween('journal_date', [$fromDate, $toDate]);
                });
            }])
            ->get()
            ->map(function($account) {
                $amount = $account->journalLines->sum('debit') - $account->journalLines->sum('credit');
                return [
                    'account_code' => $account->code,
                    'account_name' => $account->name,
                    'amount' => $amount,
                ];
            })
            ->filter(fn($item) => $item['amount'] != 0)
            ->values()
            ->toArray();
    }

    private function getOtherIncomeExpenses(int $fiscalPeriodId, string $fromDate, string $toDate): array
    {
        return Account::where(function($q) {
                $q->where('type', 'expense')->where('category', 'LIKE', '%other%')
                  ->orWhere(function($q2) {
                      $q2->where('type', 'revenue')->where('category', 'LIKE', '%other%');
                  });
            })
            ->with(['journalLines' => function($q) use ($fiscalPeriodId, $fromDate, $toDate) {
                $q->whereHas('journalEntry', function($jq) use ($fiscalPeriodId, $fromDate, $toDate) {
                    $jq->where('fiscal_period_id', $fiscalPeriodId)
                       ->where('status', 'posted')
                       ->whereBetween('journal_date', [$fromDate, $toDate]);
                });
            }])
            ->get()
            ->map(function($account) {
                // Income: credit - debit (positive)
                // Expense: debit - credit (negative)
                $amount = $account->journalLines->sum('credit') - $account->journalLines->sum('debit');
                return [
                    'account_code' => $account->code,
                    'account_name' => $account->name,
                    'amount' => $amount,
                ];
            })
            ->filter(fn($item) => $item['amount'] != 0)
            ->values()
            ->toArray();
    }

    private function getTaxExpense(int $fiscalPeriodId, string $fromDate, string $toDate): array
    {
        return Account::where('type', 'expense')
            ->where('category', 'LIKE', '%tax%')
            ->with(['journalLines' => function($q) use ($fiscalPeriodId, $fromDate, $toDate) {
                $q->whereHas('journalEntry', function($jq) use ($fiscalPeriodId, $fromDate, $toDate) {
                    $jq->where('fiscal_period_id', $fiscalPeriodId)
                       ->where('status', 'posted')
                       ->whereBetween('journal_date', [$fromDate, $toDate]);
                });
            }])
            ->get()
            ->map(function($account) {
                $amount = $account->journalLines->sum('debit') - $account->journalLines->sum('credit');
                return [
                    'account_code' => $account->code,
                    'account_name' => $account->name,
                    'amount' => $amount,
                ];
            })
            ->filter(fn($item) => $item['amount'] != 0)
            ->values()
            ->toArray();
    }

    public function exportToExcel(array $profitLoss, array $params): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        // TODO: Implementation using Laravel Excel
        throw new \Exception('Excel export not implemented yet');
    }

    public function exportToPdf(array $profitLoss, array $params): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        // TODO: Implementation using dompdf or snappy
        throw new \Exception('PDF export not implemented yet');
    }
}

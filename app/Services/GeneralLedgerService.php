<?php

namespace App\Services;

use App\Models\Account;
use App\Models\JournalLine;
use App\Models\FiscalPeriod;

class GeneralLedgerService
{
    public function generate(
        int $accountId,
        int $fiscalPeriodId,
        string $fromDate,
        string $toDate
    ): array {
        $account = Account::findOrFail($accountId);
        $fiscalPeriod = FiscalPeriod::findOrFail($fiscalPeriodId);

        // 1. Calculate opening balance
        $openingBalance = $this->calculateOpeningBalance(
            $accountId,
            $fiscalPeriodId,
            $fromDate
        );

        // 2. Get transaction lines
        $lines = $this->getTransactionLines(
            $accountId,
            $fiscalPeriodId,
            $fromDate,
            $toDate
        );

        // 3. Calculate running balance
        $runningBalance = $openingBalance;
        $totalDebit = 0;
        $totalCredit = 0;

        foreach ($lines as &$line) {
            $runningBalance += $line['debit'] - $line['credit'];
            $line['balance'] = $runningBalance;
            $totalDebit += $line['debit'];
            $totalCredit += $line['credit'];
        }

        $endingBalance = $openingBalance + $totalDebit - $totalCredit;
        $netMovement = $totalDebit - $totalCredit;

        return [
            'account' => [
                'code' => $account->code,
                'name' => $account->name,
                'type' => $account->type,
            ],
            'fiscal_period' => [
                'name' => $fiscalPeriod->period_name,
            ],
            'date_range' => [
                'from' => $fromDate,
                'to' => $toDate,
            ],
            'opening_balance' => $openingBalance,
            'lines' => $lines,
            'ending_balance' => $endingBalance,
            'total_debit' => $totalDebit,
            'total_credit' => $totalCredit,
            'net_movement' => $netMovement,
        ];
    }

    private function calculateOpeningBalance(
        int $accountId,
        int $fiscalPeriodId,
        string $fromDate
    ): float {
        $result = JournalLine::query()
            ->whereHas('journalEntry', function($q) use ($fiscalPeriodId, $fromDate) {
                $q->where('fiscal_period_id', $fiscalPeriodId)
                  ->where('status', 'posted')
                  ->where('journal_date', '<', $fromDate);
            })
            ->where('account_id', $accountId)
            ->selectRaw('SUM(debit) - SUM(credit) as balance')
            ->first();

        return $result->balance ?? 0;
    }

    private function getTransactionLines(
        int $accountId,
        int $fiscalPeriodId,
        string $fromDate,
        string $toDate
    ): array {
        return JournalLine::query()
            ->with(['journalEntry'])
            ->whereHas('journalEntry', function($q) use ($fiscalPeriodId, $fromDate, $toDate) {
                $q->where('fiscal_period_id', $fiscalPeriodId)
                  ->where('status', 'posted')
                  ->whereBetween('journal_date', [$fromDate, $toDate]);
            })
            ->where('account_id', $accountId)
            ->join('journal_entries', 'journal_lines.journal_entry_id', '=', 'journal_entries.id')
            ->orderBy('journal_entries.journal_date')
            ->orderBy('journal_lines.journal_entry_id')
            ->select('journal_lines.*')
            ->get()
            ->map(function($line) {
                return [
                    'journal_entry_id' => $line->journal_entry_id,
                    'date' => $line->journalEntry->journal_date,
                    'reference' => $line->journalEntry->reference_number,
                    'description' => $line->description ?: $line->journalEntry->description,
                    'debit' => $line->debit,
                    'credit' => $line->credit,
                ];
            })
            ->toArray();
    }

    public function exportToExcel(array $generalLedger, array $params): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        // TODO: Implementation using Laravel Excel
        throw new \Exception('Excel export not implemented yet');
    }

    public function exportToPdf(array $generalLedger, array $params): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        // TODO: Implementation using dompdf or snappy
        throw new \Exception('PDF export not implemented yet');
    }
}

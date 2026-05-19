<?php

namespace App\Http\Controllers;

use App\Models\JournalEntryLine;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class FinancialReportController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
        ]);

        $dateFrom = $request->date_from ?: now()->startOfMonth()->toDateString();
        $dateTo = $request->date_to ?: now()->toDateString();

        $balances = $this->accountBalances($dateFrom, $dateTo);

        $trialBalanceRows = $balances->map(function ($row) {
            return [
                'account_code' => $row['account_code'],
                'account_name' => $row['account_name'],
                'type' => $row['type'],
                'debit' => $row['debit'],
                'credit' => $row['credit'],
                'balance' => $row['balance'],
            ];
        })->values();

        $trialSummary = [
            'total_debit' => round($trialBalanceRows->sum('debit'), 2),
            'total_credit' => round($trialBalanceRows->sum('credit'), 2),
            'total_balance' => round($trialBalanceRows->sum('balance'), 2),
        ];

        $revenue = round($balances->where('type', 'revenue')->sum('balance'), 2);
        $expense = round($balances->where('type', 'expense')->sum('balance'), 2);
        $netProfit = round($revenue - $expense, 2);

        $profitLoss = [
            'revenue' => $revenue,
            'expense' => $expense,
            'net_profit' => $netProfit,
        ];

        $assets = round($balances->where('type', 'asset')->sum('balance'), 2);
        $liabilities = round($balances->where('type', 'liability')->sum('balance'), 2);
        $equity = round($balances->where('type', 'equity')->sum('balance'), 2);

        $balanceSheet = [
            'assets' => $assets,
            'liabilities' => $liabilities,
            'equity' => $equity,
            'liabilities_plus_equity' => round($liabilities + $equity, 2),
        ];

        return Inertia::render('FinancialReports/Index', [
            'generated_at' => now()->toDateTimeString(),
            'filters' => [
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
            ],
            'trial_balance' => [
                'rows' => $trialBalanceRows,
                'summary' => $trialSummary,
            ],
            'profit_loss' => $profitLoss,
            'balance_sheet' => $balanceSheet,
        ]);
    }

    private function accountBalances(string $dateFrom, string $dateTo): Collection
    {
        $raw = JournalEntryLine::query()
            ->join('journal_entries', 'journal_entries.id', '=', 'journal_entry_lines.journal_entry_id')
            ->join('accounts', 'accounts.id', '=', 'journal_entry_lines.account_id')
            ->where('journal_entries.status', 'posted')
            ->whereDate('journal_entries.journal_date', '>=', $dateFrom)
            ->whereDate('journal_entries.journal_date', '<=', $dateTo)
            ->whereNull('journal_entries.deleted_at')
            ->select(
                'accounts.id as account_id',
                'accounts.code as account_code',
                'accounts.name as account_name',
                'accounts.type as type',
                DB::raw('COALESCE(SUM(journal_entry_lines.debit),0) as debit_sum'),
                DB::raw('COALESCE(SUM(journal_entry_lines.credit),0) as credit_sum')
            )
            ->groupBy('accounts.id', 'accounts.code', 'accounts.name', 'accounts.type')
            ->orderBy('accounts.code')
            ->get();

        return $raw->map(function ($row) {
            $debit = (float) $row->debit_sum;
            $credit = (float) $row->credit_sum;
            $normal = in_array($row->type, ['asset', 'expense'], true) ? 'debit' : 'credit';
            $balance = $normal === 'debit' ? ($debit - $credit) : ($credit - $debit);

            return [
                'account_id' => (int) $row->account_id,
                'account_code' => $row->account_code,
                'account_name' => $row->account_name,
                'type' => $row->type,
                'debit' => round($debit, 2),
                'credit' => round($credit, 2),
                'balance' => round($balance, 2),
            ];
        });
    }
}

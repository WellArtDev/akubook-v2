<?php

namespace App\Http\Controllers;

use App\Models\BankAccount;
use App\Models\CashAccount;
use App\Models\Voucher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class CashFlowReportController extends Controller
{
    public function index(Request $request)
    {
        $dateFrom = $request->input('date_from', now()->startOfMonth()->toDateString());
        $dateTo = $request->input('date_to', now()->endOfMonth()->toDateString());

        $baseQuery = Voucher::query()
            ->with(['counterpartAccount'])
            ->where('status', 'posted')
            ->whereDate('voucher_date', '>=', $dateFrom)
            ->whereDate('voucher_date', '<=', $dateTo)
            ->when($request->filled('cash_bank_type'), fn ($query) => $query->where('cash_bank_type', $request->cash_bank_type))
            ->when($request->filled('cash_bank_account_id'), fn ($query) => $query->where('cash_bank_account_id', $request->cash_bank_account_id));

        $transactions = $baseQuery
            ->orderBy('voucher_date')
            ->orderBy('voucher_number')
            ->get();

        $cashIn = (clone $baseQuery)
            ->where('voucher_type', 'receipt')
            ->sum('amount');

        $cashOut = (clone $baseQuery)
            ->where('voucher_type', 'payment')
            ->sum('amount');

        $openingBalance = $this->openingBalance($request);
        $closingBalance = (float) $openingBalance + (float) $cashIn - (float) $cashOut;

        return Inertia::render('CashFlowReports/Index', [
            'transactions' => $transactions,
            'summary' => [
                'opening_balance' => (float) $openingBalance,
                'cash_in' => (float) $cashIn,
                'cash_out' => (float) $cashOut,
                'closing_balance' => (float) $closingBalance,
            ],
            'filters' => [
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
                'cash_bank_type' => $request->input('cash_bank_type', ''),
                'cash_bank_account_id' => $request->input('cash_bank_account_id', ''),
            ],
            'cashAccounts' => CashAccount::query()->where('is_active', true)->orderBy('code')->get(['id', 'code', 'name']),
            'bankAccounts' => BankAccount::query()->where('is_active', true)->orderBy('code')->get(['id', 'code', 'name', 'bank_name']),
        ]);
    }

    private function openingBalance(Request $request): float
    {
        if ($request->filled('cash_bank_type') && $request->filled('cash_bank_account_id')) {
            if ($request->cash_bank_type === 'cash') {
                return (float) CashAccount::query()->whereKey($request->cash_bank_account_id)->value('opening_balance');
            }

            return (float) BankAccount::query()->whereKey($request->cash_bank_account_id)->value('opening_balance');
        }

        if ($request->filled('cash_bank_type')) {
            if ($request->cash_bank_type === 'cash') {
                return (float) CashAccount::query()->where('is_active', true)->sum('opening_balance');
            }

            return (float) BankAccount::query()->where('is_active', true)->sum('opening_balance');
        }

        return (float) CashAccount::query()->where('is_active', true)->sum('opening_balance')
            + (float) BankAccount::query()->where('is_active', true)->sum('opening_balance');
    }
}

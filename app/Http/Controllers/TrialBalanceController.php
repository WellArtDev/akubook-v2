<?php

namespace App\Http\Controllers;

use App\Models\FiscalPeriod;
use App\Services\TrialBalanceService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TrialBalanceController extends Controller
{
    public function __construct(
        private TrialBalanceService $trialBalanceService
    ) {}

    public function index()
    {
        $fiscalPeriods = FiscalPeriod::orderBy('start_date', 'desc')->get();
        
        return Inertia::render('Reports/TrialBalance', [
            'fiscalPeriods' => $fiscalPeriods,
        ]);
    }

    public function generate(Request $request)
    {
        $validated = $request->validate([
            'fiscal_period_id' => 'required|exists:fiscal_periods,id',
            'from_date' => 'required|date',
            'to_date' => 'required|date|after_or_equal:from_date',
            'account_types' => 'array',
            'account_types.*' => 'in:asset,liability,equity,revenue,expense',
            'show_zero_balance' => 'boolean',
        ]);

        $trialBalance = $this->trialBalanceService->generate(
            $validated['fiscal_period_id'],
            $validated['from_date'],
            $validated['to_date'],
            $validated['account_types'] ?? [],
            $validated['show_zero_balance'] ?? false
        );

        return response()->json($trialBalance);
    }

    public function exportExcel(Request $request)
    {
        $validated = $request->validate([
            'fiscal_period_id' => 'required|exists:fiscal_periods,id',
            'from_date' => 'required|date',
            'to_date' => 'required|date|after_or_equal:from_date',
            'account_types' => 'array',
            'account_types.*' => 'in:asset,liability,equity,revenue,expense',
            'show_zero_balance' => 'boolean',
        ]);

        $trialBalance = $this->trialBalanceService->generate(
            $validated['fiscal_period_id'],
            $validated['from_date'],
            $validated['to_date'],
            $validated['account_types'] ?? [],
            $validated['show_zero_balance'] ?? false
        );

        return $this->trialBalanceService->exportToExcel($trialBalance, $validated);
    }

    public function exportPdf(Request $request)
    {
        $validated = $request->validate([
            'fiscal_period_id' => 'required|exists:fiscal_periods,id',
            'from_date' => 'required|date',
            'to_date' => 'required|date|after_or_equal:from_date',
            'account_types' => 'array',
            'account_types.*' => 'in:asset,liability,equity,revenue,expense',
            'show_zero_balance' => 'boolean',
        ]);

        $trialBalance = $this->trialBalanceService->generate(
            $validated['fiscal_period_id'],
            $validated['from_date'],
            $validated['to_date'],
            $validated['account_types'] ?? [],
            $validated['show_zero_balance'] ?? false
        );

        return $this->trialBalanceService->exportToPdf($trialBalance, $validated);
    }
}

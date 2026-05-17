<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\FiscalPeriod;
use App\Services\GeneralLedgerService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class GeneralLedgerController extends Controller
{
    public function __construct(
        private GeneralLedgerService $generalLedgerService
    ) {}

    public function index()
    {
        $accounts = Account::orderBy('code')->get();
        $fiscalPeriods = FiscalPeriod::orderBy('start_date', 'desc')->get();
        
        return Inertia::render('Reports/GeneralLedger', [
            'accounts' => $accounts,
            'fiscalPeriods' => $fiscalPeriods,
        ]);
    }

    public function generate(Request $request)
    {
        $validated = $request->validate([
            'account_id' => 'required|exists:accounts,id',
            'fiscal_period_id' => 'required|exists:fiscal_periods,id',
            'from_date' => 'required|date',
            'to_date' => 'required|date|after_or_equal:from_date',
        ]);

        $generalLedger = $this->generalLedgerService->generate(
            $validated['account_id'],
            $validated['fiscal_period_id'],
            $validated['from_date'],
            $validated['to_date']
        );

        return response()->json($generalLedger);
    }

    public function exportExcel(Request $request)
    {
        $validated = $request->validate([
            'account_id' => 'required|exists:accounts,id',
            'fiscal_period_id' => 'required|exists:fiscal_periods,id',
            'from_date' => 'required|date',
            'to_date' => 'required|date|after_or_equal:from_date',
        ]);

        $generalLedger = $this->generalLedgerService->generate(
            $validated['account_id'],
            $validated['fiscal_period_id'],
            $validated['from_date'],
            $validated['to_date']
        );

        return $this->generalLedgerService->exportToExcel($generalLedger, $validated);
    }

    public function exportPdf(Request $request)
    {
        $validated = $request->validate([
            'account_id' => 'required|exists:accounts,id',
            'fiscal_period_id' => 'required|exists:fiscal_periods,id',
            'from_date' => 'required|date',
            'to_date' => 'required|date|after_or_equal:from_date',
        ]);

        $generalLedger = $this->generalLedgerService->generate(
            $validated['account_id'],
            $validated['fiscal_period_id'],
            $validated['from_date'],
            $validated['to_date']
        );

        return $this->generalLedgerService->exportToPdf($generalLedger, $validated);
    }
}

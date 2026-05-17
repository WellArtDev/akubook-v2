<?php

namespace App\Http\Controllers;

use App\Models\FiscalPeriod;
use App\Services\BalanceSheetService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class BalanceSheetController extends Controller
{
    public function __construct(
        private BalanceSheetService $balanceSheetService
    ) {}

    public function index()
    {
        $fiscalPeriods = FiscalPeriod::orderBy('start_date', 'desc')->get();
        
        return Inertia::render('Reports/BalanceSheet', [
            'fiscalPeriods' => $fiscalPeriods,
        ]);
    }

    public function generate(Request $request)
    {
        $validated = $request->validate([
            'as_of_date' => 'required|date',
            'fiscal_period_id' => 'required|exists:fiscal_periods,id',
            'detail_level' => 'in:summary,detailed',
        ]);

        $balanceSheet = $this->balanceSheetService->generate(
            $validated['as_of_date'],
            $validated['fiscal_period_id'],
            $validated['detail_level'] ?? 'summary'
        );

        return response()->json($balanceSheet);
    }

    public function exportExcel(Request $request)
    {
        $validated = $request->validate([
            'as_of_date' => 'required|date',
            'fiscal_period_id' => 'required|exists:fiscal_periods,id',
            'detail_level' => 'in:summary,detailed',
        ]);

        $balanceSheet = $this->balanceSheetService->generate(
            $validated['as_of_date'],
            $validated['fiscal_period_id'],
            $validated['detail_level'] ?? 'summary'
        );

        return $this->balanceSheetService->exportToExcel($balanceSheet, $validated);
    }

    public function exportPdf(Request $request)
    {
        $validated = $request->validate([
            'as_of_date' => 'required|date',
            'fiscal_period_id' => 'required|exists:fiscal_periods,id',
            'detail_level' => 'in:summary,detailed',
        ]);

        $balanceSheet = $this->balanceSheetService->generate(
            $validated['as_of_date'],
            $validated['fiscal_period_id'],
            $validated['detail_level'] ?? 'summary'
        );

        return $this->balanceSheetService->exportToPdf($balanceSheet, $validated);
    }
}

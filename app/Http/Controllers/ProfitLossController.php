<?php

namespace App\Http\Controllers;

use App\Models\FiscalPeriod;
use App\Services\ProfitLossService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProfitLossController extends Controller
{
    public function __construct(
        private ProfitLossService $profitLossService
    ) {}

    public function index()
    {
        $fiscalPeriods = FiscalPeriod::orderBy('start_date', 'desc')->get();
        
        return Inertia::render('Reports/ProfitLoss', [
            'fiscalPeriods' => $fiscalPeriods,
        ]);
    }

    public function generate(Request $request)
    {
        $validated = $request->validate([
            'fiscal_period_id' => 'required|exists:fiscal_periods,id',
            'from_date' => 'required|date',
            'to_date' => 'required|date|after_or_equal:from_date',
            'detail_level' => 'in:summary,detailed',
        ]);

        $profitLoss = $this->profitLossService->generate(
            $validated['fiscal_period_id'],
            $validated['from_date'],
            $validated['to_date'],
            $validated['detail_level'] ?? 'summary'
        );

        return response()->json($profitLoss);
    }

    public function exportExcel(Request $request)
    {
        $validated = $request->validate([
            'fiscal_period_id' => 'required|exists:fiscal_periods,id',
            'from_date' => 'required|date',
            'to_date' => 'required|date|after_or_equal:from_date',
            'detail_level' => 'in:summary,detailed',
        ]);

        $profitLoss = $this->profitLossService->generate(
            $validated['fiscal_period_id'],
            $validated['from_date'],
            $validated['to_date'],
            $validated['detail_level'] ?? 'summary'
        );

        return $this->profitLossService->exportToExcel($profitLoss, $validated);
    }

    public function exportPdf(Request $request)
    {
        $validated = $request->validate([
            'fiscal_period_id' => 'required|exists:fiscal_periods,id',
            'from_date' => 'required|date',
            'to_date' => 'required|date|after_or_equal:from_date',
            'detail_level' => 'in:summary,detailed',
        ]);

        $profitLoss = $this->profitLossService->generate(
            $validated['fiscal_period_id'],
            $validated['from_date'],
            $validated['to_date'],
            $validated['detail_level'] ?? 'summary'
        );

        return $this->profitLossService->exportToPdf($profitLoss, $validated);
    }
}

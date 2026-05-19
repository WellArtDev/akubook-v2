<?php

namespace App\Http\Controllers;

use App\Models\FakturPajak;
use App\Models\TaxCalculation;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TaxReportingController extends Controller
{
    public function index(Request $request)
    {
        $validated = $request->validate([
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
            'tax_type' => ['nullable', 'in:ppn_out,ppn_in,withholding'],
        ]);

        $dateFrom = $validated['date_from'] ?? now()->startOfMonth()->toDateString();
        $dateTo = $validated['date_to'] ?? now()->endOfMonth()->toDateString();
        $taxType = $validated['tax_type'] ?? null;

        $outputRows = collect();
        if (!$taxType || $taxType === 'ppn_out') {
            $outputRows = FakturPajak::query()
                ->with('customer:id,name')
                ->where('status', 'issued')
                ->whereBetween('faktur_date', [$dateFrom, $dateTo])
                ->orderBy('faktur_date')
                ->get(['id', 'faktur_number', 'faktur_date', 'customer_id', 'dpp', 'ppn_amount', 'grand_total'])
                ->map(fn ($faktur) => [
                    'source' => 'faktur_pajak',
                    'number' => $faktur->faktur_number,
                    'date' => $faktur->faktur_date->toDateString(),
                    'party' => $faktur->customer?->name,
                    'tax_type' => 'ppn_out',
                    'dpp' => (float) $faktur->dpp,
                    'tax_amount' => (float) $faktur->ppn_amount,
                    'total' => (float) $faktur->grand_total,
                ]);
        }

        $calculationQuery = TaxCalculation::query()
            ->with('taxConfiguration:id,code,name')
            ->whereBetween('created_at', [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59']);

        if ($taxType) {
            $calculationQuery->where('tax_type', $taxType);
        } else {
            $calculationQuery->whereIn('tax_type', ['ppn_in', 'withholding']);
        }

        $calculationRows = $calculationQuery
            ->latest('created_at')
            ->get()
            ->map(fn ($calculation) => [
                'source' => 'tax_calculation',
                'number' => $calculation->taxConfiguration?->code ?? 'TAX-CALC',
                'date' => $calculation->created_at->toDateString(),
                'party' => $calculation->taxConfiguration?->name,
                'tax_type' => $calculation->tax_type,
                'dpp' => (float) $calculation->dpp,
                'tax_amount' => (float) $calculation->tax_amount,
                'total' => (float) $calculation->grand_total,
            ]);

        $rows = $outputRows->concat($calculationRows)->values();

        $summary = [
            'ppn_out' => $rows->where('tax_type', 'ppn_out')->sum('tax_amount'),
            'ppn_in' => $rows->where('tax_type', 'ppn_in')->sum('tax_amount'),
            'withholding' => $rows->where('tax_type', 'withholding')->sum('tax_amount'),
        ];
        $summary['net_vat'] = $summary['ppn_out'] - $summary['ppn_in'];

        return Inertia::render('TaxReports/Index', [
            'rows' => $rows,
            'summary' => $summary,
            'filters' => [
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
                'tax_type' => $taxType,
            ],
            'taxTypes' => [
                'ppn_out' => 'PPN Output',
                'ppn_in' => 'PPN Input',
                'withholding' => 'Withholding',
            ],
        ]);
    }
}

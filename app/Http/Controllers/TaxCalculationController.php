<?php

namespace App\Http\Controllers;

use App\Models\TaxCalculation;
use App\Models\TaxConfiguration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class TaxCalculationController extends Controller
{
    public function index(Request $request)
    {
        $taxType = $request->input('tax_type', 'ppn_out');
        $taxableAmount = (float) $request->input('taxable_amount', 0);
        $isInclusive = $request->boolean('is_inclusive');
        $configurationId = $request->input('tax_configuration_id');
        $result = null;

        if ($request->filled('taxable_amount')) {
            $request->validate([
                'tax_type' => ['required', 'in:ppn_out,ppn_in,withholding'],
                'tax_configuration_id' => ['nullable', 'exists:tax_configurations,id'],
                'taxable_amount' => ['required', 'numeric', 'min:0'],
                'is_inclusive' => ['nullable', 'boolean'],
            ]);

            $configuration = $this->resolveConfiguration($taxType, $configurationId);

            if ($configuration) {
                $result = $this->calculate($configuration, $taxableAmount, $isInclusive);

                TaxCalculation::query()->create([
                    'tax_configuration_id' => $configuration->id,
                    'tax_type' => $configuration->tax_type,
                    'taxable_amount' => $taxableAmount,
                    'is_inclusive' => $isInclusive,
                    'rate' => $configuration->rate,
                    'dpp' => $result['dpp'],
                    'tax_amount' => $result['tax_amount'],
                    'grand_total' => $result['grand_total'],
                    'created_by' => Auth::id(),
                ]);
            }
        }

        return Inertia::render('TaxCalculations/Index', [
            'taxConfigurations' => TaxConfiguration::query()
                ->where('is_active', true)
                ->orderBy('tax_type')
                ->orderByDesc('is_default')
                ->orderBy('name')
                ->get(['id', 'code', 'name', 'tax_type', 'rate', 'is_default']),
            'history' => TaxCalculation::query()
                ->with('taxConfiguration:id,code,name')
                ->latest()
                ->limit(20)
                ->get(),
            'filters' => [
                'tax_type' => $taxType,
                'tax_configuration_id' => $configurationId,
                'taxable_amount' => $request->input('taxable_amount', ''),
                'is_inclusive' => $isInclusive,
            ],
            'result' => $result,
            'taxTypes' => $this->taxTypes(),
        ]);
    }

    public function calculateApi(Request $request)
    {
        $validated = $request->validate([
            'tax_type' => ['required', 'in:ppn_out,ppn_in,withholding'],
            'tax_configuration_id' => ['nullable', 'exists:tax_configurations,id'],
            'taxable_amount' => ['required', 'numeric', 'min:0'],
            'is_inclusive' => ['nullable', 'boolean'],
        ]);

        $configuration = $this->resolveConfiguration($validated['tax_type'], $validated['tax_configuration_id'] ?? null);

        abort_if(! $configuration, 422, 'Tax configuration not found.');

        return response()->json($this->calculate($configuration, (float) $validated['taxable_amount'], $request->boolean('is_inclusive')));
    }

    private function resolveConfiguration(string $taxType, ?string $configurationId): ?TaxConfiguration
    {
        $query = TaxConfiguration::query()->where('is_active', true)->where('tax_type', $taxType);

        if ($configurationId) {
            return $query->whereKey($configurationId)->first();
        }

        return $query->orderByDesc('is_default')->orderBy('id')->first();
    }

    private function calculate(TaxConfiguration $configuration, float $taxableAmount, bool $isInclusive): array
    {
        $rate = (float) $configuration->rate;

        if ($isInclusive) {
            $dpp = round($taxableAmount / (1 + ($rate / 100)), 2);
            $taxAmount = round($taxableAmount - $dpp, 2);
            $grandTotal = round($taxableAmount, 2);
        } else {
            $dpp = round($taxableAmount, 2);
            $taxAmount = round($dpp * $rate / 100, 2);
            $grandTotal = round($dpp + $taxAmount, 2);
        }

        return [
            'tax_configuration_id' => $configuration->id,
            'tax_configuration_code' => $configuration->code,
            'tax_configuration_name' => $configuration->name,
            'tax_type' => $configuration->tax_type,
            'rate' => $rate,
            'dpp' => $dpp,
            'tax_amount' => $taxAmount,
            'grand_total' => $grandTotal,
            'is_inclusive' => $isInclusive,
        ];
    }

    private function taxTypes(): array
    {
        return [
            'ppn_out' => 'PPN Output',
            'ppn_in' => 'PPN Input',
            'withholding' => 'Withholding',
        ];
    }
}

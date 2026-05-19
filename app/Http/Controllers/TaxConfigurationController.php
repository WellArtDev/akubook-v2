<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\TaxConfiguration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class TaxConfigurationController extends Controller
{
    public function index(Request $request)
    {
        $taxConfigurations = TaxConfiguration::query()
            ->with('account')
            ->when($request->filled('tax_type'), fn ($query) => $query->where('tax_type', $request->tax_type))
            ->when($request->filled('is_active'), fn ($query) => $query->where('is_active', (bool) $request->boolean('is_active')))
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = str_replace(['%', '_'], ['\\%', '\\_'], $request->search);
                $query->where(function ($sub) use ($search) {
                    $sub->where('code', 'like', "%{$search}%")
                        ->orWhere('name', 'like', "%{$search}%");
                });
            })
            ->orderBy('tax_type')
            ->orderBy('code')
            ->paginate(50)
            ->withQueryString();

        return Inertia::render('TaxConfigurations/Index', [
            'taxConfigurations' => $taxConfigurations,
            'filters' => $request->only(['tax_type', 'is_active', 'search']),
            'taxTypes' => $this->taxTypes(),
        ]);
    }

    public function create()
    {
        return Inertia::render('TaxConfigurations/Create', $this->formData());
    }

    public function store(Request $request)
    {
        $validated = $this->validateTaxConfiguration($request);

        $taxConfiguration = DB::transaction(function () use ($validated) {
            if ($validated['is_default'] ?? false) {
                $this->clearDefault($validated['tax_type']);
            }

            return TaxConfiguration::query()->create($validated + ['created_by' => Auth::id()]);
        });

        return redirect()->route('tax-configurations.show', $taxConfiguration)->with('success', 'Tax configuration created.');
    }

    public function show(TaxConfiguration $taxConfiguration)
    {
        $taxConfiguration->load(['account', 'creator', 'updater']);

        return Inertia::render('TaxConfigurations/Show', [
            'taxConfiguration' => $taxConfiguration,
        ]);
    }

    public function edit(TaxConfiguration $taxConfiguration)
    {
        return Inertia::render('TaxConfigurations/Edit', $this->formData() + [
            'taxConfiguration' => $taxConfiguration->load('account'),
        ]);
    }

    public function update(Request $request, TaxConfiguration $taxConfiguration)
    {
        $validated = $this->validateTaxConfiguration($request, $taxConfiguration);

        DB::transaction(function () use ($taxConfiguration, $validated) {
            if ($validated['is_default'] ?? false) {
                $this->clearDefault($validated['tax_type'], $taxConfiguration->id);
            }

            $taxConfiguration->update($validated + ['updated_by' => Auth::id()]);
        });

        return redirect()->route('tax-configurations.show', $taxConfiguration)->with('success', 'Tax configuration updated.');
    }

    public function destroy(TaxConfiguration $taxConfiguration)
    {
        $taxConfiguration->delete();

        return redirect()->route('tax-configurations.index')->with('success', 'Tax configuration deleted.');
    }

    private function validateTaxConfiguration(Request $request, ?TaxConfiguration $taxConfiguration = null): array
    {
        return $request->validate([
            'code' => ['required', 'string', 'max:50', Rule::unique('tax_configurations', 'code')->ignore($taxConfiguration?->id)],
            'name' => ['required', 'string', 'max:255'],
            'tax_type' => ['required', Rule::in(array_keys($this->taxTypes()))],
            'rate' => ['required', 'numeric', 'min:0', 'max:100'],
            'account_id' => ['required', 'exists:accounts,id'],
            'is_default' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
            'description' => ['nullable', 'string'],
        ]);
    }

    private function clearDefault(string $taxType, ?int $exceptId = null): void
    {
        TaxConfiguration::query()
            ->where('tax_type', $taxType)
            ->when($exceptId, fn ($query) => $query->whereKeyNot($exceptId))
            ->update(['is_default' => false]);
    }

    private function formData(): array
    {
        return [
            'accounts' => Account::query()
                ->where('is_active', true)
                ->where('is_header', false)
                ->orderBy('code')
                ->get(['id', 'code', 'name', 'type', 'category']),
            'taxTypes' => $this->taxTypes(),
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

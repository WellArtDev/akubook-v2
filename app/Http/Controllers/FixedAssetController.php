<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\FixedAsset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class FixedAssetController extends Controller
{
    public function index(Request $request)
    {
        $query = FixedAsset::query()
            ->with(['assetAccount:id,code,name'])
            ->latest('acquisition_date');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = '%' . str_replace(['%', '_'], ['\\%', '\\_'], $request->search) . '%';
            $query->where(function ($q) use ($search) {
                $q->where('asset_code', 'like', $search)
                    ->orWhere('name', 'like', $search)
                    ->orWhere('category', 'like', $search);
            });
        }

        return Inertia::render('FixedAssets/Index', [
            'assets' => $query->paginate(50)->withQueryString(),
            'filters' => $request->only(['status', 'search']),
            'statuses' => ['active', 'inactive', 'disposed'],
        ]);
    }

    public function create()
    {
        return Inertia::render('FixedAssets/Create', [
            'accounts' => $this->availableAccounts(),
            'statuses' => ['active', 'inactive', 'disposed'],
        ]);
    }

    public function store(Request $request)
    {
        $validated = $this->validateAsset($request);
        $validated['asset_code'] = $validated['asset_code'] ?: FixedAsset::generateCode();
        $validated['created_by'] = Auth::id();

        $asset = FixedAsset::query()->create($validated);

        return redirect()->route('fixed-assets.show', $asset)->with('success', 'Fixed asset created.');
    }

    public function show(FixedAsset $fixedAsset)
    {
        return Inertia::render('FixedAssets/Show', [
            'asset' => $fixedAsset->load([
                'assetAccount:id,code,name',
                'accumulatedDepreciationAccount:id,code,name',
                'depreciationExpenseAccount:id,code,name',
            ]),
        ]);
    }

    public function edit(FixedAsset $fixedAsset)
    {
        return Inertia::render('FixedAssets/Edit', [
            'asset' => $fixedAsset,
            'accounts' => $this->availableAccounts(),
            'statuses' => ['active', 'inactive', 'disposed'],
        ]);
    }

    public function update(Request $request, FixedAsset $fixedAsset)
    {
        $validated = $this->validateAsset($request, $fixedAsset->id);
        $validated['updated_by'] = Auth::id();
        $fixedAsset->update($validated);

        return redirect()->route('fixed-assets.show', $fixedAsset)->with('success', 'Fixed asset updated.');
    }

    public function destroy(FixedAsset $fixedAsset)
    {
        $fixedAsset->delete();

        return redirect()->route('fixed-assets.index')->with('success', 'Fixed asset deleted.');
    }

    private function validateAsset(Request $request, ?int $id = null): array
    {
        return $request->validate([
            'asset_code' => ['nullable', 'string', 'max:50', 'unique:fixed_assets,asset_code,' . ($id ?? 'NULL') . ',id,deleted_at,NULL'],
            'name' => ['required', 'string', 'max:255'],
            'category' => ['nullable', 'string', 'max:100'],
            'acquisition_date' => ['required', 'date'],
            'acquisition_cost' => ['required', 'numeric', 'gt:0'],
            'useful_life_months' => ['required', 'integer', 'min:1'],
            'residual_value' => ['required', 'numeric', 'min:0'],
            'status' => ['required', 'in:active,inactive,disposed'],
            'asset_account_id' => ['required', 'exists:accounts,id'],
            'accumulated_depreciation_account_id' => ['required', 'exists:accounts,id'],
            'depreciation_expense_account_id' => ['required', 'exists:accounts,id'],
            'notes' => ['nullable', 'string'],
        ]);
    }

    private function availableAccounts()
    {
        return Account::query()
            ->where('is_active', true)
            ->where('is_header', false)
            ->orderBy('code')
            ->get(['id', 'code', 'name']);
    }
}

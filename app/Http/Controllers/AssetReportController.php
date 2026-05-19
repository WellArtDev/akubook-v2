<?php

namespace App\Http\Controllers;

use App\Models\AssetDepreciation;
use App\Models\AssetDisposal;
use App\Models\FixedAsset;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AssetReportController extends Controller
{
    public function index(Request $request)
    {
        $validated = $request->validate([
            'status' => ['nullable', 'in:active,inactive,disposed'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
            'search' => ['nullable', 'string', 'max:100'],
        ]);

        $assetsQuery = FixedAsset::query()
            ->with(['assetAccount'])
            ->when($validated['status'] ?? null, fn ($q, $status) => $q->where('status', $status))
            ->when($validated['date_from'] ?? null, fn ($q, $date) => $q->whereDate('acquisition_date', '>=', $date))
            ->when($validated['date_to'] ?? null, fn ($q, $date) => $q->whereDate('acquisition_date', '<=', $date))
            ->when($validated['search'] ?? null, function ($q, $search) {
                $q->where(function ($qq) use ($search) {
                    $qq->where('asset_code', 'like', "%{$search}%")
                        ->orWhere('name', 'like', "%{$search}%")
                        ->orWhere('category', 'like', "%{$search}%");
                });
            })
            ->orderBy('asset_code');

        $assets = $assetsQuery->get();

        $assetIds = $assets->pluck('id');
        $latestDepreciations = AssetDepreciation::query()
            ->whereIn('fixed_asset_id', $assetIds)
            ->orderByDesc('period')
            ->get()
            ->groupBy('fixed_asset_id')
            ->map(fn ($rows) => $rows->first());

        $assetRows = $assets->map(function (FixedAsset $asset) use ($latestDepreciations) {
            $depreciation = $latestDepreciations->get($asset->id);
            $accumulated = $depreciation?->accumulated_depreciation ?? 0;
            $bookValue = max((float) $asset->acquisition_cost - (float) $accumulated, 0);

            return [
                'id' => $asset->id,
                'asset_code' => $asset->asset_code,
                'name' => $asset->name,
                'category' => $asset->category,
                'acquisition_date' => optional($asset->acquisition_date)->format('Y-m-d'),
                'acquisition_cost' => (float) $asset->acquisition_cost,
                'accumulated_depreciation' => (float) $accumulated,
                'book_value' => (float) $bookValue,
                'status' => $asset->status,
            ];
        })->values();

        $disposals = AssetDisposal::query()
            ->with('fixedAsset:id,asset_code,name')
            ->when($validated['date_from'] ?? null, fn ($q, $date) => $q->whereDate('disposal_date', '>=', $date))
            ->when($validated['date_to'] ?? null, fn ($q, $date) => $q->whereDate('disposal_date', '<=', $date))
            ->when($validated['search'] ?? null, function ($q, $search) {
                $q->where(function ($qq) use ($search) {
                    $qq->where('disposal_number', 'like', "%{$search}%")
                        ->orWhereHas('fixedAsset', fn ($f) => $f->where('asset_code', 'like', "%{$search}%")->orWhere('name', 'like', "%{$search}%"));
                });
            })
            ->orderByDesc('disposal_date')
            ->get()
            ->map(fn (AssetDisposal $d) => [
                'id' => $d->id,
                'disposal_number' => $d->disposal_number,
                'disposal_date' => optional($d->disposal_date)->format('Y-m-d'),
                'asset_code' => $d->fixedAsset?->asset_code,
                'asset_name' => $d->fixedAsset?->name,
                'book_value' => (float) $d->book_value,
                'proceeds_amount' => (float) $d->proceeds_amount,
                'status' => $d->status,
            ])
            ->values();

        $summary = [
            'total_acquisition_cost' => (float) $assetRows->sum('acquisition_cost'),
            'total_accumulated_depreciation' => (float) $assetRows->sum('accumulated_depreciation'),
            'total_book_value' => (float) $assetRows->sum('book_value'),
            'total_disposal_proceeds' => (float) $disposals->sum('proceeds_amount'),
        ];

        return Inertia::render('AssetReports/Index', [
            'assets' => $assetRows,
            'disposals' => $disposals,
            'summary' => $summary,
            'filters' => [
                'status' => $validated['status'] ?? '',
                'date_from' => $validated['date_from'] ?? '',
                'date_to' => $validated['date_to'] ?? '',
                'search' => $validated['search'] ?? '',
            ],
        ]);
    }
}

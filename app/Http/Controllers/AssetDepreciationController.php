<?php

namespace App\Http\Controllers;

use App\Models\AssetDepreciation;
use App\Models\FixedAsset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class AssetDepreciationController extends Controller
{
    public function index(Request $request)
    {
        $period = $request->get('period', now()->format('Y-m'));

        if ($request->has('run')) {
            $request->validate([
                'period' => ['required', 'date_format:Y-m'],
            ]);
            $period = $request->period;
            $this->runDepreciation($period);
        }

        $rows = AssetDepreciation::query()
            ->with('fixedAsset:id,asset_code,name')
            ->where('period', $period)
            ->orderBy('fixed_asset_id')
            ->get()
            ->map(fn ($row) => [
                'id' => $row->id,
                'asset_code' => $row->fixedAsset?->asset_code,
                'asset_name' => $row->fixedAsset?->name,
                'monthly_depreciation' => (float) $row->monthly_depreciation,
                'accumulated_depreciation' => (float) $row->accumulated_depreciation,
                'book_value_end' => (float) $row->book_value_end,
            ]);

        return Inertia::render('AssetDepreciations/Index', [
            'period' => $period,
            'rows' => $rows,
            'summary' => [
                'total_monthly_depreciation' => $rows->sum('monthly_depreciation'),
                'total_accumulated_depreciation' => $rows->sum('accumulated_depreciation'),
                'total_book_value_end' => $rows->sum('book_value_end'),
            ],
        ]);
    }

    private function runDepreciation(string $period): void
    {
        $targetDate = now()->createFromFormat('Y-m', $period)->startOfMonth();

        DB::transaction(function () use ($period, $targetDate) {
            $assets = FixedAsset::query()
                ->where('status', 'active')
                ->whereDate('acquisition_date', '<=', $targetDate->toDateString())
                ->get();

            foreach ($assets as $asset) {
                $base = (float) $asset->acquisition_cost - (float) $asset->residual_value;
                if ($base <= 0 || (int) $asset->useful_life_months <= 0) {
                    continue;
                }

                $monthly = round($base / (int) $asset->useful_life_months, 2);
                $monthsElapsed = max(1, (($targetDate->year - $asset->acquisition_date->year) * 12) + ($targetDate->month - $asset->acquisition_date->month) + 1);
                $accumulated = min(round($monthly * $monthsElapsed, 2), $base);
                $bookValueEnd = round((float) $asset->acquisition_cost - $accumulated, 2);

                AssetDepreciation::query()->updateOrCreate(
                    ['fixed_asset_id' => $asset->id, 'period' => $period],
                    [
                        'monthly_depreciation' => $monthly,
                        'accumulated_depreciation' => $accumulated,
                        'book_value_end' => $bookValueEnd,
                        'created_by' => Auth::id(),
                    ]
                );
            }
        });
    }
}

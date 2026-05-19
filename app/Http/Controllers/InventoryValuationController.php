<?php

namespace App\Http\Controllers;

use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class InventoryValuationController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->input('search', ''));

        $items = Item::query()
            ->where('is_active', true)
            ->when($search !== '', function ($q) use ($search) {
                $escaped = str_replace(['%', '_'], ['\\%', '\\_'], $search);
                $q->where(function ($sub) use ($escaped) {
                    $sub->where('code', 'like', "%{$escaped}%")
                        ->orWhere('name', 'like', "%{$escaped}%");
                });
            })
            ->orderBy('code')
            ->get(['id', 'code', 'name', 'valuation_method', 'purchase_price']);

        $stockMap = DB::table('stock_transactions')
            ->select('item_id', DB::raw('COALESCE(SUM(quantity_in - quantity_out), 0) as current_stock'))
            ->groupBy('item_id')
            ->pluck('current_stock', 'item_id');

        $valuations = $items->map(function (Item $item) use ($stockMap) {
            $currentStock = (float) ($stockMap[$item->id] ?? 0);
            $averageCost = (float) ($item->purchase_price ?? 0);
            $inventoryValue = $currentStock * $averageCost;

            return [
                'id' => $item->id,
                'code' => $item->code,
                'name' => $item->name,
                'valuation_method' => $item->valuation_method,
                'current_stock' => round($currentStock, 3),
                'average_cost' => round($averageCost, 2),
                'inventory_value' => round($inventoryValue, 2),
            ];
        });

        return Inertia::render('InventoryValuations/Index', [
            'valuations' => $valuations->values(),
            'totalValue' => round((float) $valuations->sum('inventory_value'), 2),
            'filters' => [
                'search' => $search,
            ],
        ]);
    }
}

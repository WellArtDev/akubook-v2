<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\StockOpname;
use App\Models\StockOpnameLine;
use App\Models\StockTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class StockOpnameController extends Controller
{
    public function index(Request $request)
    {
        $query = StockOpname::with(['creator', 'confirmer'])->orderByDesc('opname_date')->orderByDesc('id');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        return Inertia::render('StockOpnames/Index', [
            'opnames' => $query->paginate(50)->withQueryString(),
            'filters' => $request->only(['status']),
        ]);
    }

    public function create()
    {
        $items = Item::where('is_active', true)->orderBy('code')->get(['id', 'code', 'name']);

        $stocks = DB::table('stock_transactions')
            ->select('item_id')
            ->selectRaw('COALESCE(SUM(quantity_in - quantity_out), 0) as current_stock')
            ->groupBy('item_id')
            ->pluck('current_stock', 'item_id');

        $lines = $items->map(fn ($item) => [
            'item_id' => $item->id,
            'item_code' => $item->code,
            'item_name' => $item->name,
            'system_quantity' => (float) ($stocks[$item->id] ?? 0),
            'physical_quantity' => (float) ($stocks[$item->id] ?? 0),
            'notes' => null,
        ])->values();

        return Inertia::render('StockOpnames/Create', [
            'lines' => $lines,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'opname_date' => 'required|date',
            'notes' => 'nullable|string',
            'lines' => 'required|array|min:1',
            'lines.*.item_id' => 'required|exists:items,id',
            'lines.*.system_quantity' => 'required|numeric|min:0',
            'lines.*.physical_quantity' => 'required|numeric|min:0',
            'lines.*.notes' => 'nullable|string',
        ]);

        $opname = DB::transaction(function () use ($validated) {
            $opname = StockOpname::create([
                'opname_number' => StockOpname::generateNumber(),
                'opname_date' => $validated['opname_date'],
                'status' => 'draft',
                'notes' => $validated['notes'] ?? null,
                'created_by' => Auth::id(),
            ]);

            foreach ($validated['lines'] as $line) {
                StockOpnameLine::create([
                    'stock_opname_id' => $opname->id,
                    'item_id' => $line['item_id'],
                    'system_quantity' => $line['system_quantity'],
                    'physical_quantity' => $line['physical_quantity'],
                    'variance_quantity' => (float) $line['physical_quantity'] - (float) $line['system_quantity'],
                    'notes' => $line['notes'] ?? null,
                ]);
            }

            return $opname;
        });

        return redirect()->route('stock-opnames.show', $opname)->with('success', 'Stock opname created.');
    }

    public function show(StockOpname $stockOpname)
    {
        $stockOpname->load(['lines.item', 'creator', 'confirmer']);

        return Inertia::render('StockOpnames/Show', [
            'opname' => $stockOpname,
        ]);
    }

    public function confirm(StockOpname $stockOpname)
    {
        if ($stockOpname->status !== 'draft') {
            return back()->withErrors(['error' => 'Only draft opname can be confirmed.']);
        }

        DB::transaction(function () use ($stockOpname) {
            $stockOpname->load('lines');

            foreach ($stockOpname->lines as $line) {
                if ((float) $line->variance_quantity === 0.0) {
                    continue;
                }

                StockTransaction::create([
                    'item_id' => $line->item_id,
                    'movement_type' => 'adjustment',
                    'quantity_in' => $line->variance_quantity > 0 ? abs((float) $line->variance_quantity) : 0,
                    'quantity_out' => $line->variance_quantity < 0 ? abs((float) $line->variance_quantity) : 0,
                    'movement_date' => $stockOpname->opname_date,
                    'reference_type' => 'stock_opname',
                    'reference_id' => $stockOpname->id,
                    'notes' => $line->notes,
                    'created_by' => Auth::id(),
                ]);
            }

            $stockOpname->update([
                'status' => 'confirmed',
                'confirmed_by' => Auth::id(),
                'confirmed_at' => now(),
            ]);
        });

        return redirect()->route('stock-opnames.show', $stockOpname)->with('success', 'Stock opname confirmed.');
    }
}

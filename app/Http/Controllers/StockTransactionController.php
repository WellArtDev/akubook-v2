<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\StockTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class StockTransactionController extends Controller
{
    public function index(Request $request)
    {
        $query = StockTransaction::with(['item', 'creator'])->orderByDesc('movement_date')->orderByDesc('id');

        if ($request->filled('item_id')) {
            $query->where('item_id', $request->item_id);
        }

        if ($request->filled('movement_type')) {
            $query->where('movement_type', $request->movement_type);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('movement_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('movement_date', '<=', $request->date_to);
        }

        $balances = Item::query()
            ->select('items.id', 'items.code', 'items.name')
            ->selectRaw('COALESCE(SUM(stock_transactions.quantity_in - stock_transactions.quantity_out), 0) as current_stock')
            ->leftJoin('stock_transactions', 'stock_transactions.item_id', '=', 'items.id')
            ->groupBy('items.id', 'items.code', 'items.name')
            ->orderBy('items.code')
            ->get();

        return Inertia::render('StockTransactions/Index', [
            'transactions' => $query->paginate(50)->withQueryString(),
            'balances' => $balances,
            'items' => Item::orderBy('code')->get(['id', 'code', 'name']),
            'movementTypes' => StockTransaction::MOVEMENT_TYPES,
            'filters' => $request->only(['item_id', 'movement_type', 'date_from', 'date_to']),
        ]);
    }

    public function create()
    {
        return Inertia::render('StockTransactions/Create', [
            'items' => Item::where('is_active', true)->orderBy('code')->get(['id', 'code', 'name', 'unit']),
            'movementTypes' => StockTransaction::MOVEMENT_TYPES,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'item_id' => 'required|exists:items,id',
            'movement_type' => 'required|in:' . implode(',', StockTransaction::MOVEMENT_TYPES),
            'quantity' => 'required|numeric|min:0.001',
            'direction' => 'required|in:in,out',
            'movement_date' => 'required|date',
            'reference_type' => 'nullable|string|max:100',
            'reference_id' => 'nullable|integer|min:1',
            'notes' => 'nullable|string',
        ]);

        StockTransaction::create([
            'item_id' => $validated['item_id'],
            'movement_type' => $validated['movement_type'],
            'quantity_in' => $validated['direction'] === 'in' ? $validated['quantity'] : 0,
            'quantity_out' => $validated['direction'] === 'out' ? $validated['quantity'] : 0,
            'movement_date' => $validated['movement_date'],
            'reference_type' => $validated['reference_type'] ?? null,
            'reference_id' => $validated['reference_id'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'created_by' => Auth::id(),
        ]);

        return redirect()->route('stock-transactions.index')->with('success', 'Stock movement recorded.');
    }
}

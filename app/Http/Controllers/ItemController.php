<?php

namespace App\Http\Controllers;

use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class ItemController extends Controller
{
    public function index(Request $request)
    {
        $query = Item::query()->orderBy('code');

        if ($request->filled('search')) {
            $search = str_replace(['%', '_'], ['\%', '\_'], $request->search);
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%")
                    ->orWhere('category', 'like', "%{$search}%");
            });
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        if ($request->filled('inventory_type')) {
            $query->where('inventory_type', $request->inventory_type);
        }

        return Inertia::render('Items/Index', [
            'items' => $query->paginate(50)->withQueryString(),
            'filters' => $request->only(['search', 'is_active', 'inventory_type']),
        ]);
    }

    public function create()
    {
        return Inertia::render('Items/Create', [
            'options' => $this->options(),
        ]);
    }

    public function store(Request $request)
    {
        $item = Item::create($this->validated($request));

        return redirect()->route('items.show', $item)->with('success', 'Item created successfully.');
    }

    public function show(Item $item)
    {
        return Inertia::render('Items/Show', [
            'item' => $item,
            'usage' => $this->usage($item),
        ]);
    }

    public function edit(Item $item)
    {
        return Inertia::render('Items/Edit', [
            'item' => $item,
            'options' => $this->options(),
        ]);
    }

    public function update(Request $request, Item $item)
    {
        $item->update($this->validated($request, $item));

        return redirect()->route('items.show', $item)->with('success', 'Item updated successfully.');
    }

    public function destroy(Item $item)
    {
        $item->delete();

        return redirect()->route('items.index')->with('success', 'Item deleted successfully.');
    }

    private function validated(Request $request, ?Item $item = null): array
    {
        return $request->validate([
            'code' => ['required', 'string', 'max:50', Rule::unique('items', 'code')->ignore($item?->id)],
            'name' => 'required|string|max:255',
            'category' => 'nullable|string|max:100',
            'description' => 'nullable|string',
            'item_type' => 'required|in:goods,service',
            'inventory_type' => 'required|in:stock,non_stock',
            'valuation_method' => 'required|in:moving_average,fifo,standard',
            'unit' => 'required|string|max:20',
            'purchase_price' => 'required|numeric|min:0',
            'selling_price' => 'required|numeric|min:0',
            'minimum_stock' => 'required|numeric|min:0',
            'reorder_point' => 'required|numeric|min:0',
            'is_active' => 'boolean',
        ]);
    }

    private function options(): array
    {
        return [
            'itemTypes' => ['goods', 'service'],
            'inventoryTypes' => ['stock', 'non_stock'],
            'valuationMethods' => ['moving_average', 'fifo', 'standard'],
        ];
    }

    private function usage(Item $item): array
    {
        return [
            'sales_order_lines' => $this->countTable('sales_order_lines', 'item_id', $item->id),
            'sales_quotation_lines' => $this->countTable('sales_quotation_lines', 'item_id', $item->id),
            'purchase_order_lines' => $this->countTable('purchase_order_lines', 'item_id', $item->id),
            'goods_receipt_lines' => $this->countTable('goods_receipt_lines', 'item_id', $item->id),
        ];
    }

    private function countTable(string $table, string $column, int $id): int
    {
        if (!\Schema::hasTable($table) || !\Schema::hasColumn($table, $column)) {
            return 0;
        }

        return \DB::table($table)->where($column, $id)->count();
    }
}

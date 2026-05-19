<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Item;
use App\Models\StockTransaction;
use App\Models\StockTransfer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class StockTransferController extends Controller
{
    public function index(Request $request): Response
    {
        $transfers = StockTransfer::query()
            ->with(['fromBranch:id,name', 'toBranch:id,name', 'creator:id,name'])
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')->toString()))
            ->when($request->filled('branch_id'), function ($query) use ($request) {
                $branchId = (int) $request->integer('branch_id');
                $query->where(function ($inner) use ($branchId) {
                    $inner->where('from_branch_id', $branchId)
                        ->orWhere('to_branch_id', $branchId);
                });
            })
            ->when($request->filled('date_from'), fn ($query) => $query->whereDate('transfer_date', '>=', $request->string('date_from')->toString()))
            ->when($request->filled('date_to'), fn ($query) => $query->whereDate('transfer_date', '<=', $request->string('date_to')->toString()))
            ->when($request->filled('search'), fn ($query) => $query->where('transfer_number', 'like', '%'.$request->string('search')->toString().'%'))
            ->orderByDesc('transfer_date')
            ->orderByDesc('id')
            ->paginate(50)
            ->withQueryString();

        $branches = Branch::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name']);

        return Inertia::render('StockTransfers/Index', [
            'transfers' => $transfers,
            'branches' => $branches,
            'filters' => $request->only(['status', 'branch_id', 'date_from', 'date_to', 'search']),
        ]);
    }

    public function create(): Response
    {
        $branches = Branch::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name']);

        $items = Item::query()
            ->where('is_active', true)
            ->orderBy('code')
            ->get(['id', 'code', 'name', 'unit']);

        return Inertia::render('StockTransfers/Create', [
            'branches' => $branches,
            'items' => $items,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'transfer_date' => ['required', 'date'],
            'from_branch_id' => ['required', 'exists:branches,id', 'different:to_branch_id'],
            'to_branch_id' => ['required', 'exists:branches,id', 'different:from_branch_id'],
            'notes' => ['nullable', 'string'],
            'lines' => ['required', 'array', 'min:1'],
            'lines.*.item_id' => ['required', 'exists:items,id'],
            'lines.*.quantity' => ['required', 'numeric', 'min:0.001'],
            'lines.*.notes' => ['nullable', 'string'],
        ]);

        DB::transaction(function () use ($validated) {
            $transfer = StockTransfer::create([
                'transfer_number' => StockTransfer::generateNumber(),
                'transfer_date' => $validated['transfer_date'],
                'from_branch_id' => $validated['from_branch_id'],
                'to_branch_id' => $validated['to_branch_id'],
                'status' => 'draft',
                'notes' => $validated['notes'] ?? null,
                'created_by' => Auth::id(),
            ]);

            foreach ($validated['lines'] as $index => $line) {
                $item = Item::query()->findOrFail($line['item_id']);

                $transfer->lines()->create([
                    'item_id' => $line['item_id'],
                    'line_number' => $index + 1,
                    'quantity' => $line['quantity'],
                    'unit' => $item->unit,
                    'notes' => $line['notes'] ?? null,
                ]);
            }
        });

        return redirect()->route('stock-transfers.index')
            ->with('success', 'Transfer stok berhasil dibuat.');
    }

    public function show(StockTransfer $stockTransfer): Response
    {
        $stockTransfer->load([
            'fromBranch:id,name',
            'toBranch:id,name',
            'creator:id,name',
            'confirmer:id,name',
            'lines.item:id,code,name,unit',
        ]);

        return Inertia::render('StockTransfers/Show', [
            'transfer' => $stockTransfer,
        ]);
    }

    public function confirm(StockTransfer $stockTransfer): RedirectResponse
    {
        if ($stockTransfer->status !== 'draft') {
            return back()->withErrors(['error' => 'Hanya transfer draft yang bisa dikonfirmasi.']);
        }

        $stockTransfer->load('lines');

        foreach ($stockTransfer->lines as $line) {
            $sourceStock = (float) StockTransaction::query()
                ->where('item_id', $line->item_id)
                ->where('branch_id', $stockTransfer->from_branch_id)
                ->selectRaw('COALESCE(SUM(quantity_in - quantity_out), 0) as stock')
                ->value('stock');

            if ($sourceStock < (float) $line->quantity) {
                return back()->withErrors([
                    'error' => "Stok item ID {$line->item_id} di lokasi asal tidak cukup.",
                ]);
            }
        }

        DB::transaction(function () use ($stockTransfer) {
            foreach ($stockTransfer->lines as $line) {
                StockTransaction::create([
                    'item_id' => $line->item_id,
                    'branch_id' => $stockTransfer->from_branch_id,
                    'movement_type' => 'transfer_out',
                    'quantity_in' => 0,
                    'quantity_out' => $line->quantity,
                    'movement_date' => $stockTransfer->transfer_date,
                    'reference_type' => 'stock_transfer',
                    'reference_id' => $stockTransfer->id,
                    'notes' => $line->notes,
                    'created_by' => Auth::id(),
                ]);

                StockTransaction::create([
                    'item_id' => $line->item_id,
                    'branch_id' => $stockTransfer->to_branch_id,
                    'movement_type' => 'transfer_in',
                    'quantity_in' => $line->quantity,
                    'quantity_out' => 0,
                    'movement_date' => $stockTransfer->transfer_date,
                    'reference_type' => 'stock_transfer',
                    'reference_id' => $stockTransfer->id,
                    'notes' => $line->notes,
                    'created_by' => Auth::id(),
                ]);
            }

            $stockTransfer->update([
                'status' => 'confirmed',
                'confirmed_by' => Auth::id(),
                'confirmed_at' => now(),
            ]);
        });

        return redirect()->route('stock-transfers.show', $stockTransfer)
            ->with('success', 'Transfer stok berhasil dikonfirmasi.');
    }
}

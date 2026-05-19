<?php

namespace App\Http\Controllers;

use App\Models\GoodsReceipt;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderLine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class GoodsReceiptController extends Controller
{
    public function index(Request $request)
    {
        $query = GoodsReceipt::with(['purchaseOrder', 'supplier'])
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->when($request->filled('supplier_id'), fn ($q) => $q->where('supplier_id', $request->supplier_id))
            ->when($request->filled('date_from'), fn ($q) => $q->whereDate('gr_date', '>=', $request->date_from))
            ->when($request->filled('date_to'), fn ($q) => $q->whereDate('gr_date', '<=', $request->date_to))
            ->when($request->filled('search'), function ($q) use ($request) {
                $q->where(function ($sub) use ($request) {
                    $sub->where('gr_number', 'ilike', '%' . $request->search . '%')
                        ->orWhereHas('purchaseOrder', fn ($po) => $po->where('po_number', 'ilike', '%' . $request->search . '%'));
                });
            });

        return Inertia::render('GoodsReceipts/Index', [
            'goodsReceipts' => $query->orderByDesc('gr_date')->paginate(50)->withQueryString(),
            'filters' => $request->only(['status', 'supplier_id', 'date_from', 'date_to', 'search']),
        ]);
    }

    public function create(Request $request)
    {
        $purchaseOrder = $request->filled('purchase_order_id')
            ? PurchaseOrder::with(['supplier', 'deliveryAddress', 'lines'])->findOrFail($request->purchase_order_id)
            : null;

        return Inertia::render('GoodsReceipts/Create', [
            'purchaseOrders' => $this->availablePurchaseOrders(),
            'purchaseOrder' => $purchaseOrder,
            'lines' => $purchaseOrder ? $this->availableLines($purchaseOrder) : [],
        ]);
    }

    public function store(Request $request)
    {
        $validated = $this->validateGoodsReceipt($request);
        $purchaseOrder = PurchaseOrder::with('lines')->findOrFail($validated['purchase_order_id']);
        $this->ensurePurchaseOrderReceivable($purchaseOrder);

        $goodsReceipt = DB::transaction(function () use ($validated, $purchaseOrder) {
            $goodsReceipt = GoodsReceipt::create([
                'gr_number' => GoodsReceipt::generateNumber(),
                'gr_date' => $validated['gr_date'],
                'purchase_order_id' => $purchaseOrder->id,
                'supplier_id' => $purchaseOrder->supplier_id,
                'receive_location_id' => $purchaseOrder->delivery_address_id,
                'status' => 'draft',
                'reference_number' => $validated['reference_number'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'created_by' => Auth::id(),
            ]);

            $this->syncLines($goodsReceipt, $validated['lines']);

            return $goodsReceipt;
        });

        return redirect()->route('goods-receipts.show', $goodsReceipt)->with('success', 'Goods receipt created.');
    }

    public function show(GoodsReceipt $goodsReceipt)
    {
        $goodsReceipt->load(['purchaseOrder', 'supplier', 'receiveLocation', 'lines.purchaseOrderLine', 'createdBy', 'receivedBy', 'cancelledBy']);

        return Inertia::render('GoodsReceipts/Show', [
            'goodsReceipt' => $goodsReceipt,
        ]);
    }

    public function receive(Request $request, GoodsReceipt $goodsReceipt)
    {
        abort_unless($goodsReceipt->status === 'draft', 403, 'Only draft goods receipts can be received.');

        $validated = $request->validate([
            'received_at' => ['required', 'date'],
            'lines' => ['required', 'array', 'min:1'],
            'lines.*.id' => ['required', 'exists:goods_receipt_lines,id'],
            'lines.*.accepted_quantity' => ['required', 'numeric', 'min:0'],
            'lines.*.rejected_quantity' => ['required', 'numeric', 'min:0'],
            'lines.*.inspection_notes' => ['nullable', 'string'],
        ]);

        DB::transaction(function () use ($goodsReceipt, $validated) {
            $goodsReceipt->load(['lines.purchaseOrderLine', 'purchaseOrder.lines']);
            $submitted = collect($validated['lines'])->keyBy('id');

            foreach ($goodsReceipt->lines as $line) {
                $input = $submitted->get($line->id);
                abort_if(!$input, 422, 'Receipt line missing.');
                $accepted = (float) $input['accepted_quantity'];
                $rejected = (float) $input['rejected_quantity'];
                abort_if($accepted + $rejected > (float) $line->receipt_quantity, 422, 'Accepted plus rejected quantity exceeds receipt quantity.');

                $line->update([
                    'accepted_quantity' => $accepted,
                    'rejected_quantity' => $rejected,
                    'inspection_notes' => $input['inspection_notes'] ?? null,
                ]);

                $poLine = $line->purchaseOrderLine;
                $poLine->update([
                    'received_quantity' => (float) $poLine->received_quantity + $accepted,
                ]);
            }

            $purchaseOrder = $goodsReceipt->purchaseOrder()->with('lines')->first();
            $fullyReceived = $purchaseOrder->lines->every(fn ($line) => (float) $line->received_quantity >= (float) $line->quantity);
            $purchaseOrder->update(['status' => $fullyReceived ? 'completed' : 'in_progress']);

            $goodsReceipt->update([
                'status' => 'received',
                'received_by' => Auth::id(),
                'received_at' => $validated['received_at'],
                'updated_by' => Auth::id(),
            ]);
        });

        return back()->with('success', 'Goods receipt received.');
    }

    public function cancel(Request $request, GoodsReceipt $goodsReceipt)
    {
        abort_unless(in_array($goodsReceipt->status, ['draft', 'received'], true), 403, 'Goods receipt cannot be cancelled.');
        $validated = $request->validate(['reason' => ['required', 'string', 'max:1000']]);

        DB::transaction(function () use ($goodsReceipt, $validated) {
            if ($goodsReceipt->status === 'received') {
                $goodsReceipt->load(['lines.purchaseOrderLine', 'purchaseOrder.lines']);
                foreach ($goodsReceipt->lines as $line) {
                    $poLine = $line->purchaseOrderLine;
                    $poLine->update([
                        'received_quantity' => max((float) $poLine->received_quantity - (float) $line->accepted_quantity, 0),
                    ]);
                }

                $purchaseOrder = $goodsReceipt->purchaseOrder()->with('lines')->first();
                $receivedTotal = $purchaseOrder->lines->sum(fn ($line) => (float) $line->received_quantity);
                $purchaseOrder->update(['status' => $receivedTotal > 0 ? 'in_progress' : 'approved']);
            }

            $goodsReceipt->update([
                'status' => 'cancelled',
                'cancelled_by' => Auth::id(),
                'cancelled_at' => now(),
                'cancellation_reason' => $validated['reason'],
                'updated_by' => Auth::id(),
            ]);
        });

        return back()->with('success', 'Goods receipt cancelled.');
    }

    private function validateGoodsReceipt(Request $request): array
    {
        return $request->validate([
            'gr_date' => ['required', 'date'],
            'purchase_order_id' => ['required', 'exists:purchase_orders,id'],
            'reference_number' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string'],
            'lines' => ['required', 'array', 'min:1'],
            'lines.*.purchase_order_line_id' => ['required', 'exists:purchase_order_lines,id'],
            'lines.*.receipt_quantity' => ['required', 'numeric', 'min:0.001'],
            'lines.*.inspection_notes' => ['nullable', 'string'],
        ]);
    }

    private function syncLines(GoodsReceipt $goodsReceipt, array $lines): void
    {
        foreach (array_values($lines) as $index => $line) {
            $purchaseOrderLine = PurchaseOrderLine::findOrFail($line['purchase_order_line_id']);
            $remaining = $this->remainingQuantity($purchaseOrderLine, $goodsReceipt);
            abort_if((float) $line['receipt_quantity'] > $remaining, 422, 'Receipt quantity exceeds remaining quantity.');

            $goodsReceipt->lines()->create([
                'purchase_order_line_id' => $purchaseOrderLine->id,
                'line_number' => $index + 1,
                'product_code' => $purchaseOrderLine->product_code,
                'product_name' => $purchaseOrderLine->product_name,
                'description' => $purchaseOrderLine->description,
                'po_quantity' => $purchaseOrderLine->quantity,
                'previously_received_quantity' => $purchaseOrderLine->received_quantity,
                'remaining_quantity' => $remaining,
                'receipt_quantity' => $line['receipt_quantity'],
                'unit' => $purchaseOrderLine->unit,
                'inspection_notes' => $line['inspection_notes'] ?? null,
            ]);
        }
    }

    private function ensurePurchaseOrderReceivable(PurchaseOrder $purchaseOrder): void
    {
        abort_unless(in_array($purchaseOrder->status, ['approved', 'in_progress'], true), 422, 'Purchase order must be approved or in progress.');
    }

    private function remainingQuantity(PurchaseOrderLine $line, ?GoodsReceipt $currentGoodsReceipt = null): float
    {
        $currentQuantity = $currentGoodsReceipt
            ? (float) $currentGoodsReceipt->lines()->where('purchase_order_line_id', $line->id)->sum('receipt_quantity')
            : 0.0;

        return max((float) $line->quantity - (float) $line->received_quantity + $currentQuantity, 0);
    }

    private function availableLines(PurchaseOrder $purchaseOrder): array
    {
        return $purchaseOrder->lines->map(function ($line) {
            $remaining = $this->remainingQuantity($line);

            return [
                'id' => $line->id,
                'product_code' => $line->product_code,
                'product_name' => $line->product_name,
                'description' => $line->description,
                'quantity' => $line->quantity,
                'received_quantity' => $line->received_quantity,
                'remaining_quantity' => $remaining,
                'unit' => $line->unit,
            ];
        })->filter(fn ($line) => $line['remaining_quantity'] > 0)->values()->all();
    }

    private function availablePurchaseOrders()
    {
        return PurchaseOrder::with('supplier')
            ->whereIn('status', ['approved', 'in_progress'])
            ->orderByDesc('po_date')
            ->get(['id', 'po_number', 'supplier_id', 'po_date', 'status']);
    }
}

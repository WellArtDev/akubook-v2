<?php

namespace App\Http\Controllers;

use App\Models\DeliveryOrder;
use App\Models\SalesOrder;
use App\Models\SalesOrderLine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class DeliveryOrderController extends Controller
{
    public function index(Request $request)
    {
        $query = DeliveryOrder::with(['salesOrder', 'customer'])
            ->when($request->filled('status'), fn ($q) => $q->whereIn('status', (array) $request->status))
            ->when($request->filled('customer_id'), fn ($q) => $q->where('customer_id', $request->customer_id))
            ->when($request->filled('driver'), fn ($q) => $q->where('driver_name', 'ilike', '%' . $request->driver . '%'))
            ->when($request->filled('date_from'), fn ($q) => $q->whereDate('do_date', '>=', $request->date_from))
            ->when($request->filled('date_to'), fn ($q) => $q->whereDate('do_date', '<=', $request->date_to))
            ->when($request->filled('search'), function ($q) use ($request) {
                $q->where(function ($sub) use ($request) {
                    $sub->where('do_number', 'ilike', '%' . $request->search . '%')
                        ->orWhereHas('salesOrder', fn ($so) => $so->where('so_number', 'ilike', '%' . $request->search . '%'));
                });
            });

        $sort = in_array($request->sort, ['do_number', 'do_date', 'delivery_date', 'status'], true) ? $request->sort : 'do_date';
        $direction = $request->direction === 'asc' ? 'asc' : 'desc';

        return Inertia::render('DeliveryOrders/Index', [
            'deliveryOrders' => $query->orderBy($sort, $direction)->paginate(50)->withQueryString(),
            'filters' => $request->only(['status', 'customer_id', 'driver', 'date_from', 'date_to', 'search', 'sort', 'direction']),
        ]);
    }

    public function create(Request $request)
    {
        $salesOrder = $request->filled('sales_order_id')
            ? SalesOrder::with(['customer', 'deliveryAddress', 'lines.item'])->findOrFail($request->sales_order_id)
            : null;

        return Inertia::render('DeliveryOrders/Create', [
            'salesOrders' => $this->availableSalesOrders(),
            'salesOrder' => $salesOrder,
            'lines' => $salesOrder ? $this->availableLines($salesOrder) : [],
        ]);
    }

    public function store(Request $request)
    {
        $validated = $this->validateDeliveryOrder($request);
        $salesOrder = SalesOrder::with(['lines'])->findOrFail($validated['sales_order_id']);
        $this->ensureSalesOrderDeliverable($salesOrder);

        $deliveryOrder = DB::transaction(function () use ($validated, $salesOrder) {
            $deliveryOrder = DeliveryOrder::create([
                'do_number' => DeliveryOrder::generateNumber(),
                'do_date' => $validated['do_date'],
                'sales_order_id' => $salesOrder->id,
                'customer_id' => $salesOrder->customer_id,
                'delivery_address_id' => $salesOrder->delivery_address_id,
                'delivery_date' => $validated['delivery_date'] ?? null,
                'driver_name' => $validated['driver_name'] ?? null,
                'vehicle_number' => $validated['vehicle_number'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'status' => 'draft',
                'created_by' => Auth::id(),
            ]);

            $this->syncLines($deliveryOrder, $validated['lines']);

            return $deliveryOrder;
        });

        return redirect()->route('delivery-orders.show', $deliveryOrder)->with('success', 'Delivery order created.');
    }

    public function show(DeliveryOrder $deliveryOrder)
    {
        $deliveryOrder->load(['salesOrder', 'customer', 'deliveryAddress', 'lines.item', 'lines.salesOrderLine', 'createdBy', 'cancelledBy']);

        return Inertia::render('DeliveryOrders/Show', [
            'deliveryOrder' => $deliveryOrder,
        ]);
    }

    public function edit(DeliveryOrder $deliveryOrder)
    {
        abort_unless($deliveryOrder->can_edit, 403, 'Only draft delivery orders can be edited.');
        $deliveryOrder->load(['salesOrder.customer', 'salesOrder.deliveryAddress', 'lines.item']);

        return Inertia::render('DeliveryOrders/Edit', [
            'deliveryOrder' => $deliveryOrder,
            'salesOrders' => $this->availableSalesOrders($deliveryOrder->sales_order_id),
            'lines' => $this->availableLines($deliveryOrder->salesOrder, $deliveryOrder),
        ]);
    }

    public function update(Request $request, DeliveryOrder $deliveryOrder)
    {
        abort_unless($deliveryOrder->can_edit, 403, 'Only draft delivery orders can be edited.');
        $validated = $this->validateDeliveryOrder($request);
        $salesOrder = SalesOrder::with(['lines'])->findOrFail($validated['sales_order_id']);
        $this->ensureSalesOrderDeliverable($salesOrder);

        DB::transaction(function () use ($deliveryOrder, $validated, $salesOrder) {
            $deliveryOrder->update([
                'do_date' => $validated['do_date'],
                'sales_order_id' => $salesOrder->id,
                'customer_id' => $salesOrder->customer_id,
                'delivery_address_id' => $salesOrder->delivery_address_id,
                'delivery_date' => $validated['delivery_date'] ?? null,
                'driver_name' => $validated['driver_name'] ?? null,
                'vehicle_number' => $validated['vehicle_number'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'updated_by' => Auth::id(),
            ]);
            $deliveryOrder->lines()->delete();
            $this->syncLines($deliveryOrder, $validated['lines']);
        });

        return redirect()->route('delivery-orders.show', $deliveryOrder)->with('success', 'Delivery order updated.');
    }

    public function destroy(DeliveryOrder $deliveryOrder)
    {
        abort_unless($deliveryOrder->can_edit, 403, 'Only draft delivery orders can be deleted.');
        $deliveryOrder->delete();

        return redirect()->route('delivery-orders.index')->with('success', 'Delivery order deleted.');
    }

    public function confirm(DeliveryOrder $deliveryOrder)
    {
        abort_unless($deliveryOrder->status === 'draft', 403, 'Only draft delivery orders can be confirmed.');
        abort_if(blank($deliveryOrder->driver_name) || blank($deliveryOrder->vehicle_number), 422, 'Driver and vehicle are required.');

        $deliveryOrder->update(['status' => 'ready_to_ship', 'updated_by' => Auth::id()]);

        return back()->with('success', 'Delivery order ready to ship.');
    }

    public function ship(DeliveryOrder $deliveryOrder)
    {
        abort_unless($deliveryOrder->status === 'ready_to_ship', 403, 'Only ready delivery orders can be shipped.');
        $deliveryOrder->update(['status' => 'in_transit', 'updated_by' => Auth::id()]);

        return back()->with('success', 'Delivery order in transit.');
    }

    public function markDelivered(Request $request, DeliveryOrder $deliveryOrder)
    {
        abort_unless($deliveryOrder->status === 'in_transit', 403, 'Only in-transit delivery orders can be delivered.');
        $validated = $request->validate([
            'received_by' => ['required', 'string', 'max:100'],
            'received_at' => ['required', 'date'],
            'signature_path' => ['nullable', 'string', 'max:255'],
            'pod_notes' => ['nullable', 'string'],
        ]);

        DB::transaction(function () use ($deliveryOrder, $validated) {
            $deliveryOrder->load(['lines.salesOrderLine', 'salesOrder.lines']);

            foreach ($deliveryOrder->lines as $line) {
                $salesOrderLine = $line->salesOrderLine;
                $salesOrderLine->update([
                    'delivered_quantity' => (float) $salesOrderLine->delivered_quantity + (float) $line->delivery_quantity,
                ]);
            }

            $salesOrder = $deliveryOrder->salesOrder()->with('lines')->first();
            $fullyDelivered = $salesOrder->lines->every(fn ($line) => (float) $line->delivered_quantity >= (float) $line->quantity);
            $salesOrder->update(['status' => $fullyDelivered ? 'completed' : 'in_progress']);

            $deliveryOrder->update($validated + ['status' => 'delivered', 'updated_by' => Auth::id()]);
        });

        return back()->with('success', 'Delivery order delivered.');
    }

    public function cancel(Request $request, DeliveryOrder $deliveryOrder)
    {
        abort_if($deliveryOrder->status === 'cancelled', 403, 'Delivery order already cancelled.');
        $validated = $request->validate(['reason' => ['required', 'string', 'max:1000']]);

        DB::transaction(function () use ($deliveryOrder, $validated) {
            if ($deliveryOrder->status === 'delivered') {
                $deliveryOrder->load(['lines.salesOrderLine', 'salesOrder.lines']);
                foreach ($deliveryOrder->lines as $line) {
                    $salesOrderLine = $line->salesOrderLine;
                    $salesOrderLine->update([
                        'delivered_quantity' => max((float) $salesOrderLine->delivered_quantity - (float) $line->delivery_quantity, 0),
                    ]);
                }

                $salesOrder = $deliveryOrder->salesOrder()->with('lines')->first();
                $deliveredTotal = $salesOrder->lines->sum(fn ($line) => (float) $line->delivered_quantity);
                $salesOrder->update(['status' => $deliveredTotal > 0 ? 'in_progress' : 'approved']);
            }

            $deliveryOrder->update([
                'status' => 'cancelled',
                'cancelled_by' => Auth::id(),
                'cancelled_at' => now(),
                'cancellation_reason' => $validated['reason'],
                'updated_by' => Auth::id(),
            ]);
        });

        return back()->with('success', 'Delivery order cancelled.');
    }

    private function validateDeliveryOrder(Request $request): array
    {
        return $request->validate([
            'do_date' => ['required', 'date'],
            'sales_order_id' => ['required', 'exists:sales_orders,id'],
            'delivery_date' => ['nullable', 'date', 'after_or_equal:do_date'],
            'driver_name' => ['nullable', 'string', 'max:100'],
            'vehicle_number' => ['nullable', 'string', 'max:50'],
            'notes' => ['nullable', 'string'],
            'lines' => ['required', 'array', 'min:1'],
            'lines.*.sales_order_line_id' => ['required', 'exists:sales_order_lines,id'],
            'lines.*.delivery_quantity' => ['required', 'numeric', 'min:0.001'],
            'lines.*.notes' => ['nullable', 'string'],
        ]);
    }

    private function syncLines(DeliveryOrder $deliveryOrder, array $lines): void
    {
        foreach (array_values($lines) as $index => $line) {
            $salesOrderLine = SalesOrderLine::with('item')->findOrFail($line['sales_order_line_id']);
            $remaining = $this->remainingQuantity($salesOrderLine, $deliveryOrder);
            abort_if((float) $line['delivery_quantity'] > $remaining, 422, 'Delivery quantity exceeds remaining quantity.');

            $deliveryOrder->lines()->create([
                'sales_order_line_id' => $salesOrderLine->id,
                'line_number' => $index + 1,
                'item_id' => $salesOrderLine->item_id,
                'description' => $salesOrderLine->description,
                'so_quantity' => $salesOrderLine->quantity,
                'previously_delivered_quantity' => $salesOrderLine->delivered_quantity,
                'remaining_quantity' => $remaining,
                'delivery_quantity' => $line['delivery_quantity'],
                'unit' => $salesOrderLine->unit,
                'notes' => $line['notes'] ?? null,
            ]);
        }
    }

    private function ensureSalesOrderDeliverable(SalesOrder $salesOrder): void
    {
        abort_unless(in_array($salesOrder->status, ['approved', 'in_progress'], true), 422, 'Sales order must be approved or in progress.');
    }

    private function remainingQuantity(SalesOrderLine $line, ?DeliveryOrder $currentDeliveryOrder = null): float
    {
        $currentQuantity = $currentDeliveryOrder
            ? (float) $currentDeliveryOrder->lines()->where('sales_order_line_id', $line->id)->sum('delivery_quantity')
            : 0.0;

        return max((float) $line->quantity - (float) $line->delivered_quantity + $currentQuantity, 0);
    }

    private function availableLines(SalesOrder $salesOrder, ?DeliveryOrder $currentDeliveryOrder = null): array
    {
        return $salesOrder->lines->map(function ($line) use ($currentDeliveryOrder) {
            $remaining = $this->remainingQuantity($line, $currentDeliveryOrder);

            return [
                'id' => $line->id,
                'item_id' => $line->item_id,
                'item' => $line->item,
                'description' => $line->description,
                'quantity' => $line->quantity,
                'delivered_quantity' => $line->delivered_quantity,
                'remaining_quantity' => $remaining,
                'unit' => $line->unit,
            ];
        })->filter(fn ($line) => $line['remaining_quantity'] > 0)->values()->all();
    }

    private function availableSalesOrders(?int $includeId = null)
    {
        return SalesOrder::with('customer')
            ->whereIn('status', ['approved', 'in_progress'])
            ->when($includeId, fn ($q) => $q->orWhere('id', $includeId))
            ->orderByDesc('so_date')
            ->get(['id', 'so_number', 'customer_id', 'so_date', 'status']);
    }
}

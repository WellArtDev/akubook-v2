<?php

namespace App\Http\Controllers;

use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderLine;
use App\Models\PurchaseRequest;
use App\Models\Supplier;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class PurchaseOrderController extends Controller
{
    public function index(Request $request)
    {
        $query = PurchaseOrder::with(['supplier', 'deliveryAddress', 'createdBy'])
            ->orderBy('po_date', 'desc');

        // Filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('supplier_id')) {
            $query->where('supplier_id', $request->supplier_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('po_number', 'like', "%{$search}%")
                  ->orWhere('notes', 'like', "%{$search}%");
            });
        }

        $purchaseOrders = $query->paginate(15);

        return Inertia::render('PurchaseOrders/Index', [
            'purchaseOrders' => $purchaseOrders,
            'filters' => $request->only(['status', 'supplier_id', 'search']),
        ]);
    }

    public function create()
    {
        return Inertia::render('PurchaseOrders/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'po_date' => 'required|date',
            'supplier_id' => 'required|exists:suppliers,id',
            'delivery_address_id' => 'nullable|exists:branches,id',
            'payment_terms' => 'nullable|string|max:50',
            'delivery_terms' => 'nullable|string|max:100',
            'expected_delivery_date' => 'nullable|date|after_or_equal:po_date',
            'notes' => 'nullable|string',
            'lines' => 'required|array|min:1',
            'lines.*.product_code' => 'nullable|string|max:50',
            'lines.*.product_name' => 'required|string|max:255',
            'lines.*.description' => 'nullable|string',
            'lines.*.quantity' => 'required|numeric|min:0.001',
            'lines.*.unit' => 'required|string|max:20',
            'lines.*.unit_price' => 'required|numeric|min:0',
            'lines.*.tax_amount' => 'nullable|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            $po = PurchaseOrder::create([
                'po_number' => PurchaseOrder::generatePONumber(),
                'po_date' => $validated['po_date'],
                'supplier_id' => $validated['supplier_id'],
                'delivery_address_id' => $validated['delivery_address_id'] ?? null,
                'payment_terms' => $validated['payment_terms'] ?? null,
                'delivery_terms' => $validated['delivery_terms'] ?? null,
                'expected_delivery_date' => $validated['expected_delivery_date'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'status' => 'draft',
                'created_by' => auth()->id(),
            ]);

            foreach ($validated['lines'] as $index => $lineData) {
                PurchaseOrderLine::create([
                    'purchase_order_id' => $po->id,
                    'line_number' => $index + 1,
                    'product_code' => $lineData['product_code'] ?? null,
                    'product_name' => $lineData['product_name'],
                    'description' => $lineData['description'] ?? null,
                    'quantity' => $lineData['quantity'],
                    'unit' => $lineData['unit'],
                    'unit_price' => $lineData['unit_price'],
                    'tax_amount' => $lineData['tax_amount'] ?? 0,
                ]);
            }

            DB::commit();

            return redirect()->route('purchase-orders.show', $po->id)
                ->with('success', 'Purchase Order created successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Failed to create Purchase Order: ' . $e->getMessage()]);
        }
    }

    public function show(PurchaseOrder $purchaseOrder)
    {
        $purchaseOrder->load(['supplier', 'deliveryAddress', 'lines', 'createdBy', 'approvedBy', 'updatedBy']);

        return Inertia::render('PurchaseOrders/Show', [
            'purchaseOrder' => $purchaseOrder,
        ]);
    }

    public function edit(PurchaseOrder $purchaseOrder)
    {
        if ($purchaseOrder->status !== 'draft') {
            return back()->withErrors(['error' => 'Only draft POs can be edited']);
        }

        $purchaseOrder->load('lines');

        return Inertia::render('PurchaseOrders/Edit', [
            'purchaseOrder' => $purchaseOrder,
        ]);
    }

    public function update(Request $request, PurchaseOrder $purchaseOrder)
    {
        if ($purchaseOrder->status !== 'draft') {
            return back()->withErrors(['error' => 'Only draft POs can be updated']);
        }

        $validated = $request->validate([
            'po_date' => 'required|date',
            'supplier_id' => 'required|exists:suppliers,id',
            'delivery_address_id' => 'nullable|exists:branches,id',
            'payment_terms' => 'nullable|string|max:50',
            'delivery_terms' => 'nullable|string|max:100',
            'expected_delivery_date' => 'nullable|date|after_or_equal:po_date',
            'notes' => 'nullable|string',
            'lines' => 'required|array|min:1',
            'lines.*.product_code' => 'nullable|string|max:50',
            'lines.*.product_name' => 'required|string|max:255',
            'lines.*.description' => 'nullable|string',
            'lines.*.quantity' => 'required|numeric|min:0.001',
            'lines.*.unit' => 'required|string|max:20',
            'lines.*.unit_price' => 'required|numeric|min:0',
            'lines.*.tax_amount' => 'nullable|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            $purchaseOrder->update([
                'po_date' => $validated['po_date'],
                'supplier_id' => $validated['supplier_id'],
                'delivery_address_id' => $validated['delivery_address_id'] ?? null,
                'payment_terms' => $validated['payment_terms'] ?? null,
                'delivery_terms' => $validated['delivery_terms'] ?? null,
                'expected_delivery_date' => $validated['expected_delivery_date'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'updated_by' => auth()->id(),
            ]);

            // Delete existing lines and recreate
            $purchaseOrder->lines()->delete();

            foreach ($validated['lines'] as $index => $lineData) {
                PurchaseOrderLine::create([
                    'purchase_order_id' => $purchaseOrder->id,
                    'line_number' => $index + 1,
                    'product_code' => $lineData['product_code'] ?? null,
                    'product_name' => $lineData['product_name'],
                    'description' => $lineData['description'] ?? null,
                    'quantity' => $lineData['quantity'],
                    'unit' => $lineData['unit'],
                    'unit_price' => $lineData['unit_price'],
                    'tax_amount' => $lineData['tax_amount'] ?? 0,
                ]);
            }

            DB::commit();

            return redirect()->route('purchase-orders.show', $purchaseOrder->id)
                ->with('success', 'Purchase Order updated successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Failed to update Purchase Order: ' . $e->getMessage()]);
        }
    }

    public function destroy(PurchaseOrder $purchaseOrder)
    {
        if ($purchaseOrder->status !== 'draft') {
            return back()->withErrors(['error' => 'Only draft POs can be deleted']);
        }

        $purchaseOrder->delete();

        return redirect()->route('purchase-orders.index')
            ->with('success', 'Purchase Order deleted successfully');
    }

    public function submit(PurchaseOrder $purchaseOrder)
    {
        try {
            $purchaseOrder->submit();

            return back()->with('success', 'Purchase Order submitted successfully');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function approve(PurchaseOrder $purchaseOrder)
    {
        try {
            $purchaseOrder->approve(auth()->id());

            return back()->with('success', 'Purchase Order approved');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function reject(PurchaseOrder $purchaseOrder)
    {
        try {
            $purchaseOrder->reject(auth()->id());

            return back()->with('success', 'Purchase Order rejected');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function cancel(PurchaseOrder $purchaseOrder)
    {
        try {
            $purchaseOrder->cancel();

            return back()->with('success', 'Purchase Order cancelled');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function createFromPRs(Request $request)
    {
        $validated = $request->validate([
            'pr_ids' => 'required|array|min:1',
            'pr_ids.*' => 'exists:purchase_requests,id',
            'po_date' => 'required|date',
            'supplier_id' => 'required|exists:suppliers,id',
            'delivery_address_id' => 'nullable|exists:branches,id',
            'payment_terms' => 'nullable|string|max:50',
            'delivery_terms' => 'nullable|string|max:100',
            'expected_delivery_date' => 'nullable|date|after_or_equal:po_date',
            'notes' => 'nullable|string',
        ]);

        try {
            $po = PurchaseOrder::createFromPRs($validated['pr_ids'], $validated);

            return redirect()->route('purchase-orders.show', $po->id)
                ->with('success', 'Purchase Order created from PRs successfully');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function getApprovedPRs(Request $request)
    {
        $query = PurchaseRequest::where('status', 'approved')
            ->with(['department', 'lines']);

        if ($request->filled('supplier_id')) {
            // Filter by supplier if needed (would need supplier_id in PR)
        }

        $prs = $query->get();

        return response()->json($prs);
    }
}

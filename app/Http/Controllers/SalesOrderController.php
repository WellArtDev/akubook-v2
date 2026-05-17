<?php

namespace App\Http\Controllers;

use App\Models\SalesOrder;
use App\Models\Customer;
use App\Models\Branch;
use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class SalesOrderController extends Controller
{
    public function index(Request $request)
    {
        $query = SalesOrder::with(['customer', 'salesPerson']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('so_number', 'ilike', '%' . $request->search . '%')
                  ->orWhereHas('customer', function($sq) use ($request) {
                      $sq->where('name', 'ilike', '%' . $request->search . '%');
                  });
            });
        }

        $salesOrders = $query->orderBy('so_date', 'desc')
            ->orderBy('so_number', 'desc')
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('SalesOrders/Index', [
            'salesOrders' => $salesOrders,
            'filters' => $request->only(['status', 'search']),
        ]);
    }

    public function create()
    {
        $customers = Customer::where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'code', 'credit_limit', 'payment_terms_days']);

        $branches = Branch::where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'code']);

        $items = Item::where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'code', 'unit', 'selling_price']);

        return Inertia::render('SalesOrders/Create', [
            'customers' => $customers,
            'branches' => $branches,
            'items' => $items,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'so_date' => 'required|date',
            'customer_id' => 'required|exists:customers,id',
            'customer_po_number' => 'nullable|string|max:100',
            'payment_terms' => 'nullable|string|max:50',
            'delivery_terms' => 'nullable|string|max:100',
            'delivery_address_id' => 'nullable|exists:branches,id',
            'requested_delivery_date' => 'nullable|date',
            'notes' => 'nullable|string',
            'lines' => 'required|array|min:1',
            'lines.*.item_id' => 'required|exists:items,id',
            'lines.*.description' => 'nullable|string',
            'lines.*.quantity' => 'required|numeric|min:0.001',
            'lines.*.unit' => 'required|string|max:20',
            'lines.*.unit_price' => 'required|numeric|min:0',
            'lines.*.discount_percent' => 'nullable|numeric|min:0|max:100',
            'lines.*.discount_amount' => 'nullable|numeric|min:0',
            'lines.*.tax_amount' => 'nullable|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();

            // Generate SO number
            $year = date('Y');
            $lastSO = SalesOrder::where('so_number', 'like', "SO-{$year}-%")
                ->orderBy('so_number', 'desc')
                ->first();
            
            $nextNumber = $lastSO 
                ? intval(substr($lastSO->so_number, -4)) + 1 
                : 1;
            
            $soNumber = sprintf('SO-%s-%04d', $year, $nextNumber);

            // Create SO
            $so = SalesOrder::create([
                'so_number' => $soNumber,
                'so_date' => $validated['so_date'],
                'customer_id' => $validated['customer_id'],
                'customer_po_number' => $validated['customer_po_number'] ?? null,
                'sales_person_id' => auth()->id(),
                'payment_terms' => $validated['payment_terms'],
                'delivery_terms' => $validated['delivery_terms'],
                'delivery_address_id' => $validated['delivery_address_id'],
                'requested_delivery_date' => $validated['requested_delivery_date'],
                'notes' => $validated['notes'],
                'status' => 'draft',
                'created_by' => auth()->id(),
            ]);

            // Create lines
            foreach ($validated['lines'] as $index => $line) {
                $lineTotal = ($line['quantity'] * $line['unit_price']) - ($line['discount_amount'] ?? 0);
                
                $so->lines()->create([
                    'line_number' => $index + 1,
                    'item_id' => $line['item_id'],
                    'description' => $line['description'],
                    'quantity' => $line['quantity'],
                    'unit' => $line['unit'],
                    'unit_price' => $line['unit_price'],
                    'discount_percent' => $line['discount_percent'] ?? 0,
                    'discount_amount' => $line['discount_amount'] ?? 0,
                    'tax_amount' => $line['tax_amount'] ?? 0,
                    'line_total' => $lineTotal,
                ]);
            }

            // Calculate totals
            $so->calculateTotals();
            $so->approval_required = $so->requiresApproval();
            
            // Check credit limit
            $creditCheck = $so->checkCreditLimit();
            $so->credit_check_passed = $creditCheck['passed'];
            $so->credit_check_notes = $creditCheck['notes'];
            
            $so->save();

            DB::commit();

            $message = "Sales Order {$so->so_number} berhasil dibuat.";
            if (!$creditCheck['passed']) {
                $message .= " Warning: " . $creditCheck['notes'];
            }

            return redirect()->route('sales-orders.index')
                ->with('success', $message);
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => $e->getMessage()])->withInput();
        }
    }

    public function show(SalesOrder $salesOrder)
    {
        $salesOrder->load([
            'customer',
            'salesPerson',
            'deliveryAddress',
            'lines.item',
            'approvedBy',
            'createdBy'
        ]);

        return Inertia::render('SalesOrders/Show', [
            'salesOrder' => $salesOrder,
        ]);
    }

    public function edit(SalesOrder $salesOrder)
    {
        if (!in_array($salesOrder->status, ['draft', 'pending_approval'])) {
            return back()->withErrors(['error' => 'Hanya draft atau pending approval yang dapat diedit.']);
        }

        $salesOrder->load('lines.item');

        $customers = Customer::where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'code', 'credit_limit', 'payment_terms_days']);

        $branches = Branch::where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'code']);

        $items = Item::where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'code', 'unit', 'selling_price']);

        return Inertia::render('SalesOrders/Edit', [
            'salesOrder' => $salesOrder,
            'customers' => $customers,
            'branches' => $branches,
            'items' => $items,
        ]);
    }

    public function update(Request $request, SalesOrder $salesOrder)
    {
        if (!in_array($salesOrder->status, ['draft', 'pending_approval'])) {
            return back()->withErrors(['error' => 'Hanya draft atau pending approval yang dapat diedit.']);
        }

        $validated = $request->validate([
            'so_date' => 'required|date',
            'customer_id' => 'required|exists:customers,id',
            'customer_po_number' => 'nullable|string|max:100',
            'payment_terms' => 'nullable|string|max:50',
            'delivery_terms' => 'nullable|string|max:100',
            'delivery_address_id' => 'nullable|exists:branches,id',
            'requested_delivery_date' => 'nullable|date',
            'notes' => 'nullable|string',
            'lines' => 'required|array|min:1',
            'lines.*.item_id' => 'required|exists:items,id',
            'lines.*.description' => 'nullable|string',
            'lines.*.quantity' => 'required|numeric|min:0.001',
            'lines.*.unit' => 'required|string|max:20',
            'lines.*.unit_price' => 'required|numeric|min:0',
            'lines.*.discount_percent' => 'nullable|numeric|min:0|max:100',
            'lines.*.discount_amount' => 'nullable|numeric|min:0',
            'lines.*.tax_amount' => 'nullable|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();

            // Delete existing lines
            $salesOrder->lines()->delete();

            // Update SO
            $salesOrder->update([
                'so_date' => $validated['so_date'],
                'customer_id' => $validated['customer_id'],
                'customer_po_number' => $validated['customer_po_number'] ?? null,
                'payment_terms' => $validated['payment_terms'],
                'delivery_terms' => $validated['delivery_terms'],
                'delivery_address_id' => $validated['delivery_address_id'],
                'requested_delivery_date' => $validated['requested_delivery_date'],
                'notes' => $validated['notes'],
            ]);

            // Recreate lines
            foreach ($validated['lines'] as $index => $line) {
                $lineTotal = ($line['quantity'] * $line['unit_price']) - ($line['discount_amount'] ?? 0);
                
                $salesOrder->lines()->create([
                    'line_number' => $index + 1,
                    'item_id' => $line['item_id'],
                    'description' => $line['description'],
                    'quantity' => $line['quantity'],
                    'unit' => $line['unit'],
                    'unit_price' => $line['unit_price'],
                    'discount_percent' => $line['discount_percent'] ?? 0,
                    'discount_amount' => $line['discount_amount'] ?? 0,
                    'tax_amount' => $line['tax_amount'] ?? 0,
                    'line_total' => $lineTotal,
                ]);
            }

            // Recalculate totals
            $salesOrder->calculateTotals();
            $salesOrder->approval_required = $salesOrder->requiresApproval();
            
            // Recheck credit limit
            $creditCheck = $salesOrder->checkCreditLimit();
            $salesOrder->credit_check_passed = $creditCheck['passed'];
            $salesOrder->credit_check_notes = $creditCheck['notes'];
            
            $salesOrder->save();

            DB::commit();

            $message = "Sales Order {$salesOrder->so_number} berhasil diperbarui.";
            if (!$creditCheck['passed']) {
                $message .= " Warning: " . $creditCheck['notes'];
            }

            return redirect()->route('sales-orders.index')
                ->with('success', $message);
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => $e->getMessage()])->withInput();
        }
    }

    public function destroy(SalesOrder $salesOrder)
    {
        if ($salesOrder->status !== 'draft') {
            return back()->withErrors(['error' => 'Hanya draft yang dapat dihapus.']);
        }

        try {
            $soNumber = $salesOrder->so_number;
            $salesOrder->delete();

            return redirect()->route('sales-orders.index')
                ->with('success', "Sales Order {$soNumber} berhasil dihapus.");
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function approve(SalesOrder $salesOrder)
    {
        if ($salesOrder->status !== 'pending_approval') {
            return back()->withErrors(['error' => 'Hanya SO pending approval yang dapat diapprove.']);
        }

        try {
            $salesOrder->update([
                'status' => 'approved',
                'approved_by' => auth()->id(),
                'approved_at' => now(),
            ]);

            return redirect()->route('sales-orders.show', $salesOrder)
                ->with('success', "Sales Order {$salesOrder->so_number} berhasil diapprove.");
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function submitForApproval(SalesOrder $salesOrder)
    {
        if ($salesOrder->status !== 'draft') {
            return back()->withErrors(['error' => 'Hanya draft yang dapat disubmit untuk approval.']);
        }

        if (!$salesOrder->approval_required) {
            return back()->withErrors(['error' => 'SO ini tidak memerlukan approval.']);
        }

        try {
            $salesOrder->update(['status' => 'pending_approval']);

            return redirect()->route('sales-orders.show', $salesOrder)
                ->with('success', "Sales Order {$salesOrder->so_number} berhasil disubmit untuk approval.");
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }
}

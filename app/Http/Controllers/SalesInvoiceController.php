<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\DeliveryOrder;
use App\Models\DeliveryOrderLine;
use App\Models\SalesInvoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class SalesInvoiceController extends Controller
{
    public function index(Request $request)
    {
        $query = SalesInvoice::with(['customer', 'salesOrder']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('invoice_number', 'ilike', '%' . $request->search . '%')
                    ->orWhere('tax_invoice_number', 'ilike', '%' . $request->search . '%')
                    ->orWhereHas('customer', function ($sq) use ($request) {
                        $sq->where('name', 'ilike', '%' . $request->search . '%');
                    });
            });
        }

        $invoices = $query->orderBy('invoice_date', 'desc')
            ->orderBy('invoice_number', 'desc')
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('SalesInvoices/Index', [
            'invoices' => $invoices,
            'filters' => $request->only(['status', 'search']),
        ]);
    }

    public function create(Request $request)
    {
        $deliveryOrders = DeliveryOrder::with(['customer', 'salesOrder', 'lines.item'])
            ->whereIn('status', ['delivered'])
            ->orderByDesc('do_date')
            ->get();

        $selectedDeliveryOrder = $request->filled('delivery_order_id')
            ? DeliveryOrder::with(['customer', 'salesOrder', 'lines.item', 'lines.salesOrderLine'])->findOrFail($request->delivery_order_id)
            : null;

        $customers = Customer::where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'code', 'tax_id']);

        return Inertia::render('SalesInvoices/Create', [
            'deliveryOrders' => $deliveryOrders,
            'selectedDeliveryOrder' => $selectedDeliveryOrder,
            'availableLines' => $selectedDeliveryOrder ? $this->buildAvailableLines($selectedDeliveryOrder) : [],
            'customers' => $customers,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'invoice_date' => 'required|date',
            'due_date' => 'required|date|after_or_equal:invoice_date',
            'delivery_order_id' => 'required|exists:delivery_orders,id',
            'sales_order_id' => 'required|exists:sales_orders,id',
            'customer_id' => 'required|exists:customers,id',
            'billing_address' => 'nullable|string',
            'payment_terms' => 'nullable|string|max:50',
            'reference' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
            'generate_tax_invoice' => 'boolean',
            'lines' => 'required|array|min:1',
            'lines.*.delivery_order_line_id' => 'required|exists:delivery_order_lines,id',
            'lines.*.quantity' => 'required|numeric|min:0.001',
        ]);

        $deliveryOrder = DeliveryOrder::with(['lines.item', 'lines.salesOrderLine', 'salesOrder'])->findOrFail($validated['delivery_order_id']);
        abort_unless($deliveryOrder->status === 'delivered', 422, 'Delivery order must be delivered.');

        $available = collect($this->buildAvailableLines($deliveryOrder))->keyBy('delivery_order_line_id');

        DB::beginTransaction();
        try {
            $invoice = new SalesInvoice();
            $invoice->invoice_number = SalesInvoice::generateInvoiceNumber();
            $invoice->invoice_date = $validated['invoice_date'];
            $invoice->due_date = $validated['due_date'];
            $invoice->sales_order_id = $validated['sales_order_id'];
            $invoice->customer_id = $validated['customer_id'];
            $invoice->billing_address = $validated['billing_address'] ?? null;
            $invoice->payment_terms = $validated['payment_terms'] ?? null;
            $invoice->reference = $validated['reference'] ?? null;
            $invoice->notes = $validated['notes'] ?? null;
            $invoice->status = 'draft';
            $invoice->created_by = Auth::id();

            if ($request->boolean('generate_tax_invoice')) {
                $invoice->tax_invoice_number = SalesInvoice::generateTaxInvoiceNumber();
            }

            $invoice->save();

            foreach ($validated['lines'] as $index => $lineData) {
                $source = $available->get((int) $lineData['delivery_order_line_id']);
                abort_if(!$source, 422, 'Invalid delivery order line.');
                abort_if((float) $lineData['quantity'] > (float) $source['remaining_to_invoice'], 422, 'Invoice quantity exceeds remaining delivered quantity.');

                $line = $invoice->lines()->create([
                    'sales_order_line_id' => $source['sales_order_line_id'],
                    'delivery_order_line_id' => $source['delivery_order_line_id'],
                    'line_number' => $index + 1,
                    'product_id' => $source['product_id'],
                    'product_name' => $source['product_name'],
                    'description' => $source['description'],
                    'quantity' => $lineData['quantity'],
                    'unit' => $source['unit'],
                    'unit_price' => $source['unit_price'],
                    'discount_amount' => $lineData['discount_amount'] ?? 0,
                    'line_total' => 0,
                ]);

                $line->calculateTax(0.11);
                $line->calculateLineTotal();
                $line->save();
            }

            $invoice->load('lines');
            $invoice->calculateTotals();
            $invoice->save();

            DB::commit();

            return redirect()->route('sales-invoices.show', $invoice)
                ->with('success', 'Invoice created successfully');
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->withErrors(['error' => 'Failed to create invoice: ' . $e->getMessage()]);
        }
    }

    public function show(SalesInvoice $salesInvoice)
    {
        $salesInvoice->load(['customer', 'salesOrder', 'lines.deliveryOrderLine', 'createdBy']);

        return Inertia::render('SalesInvoices/Show', [
            'invoice' => $salesInvoice,
        ]);
    }

    public function edit(SalesInvoice $salesInvoice)
    {
        if ($salesInvoice->status !== 'draft') {
            return back()->withErrors(['error' => 'Only draft invoices can be edited']);
        }

        $salesInvoice->load(['customer', 'salesOrder', 'lines']);

        $customers = Customer::where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'code', 'tax_id']);

        return Inertia::render('SalesInvoices/Edit', [
            'invoice' => $salesInvoice,
            'customers' => $customers,
        ]);
    }

    public function update(Request $request, SalesInvoice $salesInvoice)
    {
        if ($salesInvoice->status !== 'draft') {
            return back()->withErrors(['error' => 'Only draft invoices can be updated']);
        }

        $validated = $request->validate([
            'invoice_date' => 'required|date',
            'due_date' => 'required|date|after_or_equal:invoice_date',
            'billing_address' => 'nullable|string',
            'payment_terms' => 'nullable|string|max:50',
            'reference' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
            'lines' => 'required|array|min:1',
            'lines.*.id' => 'nullable|exists:sales_invoice_lines,id',
            'lines.*.product_name' => 'required|string',
            'lines.*.description' => 'nullable|string',
            'lines.*.quantity' => 'required|numeric|min:0.001',
            'lines.*.unit' => 'required|string|max:20',
            'lines.*.unit_price' => 'required|numeric|min:0',
            'lines.*.discount_amount' => 'nullable|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            $salesInvoice->update([
                'invoice_date' => $validated['invoice_date'],
                'due_date' => $validated['due_date'],
                'billing_address' => $validated['billing_address'] ?? null,
                'payment_terms' => $validated['payment_terms'] ?? null,
                'reference' => $validated['reference'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'updated_by' => Auth::id(),
            ]);

            $salesInvoice->lines()->delete();

            foreach ($validated['lines'] as $index => $lineData) {
                $line = $salesInvoice->lines()->create([
                    'line_number' => $index + 1,
                    'product_name' => $lineData['product_name'],
                    'description' => $lineData['description'] ?? null,
                    'quantity' => $lineData['quantity'],
                    'unit' => $lineData['unit'],
                    'unit_price' => $lineData['unit_price'],
                    'discount_amount' => $lineData['discount_amount'] ?? 0,
                    'line_total' => 0,
                ]);

                $line->calculateTax(0.11);
                $line->calculateLineTotal();
                $line->save();
            }

            $salesInvoice->load('lines');
            $salesInvoice->calculateTotals();
            $salesInvoice->save();

            DB::commit();

            return redirect()->route('sales-invoices.show', $salesInvoice)
                ->with('success', 'Invoice updated successfully');
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->withErrors(['error' => 'Failed to update invoice: ' . $e->getMessage()]);
        }
    }

    public function destroy(SalesInvoice $salesInvoice)
    {
        if ($salesInvoice->status !== 'draft') {
            return back()->withErrors(['error' => 'Only draft invoices can be deleted']);
        }

        $salesInvoice->delete();

        return redirect()->route('sales-invoices.index')
            ->with('success', 'Invoice deleted successfully');
    }

    public function send(SalesInvoice $salesInvoice)
    {
        if ($salesInvoice->status !== 'draft') {
            return back()->withErrors(['error' => 'Only draft invoices can be sent']);
        }

        try {
            $salesInvoice->post();

            return back()->with('success', 'Invoice sent successfully');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to send invoice: ' . $e->getMessage()]);
        }
    }

    public function post(SalesInvoice $salesInvoice)
    {
        if ($salesInvoice->status !== 'draft') {
            return back()->withErrors(['error' => 'Only draft invoices can be posted']);
        }

        try {
            $salesInvoice->post();

            return back()->with('success', 'Invoice posted and journal entry generated');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to post invoice: ' . $e->getMessage()]);
        }
    }

    public function cancel(Request $request, SalesInvoice $salesInvoice)
    {
        $validated = $request->validate([
            'cancellation_reason' => 'required|string|max:500',
        ]);

        $success = $salesInvoice->cancel(Auth::id(), $validated['cancellation_reason']);

        if (!$success) {
            return back()->withErrors(['error' => 'Cannot cancel invoice with payments']);
        }

        return back()->with('success', 'Invoice cancelled successfully');
    }

    public function recordPayment(Request $request, SalesInvoice $salesInvoice)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'required|string|max:50',
        ]);

        if ($validated['amount'] > $salesInvoice->amount_due) {
            return back()->withErrors(['error' => 'Payment amount exceeds amount due']);
        }

        try {
            $salesInvoice->recordPayment($validated['amount'], $validated['payment_method']);

            return back()->with('success', 'Payment recorded successfully');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to record payment: ' . $e->getMessage()]);
        }
    }

    private function buildAvailableLines(DeliveryOrder $deliveryOrder): array
    {
        $deliveryOrder->loadMissing(['lines.salesOrderLine', 'lines.item']);

        return $deliveryOrder->lines->map(function ($line) {
            $alreadyInvoiced = (float) DB::table('sales_invoice_lines')
                ->where('delivery_order_line_id', $line->id)
                ->sum('quantity');

            $remaining = max((float) $line->delivery_quantity - $alreadyInvoiced, 0);

            return [
                'delivery_order_line_id' => $line->id,
                'sales_order_line_id' => $line->sales_order_line_id,
                'product_id' => $line->item_id,
                'product_name' => $line->item?->name ?? $line->description,
                'description' => $line->description,
                'unit' => $line->unit,
                'unit_price' => (float) ($line->salesOrderLine?->unit_price ?? 0),
                'delivery_quantity' => (float) $line->delivery_quantity,
                'already_invoiced' => $alreadyInvoiced,
                'remaining_to_invoice' => $remaining,
            ];
        })->filter(fn ($line) => $line['remaining_to_invoice'] > 0)->values()->all();
    }
}

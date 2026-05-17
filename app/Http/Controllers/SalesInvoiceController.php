<?php

namespace App\Http\Controllers;

use App\Models\SalesInvoice;
use App\Models\SalesOrder;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
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
            $query->where(function($q) use ($request) {
                $q->where('invoice_number', 'ilike', '%' . $request->search . '%')
                  ->orWhere('tax_invoice_number', 'ilike', '%' . $request->search . '%')
                  ->orWhereHas('customer', function($sq) use ($request) {
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

    public function create()
    {
        // Get approved sales orders that can be invoiced
        $salesOrders = SalesOrder::with(['customer', 'lines'])
            ->where('status', 'approved')
            ->orderBy('so_date', 'desc')
            ->get();

        $customers = Customer::where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'code', 'tax_id']);

        return Inertia::render('SalesInvoices/Create', [
            'salesOrders' => $salesOrders,
            'customers' => $customers,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'invoice_date' => 'required|date',
            'due_date' => 'required|date|after_or_equal:invoice_date',
            'sales_order_id' => 'required|exists:sales_orders,id',
            'customer_id' => 'required|exists:customers,id',
            'billing_address' => 'nullable|string',
            'payment_terms' => 'nullable|string|max:50',
            'reference' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
            'generate_tax_invoice' => 'boolean',
            'lines' => 'required|array|min:1',
            'lines.*.sales_order_line_id' => 'nullable|exists:sales_order_lines,id',
            'lines.*.product_id' => 'nullable|integer',
            'lines.*.product_name' => 'required|string',
            'lines.*.description' => 'nullable|string',
            'lines.*.quantity' => 'required|numeric|min:0.001',
            'lines.*.unit' => 'required|string|max:20',
            'lines.*.unit_price' => 'required|numeric|min:0',
            'lines.*.discount_amount' => 'nullable|numeric|min:0',
        ]);

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

            // Create invoice lines
            foreach ($validated['lines'] as $index => $lineData) {
                $line = $invoice->lines()->create([
                    'sales_order_line_id' => $lineData['sales_order_line_id'] ?? null,
                    'line_number' => $index + 1,
                    'product_id' => $lineData['product_id'] ?? null,
                    'product_name' => $lineData['product_name'],
                    'description' => $lineData['description'] ?? null,
                    'quantity' => $lineData['quantity'],
                    'unit' => $lineData['unit'],
                    'unit_price' => $lineData['unit_price'],
                    'discount_amount' => $lineData['discount_amount'] ?? 0,
                ]);

                // Calculate tax (PPN 11%)
                $line->calculateTax(0.11);
                $line->calculateLineTotal();
                $line->save();
            }

            // Calculate invoice totals
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
        $salesInvoice->load(['customer', 'salesOrder', 'lines', 'createdBy']);

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

            // Delete existing lines
            $salesInvoice->lines()->delete();

            // Create new lines
            foreach ($validated['lines'] as $index => $lineData) {
                $line = $salesInvoice->lines()->create([
                    'line_number' => $index + 1,
                    'product_name' => $lineData['product_name'],
                    'description' => $lineData['description'] ?? null,
                    'quantity' => $lineData['quantity'],
                    'unit' => $lineData['unit'],
                    'unit_price' => $lineData['unit_price'],
                    'discount_amount' => $lineData['discount_amount'] ?? 0,
                ]);

                $line->calculateTax(0.11);
                $line->calculateLineTotal();
                $line->save();
            }

            // Recalculate totals
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
            $salesInvoice->send();

            return back()->with('success', 'Invoice sent successfully');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to send invoice: ' . $e->getMessage()]);
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
}

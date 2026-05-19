<?php

namespace App\Http\Controllers;

use App\Models\SalesInvoice;
use App\Models\SalesReturn;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class SalesReturnController extends Controller
{
    public function index(Request $request)
    {
        $query = SalesReturn::with(['customer', 'salesInvoice']);

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        if ($request->filled('search')) {
            $query->where('rma_number', 'ilike', '%' . $request->string('search') . '%');
        }

        $returns = $query->orderByDesc('return_date')->paginate(15)->withQueryString();

        return Inertia::render('SalesReturns/Index', [
            'returns' => $returns,
            'filters' => $request->only(['status', 'search']),
        ]);
    }

    public function create()
    {
        $invoices = SalesInvoice::with(['customer', 'lines'])
            ->whereIn('status', ['sent', 'partially_paid', 'paid'])
            ->orderByDesc('invoice_date')
            ->get();

        return Inertia::render('SalesReturns/Create', [
            'invoices' => $invoices,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'return_date' => 'required|date',
            'sales_invoice_id' => 'required|exists:sales_invoices,id',
            'return_reason' => 'required|string',
            'lines' => 'required|array|min:1',
            'lines.*.sales_invoice_line_id' => 'required|exists:sales_invoice_lines,id',
            'lines.*.return_quantity' => 'required|numeric|min:0.001',
            'lines.*.inspection_notes' => 'nullable|string',
        ]);

        $invoice = SalesInvoice::with('lines')->findOrFail($validated['sales_invoice_id']);

        try {
            DB::transaction(function () use ($validated, $invoice) {
            $salesReturn = SalesReturn::create([
                'rma_number' => SalesReturn::generateRmaNumber(),
                'return_date' => $validated['return_date'],
                'sales_invoice_id' => $invoice->id,
                'customer_id' => $invoice->customer_id,
                'return_reason' => $validated['return_reason'],
                'status' => 'pending',
                'created_by' => Auth::id(),
            ]);

            foreach ($validated['lines'] as $line) {
                $invoiceLine = $invoice->lines->firstWhere('id', $line['sales_invoice_line_id']);
                if (!$invoiceLine) {
                    throw new \RuntimeException('Invoice line tidak valid');
                }
                if ((float) $line['return_quantity'] > (float) $invoiceLine->quantity) {
                    throw new \RuntimeException('Qty return melebihi qty invoice');
                }

                $returnLine = $salesReturn->lines()->create([
                    'sales_invoice_line_id' => $invoiceLine->id,
                    'product_id' => $invoiceLine->product_id,
                    'product_name' => $invoiceLine->product_name,
                    'return_quantity' => $line['return_quantity'],
                    'accepted_quantity' => 0,
                    'rejected_quantity' => 0,
                    'unit_price' => $invoiceLine->unit_price,
                    'tax_amount' => 0,
                    'line_total' => 0,
                    'inspection_notes' => $line['inspection_notes'] ?? null,
                ]);
                $returnLine->calculateTotals();
                $returnLine->save();
            }

            $salesReturn->load('lines');
            $salesReturn->calculateTotals();
            $salesReturn->save();
        });
        } catch (\Throwable $e) {
            throw ValidationException::withMessages(['error' => $e->getMessage()]);
        }

        return redirect()->route('sales-returns.index')->with('success', 'Sales return created');
    }

    public function show(SalesReturn $salesReturn)
    {
        $salesReturn->load(['customer', 'salesInvoice', 'lines.salesInvoiceLine', 'createdBy']);

        return Inertia::render('SalesReturns/Show', [
            'salesReturn' => $salesReturn,
        ]);
    }

    public function approve(SalesReturn $salesReturn)
    {
        if ($salesReturn->status !== 'pending') {
            return back()->withErrors(['error' => 'Hanya return pending bisa diapprove']);
        }

        $salesReturn->update([
            'status' => 'approved',
            'approved_by' => Auth::id(),
            'approved_at' => now(),
        ]);

        return back()->with('success', 'Sales return approved');
    }

    public function receive(Request $request, SalesReturn $salesReturn)
    {
        if (!in_array($salesReturn->status, ['approved', 'received'], true)) {
            return back()->withErrors(['error' => 'Status return tidak valid untuk receive']);
        }

        $validated = $request->validate([
            'lines' => 'required|array|min:1',
            'lines.*.id' => 'required|exists:sales_return_lines,id',
            'lines.*.accepted_quantity' => 'required|numeric|min:0',
            'lines.*.rejected_quantity' => 'required|numeric|min:0',
            'lines.*.inspection_notes' => 'nullable|string',
        ]);

        DB::transaction(function () use ($validated, $salesReturn) {
            $salesReturn->load('lines');
            foreach ($validated['lines'] as $inputLine) {
                $line = $salesReturn->lines->firstWhere('id', $inputLine['id']);
                if (!$line) {
                    throw new \RuntimeException('Line return tidak ditemukan');
                }
                $totalChecked = (float) $inputLine['accepted_quantity'] + (float) $inputLine['rejected_quantity'];
                if ($totalChecked > (float) $line->return_quantity) {
                    throw new \RuntimeException('Qty inspeksi melebihi qty return');
                }

                $line->accepted_quantity = $inputLine['accepted_quantity'];
                $line->rejected_quantity = $inputLine['rejected_quantity'];
                $line->inspection_notes = $inputLine['inspection_notes'] ?? $line->inspection_notes;
                $line->save();
            }

            $salesReturn->status = 'received';
            $salesReturn->received_by = Auth::id();
            $salesReturn->received_at = now();
            $salesReturn->save();
        });

        return back()->with('success', 'Sales return received');
    }

    public function complete(SalesReturn $salesReturn)
    {
        if ($salesReturn->status !== 'received') {
            return back()->withErrors(['error' => 'Hanya return received bisa diselesaikan']);
        }

        DB::transaction(function () use ($salesReturn) {
            $salesReturn->status = 'completed';
            $salesReturn->completed_by = Auth::id();
            $salesReturn->completed_at = now();
            $salesReturn->save();

            $salesReturn->createJournalEntry();
        });

        return back()->with('success', 'Sales return completed');
    }

    public function reject(Request $request, SalesReturn $salesReturn)
    {
        if (!in_array($salesReturn->status, ['pending', 'approved'], true)) {
            return back()->withErrors(['error' => 'Status return tidak valid untuk reject']);
        }

        $validated = $request->validate([
            'reason' => 'required|string|max:1000',
        ]);

        $salesReturn->update([
            'status' => 'rejected',
            'rejected_by' => Auth::id(),
            'rejected_at' => now(),
            'rejection_reason' => $validated['reason'],
        ]);

        return back()->with('success', 'Sales return rejected');
    }
}

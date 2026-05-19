<?php

namespace App\Http\Controllers;

use App\Models\PurchaseInvoice;
use App\Models\PurchaseReturn;
use App\Models\PurchaseReturnLine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class PurchaseReturnController extends Controller
{
    public function index(Request $request)
    {
        $query = PurchaseReturn::with(['supplier', 'purchaseInvoice']);

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        if ($request->filled('search')) {
            $search = $request->string('search');
            $query->where(function ($q) use ($search) {
                $q->where('return_number', 'ilike', "%{$search}%")
                    ->orWhereHas('supplier', fn ($supplierQ) => $supplierQ->where('name', 'ilike', "%{$search}%"));
            });
        }

        return Inertia::render('PurchaseReturns/Index', [
            'returns' => $query->orderByDesc('return_date')->paginate(50)->withQueryString(),
            'filters' => $request->only(['status', 'search']),
        ]);
    }

    public function create(Request $request)
    {
        $invoiceId = $request->integer('purchase_invoice_id');
        $invoice = null;

        if ($invoiceId) {
            $invoice = PurchaseInvoice::with(['supplier', 'purchaseOrder', 'lines'])
                ->whereIn('status', ['posted', 'partially_paid', 'paid'])
                ->findOrFail($invoiceId);
        }

        return Inertia::render('PurchaseReturns/Create', [
            'invoices' => $this->availableInvoices(),
            'invoice' => $invoice,
            'lines' => $invoice ? $this->availableLines($invoice) : [],
        ]);
    }

    public function store(Request $request)
    {
        $validated = $this->validateReturn($request);

        $invoice = PurchaseInvoice::with(['supplier', 'purchaseOrder', 'lines'])
            ->whereIn('status', ['posted', 'partially_paid', 'paid'])
            ->findOrFail($validated['purchase_invoice_id']);

        DB::transaction(function () use ($validated, $invoice) {
            $purchaseReturn = PurchaseReturn::create([
                'return_number' => PurchaseReturn::generateNumber(),
                'return_date' => $validated['return_date'],
                'purchase_invoice_id' => $invoice->id,
                'purchase_order_id' => $invoice->purchase_order_id,
                'supplier_id' => $invoice->supplier_id,
                'return_reason' => $validated['return_reason'],
                'status' => 'draft',
                'created_by' => Auth::id(),
            ]);

            $this->syncLines($purchaseReturn, $invoice, $validated['lines']);
            $purchaseReturn->calculateTotals();
            $purchaseReturn->save();
        });

        return redirect()->route('purchase-returns.index')->with('success', 'Purchase return created');
    }

    public function show(PurchaseReturn $purchaseReturn)
    {
        $purchaseReturn->load([
            'supplier',
            'purchaseInvoice',
            'purchaseOrder',
            'lines.purchaseInvoiceLine',
            'createdBy',
        ]);

        return Inertia::render('PurchaseReturns/Show', [
            'purchaseReturn' => $purchaseReturn,
        ]);
    }

    public function approve(PurchaseReturn $purchaseReturn)
    {
        if ($purchaseReturn->status !== 'draft') {
            return back()->withErrors(['error' => 'Hanya return draft bisa diapprove']);
        }

        $purchaseReturn->update([
            'status' => 'approved',
            'approved_by' => Auth::id(),
            'approved_at' => now(),
        ]);

        return back()->with('success', 'Purchase return approved');
    }

    public function receive(Request $request, PurchaseReturn $purchaseReturn)
    {
        if ($purchaseReturn->status !== 'approved') {
            return back()->withErrors(['error' => 'Hanya return approved bisa diterima']);
        }

        $validated = $request->validate([
            'lines' => 'required|array|min:1',
            'lines.*.id' => 'required|exists:purchase_return_lines,id',
            'lines.*.accepted_quantity' => 'required|numeric|min:0',
            'lines.*.rejected_quantity' => 'required|numeric|min:0',
            'lines.*.inspection_notes' => 'nullable|string',
        ]);

        DB::transaction(function () use ($purchaseReturn, $validated) {
            $purchaseReturn->load('lines.purchaseInvoiceLine');

            foreach ($validated['lines'] as $lineInput) {
                $line = $purchaseReturn->lines->firstWhere('id', $lineInput['id']);
                if (!$line) {
                    abort(422, 'Line return tidak ditemukan');
                }
                $totalCheck = (float) $lineInput['accepted_quantity'] + (float) $lineInput['rejected_quantity'];
                if ($totalCheck > (float) $line->return_quantity) {
                    abort(422, 'Qty inspeksi melebihi qty return');
                }

                $line->accepted_quantity = $lineInput['accepted_quantity'];
                $line->rejected_quantity = $lineInput['rejected_quantity'];
                $line->inspection_notes = $lineInput['inspection_notes'] ?? $line->inspection_notes;
                $line->save();

                $invoiceLine = $line->purchaseInvoiceLine;
                $invoiceLine->invoice_quantity = max((float) $invoiceLine->invoice_quantity - (float) $line->accepted_quantity, 0);
                $invoiceLine->save();
            }

            $purchaseReturn->status = 'received';
            $purchaseReturn->received_by = Auth::id();
            $purchaseReturn->received_at = now();
            $purchaseReturn->updated_by = Auth::id();
            $purchaseReturn->save();
        });

        return back()->with('success', 'Purchase return received');
    }

    public function complete(PurchaseReturn $purchaseReturn)
    {
        if ($purchaseReturn->status !== 'received') {
            return back()->withErrors(['error' => 'Hanya return received bisa diselesaikan']);
        }

        DB::transaction(function () use ($purchaseReturn) {
            $purchaseReturn->status = 'completed';
            $purchaseReturn->completed_by = Auth::id();
            $purchaseReturn->completed_at = now();
            $purchaseReturn->updated_by = Auth::id();
            $purchaseReturn->save();
            $purchaseReturn->createJournalEntry();
        });

        return back()->with('success', 'Purchase return completed');
    }

    public function reject(Request $request, PurchaseReturn $purchaseReturn)
    {
        if (!in_array($purchaseReturn->status, ['draft', 'approved'], true)) {
            return back()->withErrors(['error' => 'Status return tidak valid untuk reject']);
        }

        $validated = $request->validate([
            'reason' => 'required|string|max:1000',
        ]);

        $purchaseReturn->update([
            'status' => 'rejected',
            'rejected_by' => Auth::id(),
            'rejected_at' => now(),
            'rejection_reason' => $validated['reason'],
            'updated_by' => Auth::id(),
        ]);

        return back()->with('success', 'Purchase return rejected');
    }

    private function validateReturn(Request $request): array
    {
        return $request->validate([
            'return_date' => 'required|date',
            'purchase_invoice_id' => 'required|exists:purchase_invoices,id',
            'return_reason' => 'required|string|max:255',
            'lines' => 'required|array|min:1',
            'lines.*.purchase_invoice_line_id' => 'required|exists:purchase_invoice_lines,id',
            'lines.*.return_quantity' => 'required|numeric|min:0.001',
            'lines.*.tax_percentage' => 'nullable|numeric|min:0',
            'lines.*.inspection_notes' => 'nullable|string',
        ]);
    }

    private function syncLines(PurchaseReturn $purchaseReturn, PurchaseInvoice $invoice, array $lines): void
    {
        $availableMap = collect($this->availableLines($invoice))->keyBy('purchase_invoice_line_id');

        foreach (array_values($lines) as $index => $lineData) {
            $source = $availableMap->get((int) $lineData['purchase_invoice_line_id']);
            if (!$source) {
                abort(422, 'Line invoice tidak valid untuk return');
            }
            if ((float) $lineData['return_quantity'] > (float) $source['remaining_quantity']) {
                abort(422, 'Qty return melebihi qty tersedia');
            }

            $line = new PurchaseReturnLine([
                'purchase_invoice_line_id' => $source['purchase_invoice_line_id'],
                'line_number' => $index + 1,
                'product_id' => $source['product_id'],
                'product_name' => $source['product_name'],
                'return_quantity' => $lineData['return_quantity'],
                'accepted_quantity' => 0,
                'rejected_quantity' => 0,
                'unit' => $source['unit'],
                'unit_price' => $source['unit_price'],
                'tax_percentage' => $lineData['tax_percentage'] ?? 11,
                'inspection_notes' => $lineData['inspection_notes'] ?? null,
            ]);
            $line->calculateTotals();
            $purchaseReturn->lines()->save($line);
        }
    }

    private function availableLines(PurchaseInvoice $invoice): array
    {
        $invoice->loadMissing('lines');

        return $invoice->lines
            ->map(function ($line) {
                $alreadyReturned = (float) PurchaseReturnLine::where('purchase_invoice_line_id', $line->id)
                    ->whereHas('purchaseReturn', fn ($q) => $q->whereNotIn('status', ['rejected']))
                    ->sum('return_quantity');
                $remaining = max((float) $line->invoice_quantity - $alreadyReturned, 0);

                return [
                    'purchase_invoice_line_id' => $line->id,
                    'product_id' => $line->product_id,
                    'product_name' => $line->product_name,
                    'unit' => $line->unit,
                    'unit_price' => (float) $line->unit_price,
                    'invoice_quantity' => (float) $line->invoice_quantity,
                    'already_returned' => $alreadyReturned,
                    'remaining_quantity' => $remaining,
                ];
            })
            ->filter(fn ($line) => $line['remaining_quantity'] > 0)
            ->values()
            ->all();
    }

    private function availableInvoices()
    {
        return PurchaseInvoice::with(['supplier'])
            ->whereIn('status', ['posted', 'partially_paid', 'paid'])
            ->orderByDesc('invoice_date')
            ->get();
    }
}

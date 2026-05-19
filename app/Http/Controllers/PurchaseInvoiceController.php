<?php

namespace App\Http\Controllers;

use App\Models\GoodsReceipt;
use App\Models\PurchaseInvoice;
use App\Models\PurchaseInvoiceLine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class PurchaseInvoiceController extends Controller
{
    public function index(Request $request)
    {
        $query = PurchaseInvoice::with(['supplier', 'purchaseOrder', 'goodsReceipt'])
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->when($request->filled('search'), function ($q) use ($request) {
                $q->where(function ($sub) use ($request) {
                    $sub->where('invoice_number', 'ilike', '%' . $request->search . '%')
                        ->orWhere('supplier_invoice_number', 'ilike', '%' . $request->search . '%')
                        ->orWhereHas('supplier', fn ($supplier) => $supplier->where('name', 'ilike', '%' . $request->search . '%'));
                });
            });

        return Inertia::render('PurchaseInvoices/Index', [
            'purchaseInvoices' => $query->orderByDesc('invoice_date')->paginate(50)->withQueryString(),
            'filters' => $request->only(['status', 'search']),
        ]);
    }

    public function create(Request $request)
    {
        $goodsReceipt = $request->filled('goods_receipt_id')
            ? GoodsReceipt::with(['supplier', 'purchaseOrder', 'lines.purchaseOrderLine'])
                ->where('status', 'received')
                ->findOrFail($request->goods_receipt_id)
            : null;

        return Inertia::render('PurchaseInvoices/Create', [
            'goodsReceipts' => $this->availableGoodsReceipts(),
            'goodsReceipt' => $goodsReceipt,
            'lines' => $goodsReceipt ? $this->availableLines($goodsReceipt) : [],
        ]);
    }

    public function store(Request $request)
    {
        $validated = $this->validateInvoice($request);
        $goodsReceipt = GoodsReceipt::with(['purchaseOrder.lines', 'lines.purchaseOrderLine', 'supplier'])
            ->where('status', 'received')
            ->findOrFail($validated['goods_receipt_id']);

        $invoice = DB::transaction(function () use ($validated, $goodsReceipt) {
            $invoice = PurchaseInvoice::create([
                'invoice_number' => PurchaseInvoice::generateNumber(),
                'invoice_date' => $validated['invoice_date'],
                'due_date' => $validated['due_date'],
                'goods_receipt_id' => $goodsReceipt->id,
                'purchase_order_id' => $goodsReceipt->purchase_order_id,
                'supplier_id' => $goodsReceipt->supplier_id,
                'supplier_invoice_number' => $validated['supplier_invoice_number'] ?? null,
                'tax_invoice_number' => $validated['tax_invoice_number'] ?? null,
                'generate_tax_invoice' => $validated['generate_tax_invoice'] ?? false,
                'notes' => $validated['notes'] ?? null,
                'status' => 'draft',
                'created_by' => Auth::id(),
            ]);

            $this->syncLines($invoice, $goodsReceipt, $validated['lines']);
            $invoice->calculateTotals();
            $invoice->save();

            return $invoice;
        });

        return redirect()->route('purchase-invoices.show', $invoice)->with('success', 'Purchase invoice created.');
    }

    public function show(PurchaseInvoice $purchaseInvoice)
    {
        $purchaseInvoice->load(['supplier', 'purchaseOrder', 'goodsReceipt', 'lines.goodsReceiptLine', 'createdBy', 'postedBy', 'journalEntry']);

        return Inertia::render('PurchaseInvoices/Show', [
            'purchaseInvoice' => $purchaseInvoice,
        ]);
    }

    public function post(PurchaseInvoice $purchaseInvoice)
    {
        $purchaseInvoice->post();

        return back()->with('success', 'Purchase invoice posted.');
    }

    public function cancel(PurchaseInvoice $purchaseInvoice)
    {
        abort_unless(in_array($purchaseInvoice->status, ['draft', 'posted', 'partially_paid'], true), 403, 'Invoice cannot be cancelled.');

        $purchaseInvoice->update([
            'status' => 'cancelled',
            'updated_by' => Auth::id(),
        ]);

        return back()->with('success', 'Purchase invoice cancelled.');
    }

    private function validateInvoice(Request $request): array
    {
        return $request->validate([
            'invoice_date' => ['required', 'date'],
            'due_date' => ['required', 'date', 'after_or_equal:invoice_date'],
            'goods_receipt_id' => ['required', 'exists:goods_receipts,id'],
            'supplier_invoice_number' => ['nullable', 'string', 'max:100'],
            'tax_invoice_number' => ['nullable', 'string', 'max:100'],
            'generate_tax_invoice' => ['nullable', 'boolean'],
            'notes' => ['nullable', 'string'],
            'lines' => ['required', 'array', 'min:1'],
            'lines.*.goods_receipt_line_id' => ['required', 'exists:goods_receipt_lines,id'],
            'lines.*.invoice_quantity' => ['required', 'numeric', 'min:0.001'],
            'lines.*.tax_percentage' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'lines.*.notes' => ['nullable', 'string'],
        ]);
    }

    private function syncLines(PurchaseInvoice $invoice, GoodsReceipt $goodsReceipt, array $lines): void
    {
        $available = collect($this->availableLines($goodsReceipt))->keyBy('goods_receipt_line_id');

        foreach (array_values($lines) as $index => $line) {
            $source = $available->get((int) $line['goods_receipt_line_id']);
            abort_if(!$source, 422, 'Invoice line not available from selected goods receipt.');

            $quantity = (float) $line['invoice_quantity'];
            abort_if($quantity > (float) $source['remaining_to_invoice_quantity'], 422, 'Invoice quantity exceeds remaining quantity.');

            $invoiceLine = new PurchaseInvoiceLine([
                'goods_receipt_line_id' => $source['goods_receipt_line_id'],
                'purchase_order_line_id' => $source['purchase_order_line_id'],
                'line_number' => $index + 1,
                'product_code' => $source['product_code'],
                'product_name' => $source['product_name'],
                'description' => $source['description'],
                'ordered_quantity' => $source['ordered_quantity'],
                'received_quantity' => $source['received_quantity'],
                'previously_invoiced_quantity' => $source['previously_invoiced_quantity'],
                'remaining_to_invoice_quantity' => $source['remaining_to_invoice_quantity'],
                'invoice_quantity' => $quantity,
                'unit' => $source['unit'],
                'unit_price' => $source['unit_price'],
                'tax_percentage' => $line['tax_percentage'] ?? 11,
                'notes' => $line['notes'] ?? null,
            ]);
            $invoiceLine->calculateTotals();
            $invoice->lines()->save($invoiceLine);
        }
    }

    private function availableLines(GoodsReceipt $goodsReceipt): array
    {
        $goodsReceipt->loadMissing('lines.purchaseOrderLine');

        return $goodsReceipt->lines->map(function ($line) {
            $alreadyInvoiced = (float) PurchaseInvoiceLine::where('goods_receipt_line_id', $line->id)->sum('invoice_quantity');
            $remaining = max((float) $line->accepted_quantity - $alreadyInvoiced, 0);
            $poLine = $line->purchaseOrderLine;

            return [
                'goods_receipt_line_id' => $line->id,
                'purchase_order_line_id' => $line->purchase_order_line_id,
                'product_code' => $line->product_code,
                'product_name' => $line->product_name,
                'description' => $line->description,
                'ordered_quantity' => (float) $line->po_quantity,
                'received_quantity' => (float) $line->accepted_quantity,
                'previously_invoiced_quantity' => $alreadyInvoiced,
                'remaining_to_invoice_quantity' => $remaining,
                'unit' => $line->unit,
                'unit_price' => $poLine?->unit_price ?? 0,
            ];
        })->filter(fn ($line) => $line['remaining_to_invoice_quantity'] > 0)->values()->all();
    }

    private function availableGoodsReceipts()
    {
        return GoodsReceipt::with(['supplier', 'purchaseOrder'])
            ->where('status', 'received')
            ->orderByDesc('gr_date')
            ->get(['id', 'gr_number', 'purchase_order_id', 'supplier_id', 'gr_date']);
    }
}

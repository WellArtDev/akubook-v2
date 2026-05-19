<?php

namespace App\Http\Controllers;

use App\Models\FakturPajak;
use App\Models\SalesInvoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class FakturPajakController extends Controller
{
    public function index(Request $request)
    {
        $query = FakturPajak::query()->with(['salesInvoice', 'customer'])->latest('faktur_date');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('faktur_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('faktur_date', '<=', $request->date_to);
        }

        if ($request->filled('search')) {
            $search = '%' . str_replace(['%', '_'], ['\\%', '\\_'], $request->search) . '%';
            $query->where(function ($q) use ($search) {
                $q->where('faktur_number', 'like', $search)
                    ->orWhereHas('salesInvoice', fn ($invoice) => $invoice->where('invoice_number', 'like', $search))
                    ->orWhereHas('customer', fn ($customer) => $customer->where('name', 'like', $search));
            });
        }

        return Inertia::render('FakturPajaks/Index', [
            'fakturs' => $query->paginate(50)->withQueryString(),
            'filters' => $request->only(['status', 'date_from', 'date_to', 'search']),
        ]);
    }

    public function create()
    {
        return Inertia::render('FakturPajaks/Create', [
            'salesInvoices' => $this->availableInvoices(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'faktur_date' => ['required', 'date'],
            'sales_invoice_id' => ['required', 'exists:sales_invoices,id'],
            'notes' => ['nullable', 'string'],
        ]);

        $invoice = SalesInvoice::query()->with('customer')->findOrFail($validated['sales_invoice_id']);

        abort_if($invoice->tax_amount <= 0, 422, 'Sales invoice tidak memiliki PPN.');

        $faktur = DB::transaction(function () use ($validated, $invoice) {
            return FakturPajak::query()->create([
                'faktur_number' => FakturPajak::generateNumber(),
                'faktur_date' => $validated['faktur_date'],
                'sales_invoice_id' => $invoice->id,
                'customer_id' => $invoice->customer_id,
                'dpp' => $invoice->subtotal - $invoice->discount_amount,
                'ppn_amount' => $invoice->tax_amount,
                'grand_total' => $invoice->grand_total,
                'status' => 'draft',
                'notes' => $validated['notes'] ?? null,
                'created_by' => Auth::id(),
            ]);
        });

        return redirect()->route('faktur-pajaks.show', $faktur)->with('success', 'Faktur Pajak created.');
    }

    public function show(FakturPajak $fakturPajak)
    {
        return Inertia::render('FakturPajaks/Show', [
            'faktur' => $fakturPajak->load(['salesInvoice', 'customer', 'creator']),
        ]);
    }

    public function issue(FakturPajak $fakturPajak)
    {
        abort_if($fakturPajak->status !== 'draft', 422, 'Only draft faktur can be issued.');

        $fakturPajak->update([
            'status' => 'issued',
            'issued_by' => Auth::id(),
            'issued_at' => now(),
        ]);

        return back()->with('success', 'Faktur Pajak issued.');
    }

    public function cancel(FakturPajak $fakturPajak)
    {
        abort_if($fakturPajak->status === 'cancelled', 422, 'Faktur already cancelled.');

        $fakturPajak->update([
            'status' => 'cancelled',
            'cancelled_by' => Auth::id(),
            'cancelled_at' => now(),
        ]);

        return back()->with('success', 'Faktur Pajak cancelled.');
    }

    private function availableInvoices()
    {
        $usedInvoiceIds = FakturPajak::query()->where('status', '!=', 'cancelled')->pluck('sales_invoice_id');

        return SalesInvoice::query()
            ->with('customer:id,name')
            ->whereNotIn('id', $usedInvoiceIds)
            ->where('tax_amount', '>', 0)
            ->latest('invoice_date')
            ->limit(100)
            ->get(['id', 'invoice_number', 'invoice_date', 'customer_id', 'subtotal', 'discount_amount', 'tax_amount', 'grand_total']);
    }
}

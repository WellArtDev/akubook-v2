<?php

namespace App\Http\Controllers;

use App\Models\PurchaseInvoice;
use App\Models\Supplier;
use App\Models\SupplierPayment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class SupplierPaymentController extends Controller
{
    public function index(Request $request)
    {
        $query = SupplierPayment::with('supplier')
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->when($request->filled('search'), function ($q) use ($request) {
                $q->where(function ($sub) use ($request) {
                    $sub->where('payment_number', 'ilike', '%' . $request->search . '%')
                        ->orWhereHas('supplier', fn ($supplier) => $supplier->where('name', 'ilike', '%' . $request->search . '%'));
                });
            });

        return Inertia::render('SupplierPayments/Index', [
            'payments' => $query->orderByDesc('payment_date')->paginate(50)->withQueryString(),
            'filters' => $request->only(['status', 'search']),
        ]);
    }

    public function create()
    {
        return Inertia::render('SupplierPayments/Create', [
            'suppliers' => Supplier::where('is_active', true)->orderBy('name')->get(['id', 'name', 'code']),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'payment_date' => ['required', 'date'],
            'supplier_id' => ['required', 'exists:suppliers,id'],
            'payment_method' => ['required', 'in:cash,bank_transfer,check,giro'],
            'bank_account_id' => ['nullable', 'integer'],
            'reference_number' => ['nullable', 'string', 'max:100'],
            'total_amount' => ['required', 'numeric', 'min:0.01'],
            'notes' => ['nullable', 'string'],
            'allocations' => ['nullable', 'array'],
            'allocations.*.purchase_invoice_id' => ['required', 'exists:purchase_invoices,id'],
            'allocations.*.allocated_amount' => ['required', 'numeric', 'min:0.01'],
        ]);

        $payment = SupplierPayment::create([
            'payment_number' => SupplierPayment::generatePaymentNumber(),
            'payment_date' => $validated['payment_date'],
            'supplier_id' => $validated['supplier_id'],
            'payment_method' => $validated['payment_method'],
            'bank_account_id' => $validated['bank_account_id'] ?? null,
            'reference_number' => $validated['reference_number'] ?? null,
            'total_amount' => $validated['total_amount'],
            'allocated_amount' => 0,
            'unapplied_amount' => $validated['total_amount'],
            'status' => 'draft',
            'notes' => $validated['notes'] ?? null,
            'created_by' => Auth::id(),
        ]);

        if (!empty($validated['allocations'])) {
            $payment->allocateToInvoices($validated['allocations']);
        }

        return redirect()->route('supplier-payments.show', $payment)->with('success', 'Supplier payment created.');
    }

    public function show(SupplierPayment $supplierPayment)
    {
        $supplierPayment->load(['supplier', 'allocations.purchaseInvoice', 'journalEntry.lines']);

        return Inertia::render('SupplierPayments/Show', [
            'payment' => $supplierPayment,
        ]);
    }

    public function post(SupplierPayment $supplierPayment)
    {
        $supplierPayment->post();

        return back()->with('success', 'Supplier payment posted.');
    }

    public function void(Request $request, SupplierPayment $supplierPayment)
    {
        $validated = $request->validate([
            'reason' => ['required', 'string', 'max:500'],
        ]);

        $supplierPayment->update([
            'status' => 'voided',
            'notes' => trim(($supplierPayment->notes ? $supplierPayment->notes . "\n\n" : '') . 'VOIDED: ' . $validated['reason']),
            'updated_by' => Auth::id(),
        ]);

        return back()->with('success', 'Supplier payment voided.');
    }

    public function getUnpaidInvoices(Request $request)
    {
        $supplierId = $request->input('supplier_id');

        return PurchaseInvoice::where('supplier_id', $supplierId)
            ->whereIn('status', ['posted', 'partially_paid'])
            ->where('outstanding_amount', '>', 0)
            ->orderBy('invoice_date')
            ->get(['id', 'invoice_number', 'invoice_date', 'total_amount', 'paid_amount', 'outstanding_amount']);
    }
}

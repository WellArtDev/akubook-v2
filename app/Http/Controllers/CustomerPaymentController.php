<?php

namespace App\Http\Controllers;

use App\Models\CustomerPayment;
use App\Models\Customer;
use App\Models\SalesInvoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class CustomerPaymentController extends Controller
{
    public function index(Request $request)
    {
        $query = CustomerPayment::with(['customer']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('payment_number', 'ilike', '%' . $request->search . '%')
                  ->orWhereHas('customer', function($sq) use ($request) {
                      $sq->where('name', 'ilike', '%' . $request->search . '%');
                  });
            });
        }

        $payments = $query->orderBy('payment_date', 'desc')
            ->orderBy('payment_number', 'desc')
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('CustomerPayments/Index', [
            'payments' => $payments,
            'filters' => $request->only(['status', 'search']),
        ]);
    }

    public function create()
    {
        $customers = Customer::where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'code']);

        return Inertia::render('CustomerPayments/Create', [
            'customers' => $customers,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'payment_date' => 'required|date',
            'customer_id' => 'required|exists:customers,id',
            'payment_method' => 'required|in:cash,bank_transfer,check,credit_card,giro',
            'bank_account_id' => 'nullable|integer',
            'reference_number' => 'nullable|string|max:100',
            'total_amount' => 'required|numeric|min:0.01',
            'notes' => 'nullable|string',
            'allocations' => 'nullable|array',
            'allocations.*.sales_invoice_id' => 'required|exists:sales_invoices,id',
            'allocations.*.allocated_amount' => 'required|numeric|min:0.01',
        ]);

        try {
            $payment = CustomerPayment::create([
                'payment_number' => CustomerPayment::generatePaymentNumber(),
                'payment_date' => $validated['payment_date'],
                'customer_id' => $validated['customer_id'],
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

            // Allocate to invoices if provided
            if (!empty($validated['allocations'])) {
                $payment->allocateToInvoices($validated['allocations']);
            }

            return redirect()->route('customer-payments.show', $payment)
                ->with('success', 'Payment created successfully');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to create payment: ' . $e->getMessage()]);
        }
    }

    public function show(CustomerPayment $customerPayment)
    {
        $customerPayment->load(['customer', 'allocations.salesInvoice', 'createdBy']);

        return Inertia::render('CustomerPayments/Show', [
            'payment' => $customerPayment,
        ]);
    }

    public function edit(CustomerPayment $customerPayment)
    {
        if ($customerPayment->status !== 'draft') {
            return back()->withErrors(['error' => 'Only draft payments can be edited']);
        }

        $customerPayment->load(['customer', 'allocations.salesInvoice']);

        $customers = Customer::where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'code']);

        return Inertia::render('CustomerPayments/Edit', [
            'payment' => $customerPayment,
            'customers' => $customers,
        ]);
    }

    public function update(Request $request, CustomerPayment $customerPayment)
    {
        if ($customerPayment->status !== 'draft') {
            return back()->withErrors(['error' => 'Only draft payments can be updated']);
        }

        $validated = $request->validate([
            'payment_date' => 'required|date',
            'payment_method' => 'required|in:cash,bank_transfer,check,credit_card,giro',
            'bank_account_id' => 'nullable|integer',
            'reference_number' => 'nullable|string|max:100',
            'total_amount' => 'required|numeric|min:0.01',
            'notes' => 'nullable|string',
        ]);

        try {
            $customerPayment->update([
                'payment_date' => $validated['payment_date'],
                'payment_method' => $validated['payment_method'],
                'bank_account_id' => $validated['bank_account_id'] ?? null,
                'reference_number' => $validated['reference_number'] ?? null,
                'total_amount' => $validated['total_amount'],
                'notes' => $validated['notes'] ?? null,
                'updated_by' => Auth::id(),
            ]);

            // Recalculate unapplied amount
            $customerPayment->unapplied_amount = $customerPayment->total_amount - $customerPayment->allocated_amount;
            $customerPayment->save();

            return redirect()->route('customer-payments.show', $customerPayment)
                ->with('success', 'Payment updated successfully');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to update payment: ' . $e->getMessage()]);
        }
    }

    public function destroy(CustomerPayment $customerPayment)
    {
        if ($customerPayment->status !== 'draft') {
            return back()->withErrors(['error' => 'Only draft payments can be deleted']);
        }

        $customerPayment->delete();

        return redirect()->route('customer-payments.index')
            ->with('success', 'Payment deleted successfully');
    }

    public function post(CustomerPayment $customerPayment)
    {
        if ($customerPayment->status !== 'draft') {
            return back()->withErrors(['error' => 'Only draft payments can be posted']);
        }

        try {
            $customerPayment->post();

            return back()->with('success', 'Payment posted successfully');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to post payment: ' . $e->getMessage()]);
        }
    }

    public function void(Request $request, CustomerPayment $customerPayment)
    {
        $validated = $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        try {
            $success = $customerPayment->void(Auth::id(), $validated['reason']);

            if (!$success) {
                return back()->withErrors(['error' => 'Cannot void reconciled payments']);
            }

            return back()->with('success', 'Payment voided successfully');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to void payment: ' . $e->getMessage()]);
        }
    }

    public function getUnpaidInvoices(Request $request)
    {
        $customerId = $request->input('customer_id');

        $invoices = SalesInvoice::where('customer_id', $customerId)
            ->whereIn('status', ['sent', 'partially_paid', 'overdue'])
            ->where('amount_due', '>', 0)
            ->orderBy('invoice_date')
            ->get(['id', 'invoice_number', 'invoice_date', 'grand_total', 'amount_paid', 'amount_due']);

        return response()->json($invoices);
    }
}

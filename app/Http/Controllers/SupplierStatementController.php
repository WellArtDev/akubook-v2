<?php

namespace App\Http\Controllers;

use App\Models\PurchaseInvoice;
use App\Models\PurchaseReturn;
use App\Models\Supplier;
use App\Models\SupplierPayment;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SupplierStatementController extends Controller
{
    public function index(Request $request)
    {
        $validated = $request->validate([
            'supplier_id' => ['nullable', 'exists:suppliers,id'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
        ]);

        $dateFrom = $validated['date_from'] ?? now()->startOfMonth()->toDateString();
        $dateTo = $validated['date_to'] ?? now()->toDateString();
        $supplierId = $validated['supplier_id'] ?? null;

        $suppliers = Supplier::query()->orderBy('name')->get(['id', 'supplier_code', 'name']);

        if (!$supplierId) {
            return Inertia::render('SupplierStatements/Index', [
                'suppliers' => $suppliers,
                'filters' => [
                    'supplier_id' => null,
                    'date_from' => $dateFrom,
                    'date_to' => $dateTo,
                ],
                'statement' => null,
            ]);
        }

        $supplier = Supplier::findOrFail($supplierId);
        $statement = $this->buildStatement($supplierId, $dateFrom, $dateTo);

        return Inertia::render('SupplierStatements/Index', [
            'suppliers' => $suppliers,
            'filters' => [
                'supplier_id' => $supplierId,
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
            ],
            'statement' => [
                'supplier' => [
                    'id' => $supplier->id,
                    'supplier_code' => $supplier->supplier_code,
                    'name' => $supplier->name,
                ],
                ...$statement,
            ],
        ]);
    }

    public function pdf(Request $request)
    {
        $validated = $request->validate([
            'supplier_id' => ['required', 'exists:suppliers,id'],
            'date_from' => ['required', 'date'],
            'date_to' => ['required', 'date', 'after_or_equal:date_from'],
        ]);

        $supplier = Supplier::findOrFail($validated['supplier_id']);
        $statement = $this->buildStatement($supplier->id, $validated['date_from'], $validated['date_to']);

        return response()->json([
            'supplier' => [
                'id' => $supplier->id,
                'supplier_code' => $supplier->supplier_code,
                'name' => $supplier->name,
            ],
            'date_from' => $validated['date_from'],
            'date_to' => $validated['date_to'],
            ...$statement,
            'format' => 'pdf-ready-json',
        ]);
    }

    private function buildStatement(int $supplierId, string $dateFrom, string $dateTo): array
    {
        $openingDebit = (float) PurchaseInvoice::query()
            ->where('supplier_id', $supplierId)
            ->whereDate('invoice_date', '<', $dateFrom)
            ->sum('total_amount');

        $openingCreditPayments = (float) SupplierPayment::query()
            ->where('supplier_id', $supplierId)
            ->where('status', 'posted')
            ->whereDate('payment_date', '<', $dateFrom)
            ->sum('total_amount');

        $openingCreditReturns = (float) PurchaseReturn::query()
            ->where('supplier_id', $supplierId)
            ->whereDate('return_date', '<', $dateFrom)
            ->sum('total_amount');

        $openingBalance = $openingDebit - ($openingCreditPayments + $openingCreditReturns);

        $transactions = collect();

        $invoices = PurchaseInvoice::query()
            ->where('supplier_id', $supplierId)
            ->whereDate('invoice_date', '>=', $dateFrom)
            ->whereDate('invoice_date', '<=', $dateTo)
            ->get(['id', 'invoice_number', 'invoice_date', 'total_amount']);

        foreach ($invoices as $invoice) {
            $transactions->push([
                'date' => $invoice->invoice_date->toDateString(),
                'type' => 'invoice',
                'reference' => $invoice->invoice_number,
                'description' => 'Purchase Invoice',
                'debit' => (float) $invoice->total_amount,
                'credit' => 0,
            ]);
        }

        $payments = SupplierPayment::query()
            ->where('supplier_id', $supplierId)
            ->where('status', 'posted')
            ->whereDate('payment_date', '>=', $dateFrom)
            ->whereDate('payment_date', '<=', $dateTo)
            ->get(['id', 'payment_number', 'payment_date', 'total_amount']);

        foreach ($payments as $payment) {
            $transactions->push([
                'date' => $payment->payment_date->toDateString(),
                'type' => 'payment',
                'reference' => $payment->payment_number,
                'description' => 'Supplier Payment',
                'debit' => 0,
                'credit' => (float) $payment->total_amount,
            ]);
        }

        $returns = PurchaseReturn::query()
            ->where('supplier_id', $supplierId)
            ->whereDate('return_date', '>=', $dateFrom)
            ->whereDate('return_date', '<=', $dateTo)
            ->get(['id', 'return_number', 'return_date', 'total_amount']);

        foreach ($returns as $return) {
            $transactions->push([
                'date' => $return->return_date->toDateString(),
                'type' => 'debit_note',
                'reference' => $return->return_number,
                'description' => 'Purchase Return / Debit Note',
                'debit' => 0,
                'credit' => (float) $return->total_amount,
            ]);
        }

        $transactions = $transactions
            ->sortBy(fn ($row) => $row['date'] . '-' . $row['reference'])
            ->values();

        $runningBalance = $openingBalance;
        $transactions = $transactions->map(function ($row) use (&$runningBalance) {
            $runningBalance += ((float) $row['debit'] - (float) $row['credit']);
            $row['balance'] = $runningBalance;

            return $row;
        });

        return [
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
            'opening_balance' => $openingBalance,
            'closing_balance' => $runningBalance,
            'total_debit' => (float) $transactions->sum('debit'),
            'total_credit' => (float) $transactions->sum('credit'),
            'transactions' => $transactions->all(),
        ];
    }
}

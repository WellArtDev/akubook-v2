<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\CustomerPayment;
use App\Models\SalesInvoice;
use App\Models\SalesReturn;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CustomerStatementController extends Controller
{
    public function index(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => ['nullable', 'exists:customers,id'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
        ]);

        $dateFrom = $validated['date_from'] ?? now()->startOfMonth()->toDateString();
        $dateTo = $validated['date_to'] ?? now()->toDateString();
        $customerId = $validated['customer_id'] ?? null;

        $customers = Customer::query()->orderBy('name')->get(['id', 'code', 'name', 'email']);

        if (!$customerId) {
            return Inertia::render('CustomerStatements/Index', [
                'customers' => $customers,
                'filters' => [
                    'customer_id' => null,
                    'date_from' => $dateFrom,
                    'date_to' => $dateTo,
                ],
                'statement' => null,
            ]);
        }

        $customer = Customer::findOrFail($customerId);
        $statement = $this->buildStatement($customerId, $dateFrom, $dateTo);

        return Inertia::render('CustomerStatements/Index', [
            'customers' => $customers,
            'filters' => [
                'customer_id' => $customerId,
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
            ],
            'statement' => [
                'customer' => [
                    'id' => $customer->id,
                    'code' => $customer->code,
                    'name' => $customer->name,
                    'email' => $customer->email,
                ],
                ...$statement,
            ],
        ]);
    }

    public function pdf(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => ['required', 'exists:customers,id'],
            'date_from' => ['required', 'date'],
            'date_to' => ['required', 'date', 'after_or_equal:date_from'],
        ]);

        $customer = Customer::findOrFail($validated['customer_id']);
        $statement = $this->buildStatement($customer->id, $validated['date_from'], $validated['date_to']);

        return response()->json([
            'customer' => [
                'id' => $customer->id,
                'code' => $customer->code,
                'name' => $customer->name,
                'email' => $customer->email,
            ],
            'date_from' => $validated['date_from'],
            'date_to' => $validated['date_to'],
            ...$statement,
            'format' => 'pdf-ready-json',
        ]);
    }

    private function buildStatement(int $customerId, string $dateFrom, string $dateTo): array
    {
        $openingDebit = (float) SalesInvoice::query()
            ->where('customer_id', $customerId)
            ->whereDate('invoice_date', '<', $dateFrom)
            ->sum('grand_total');

        $openingCreditPayments = (float) CustomerPayment::query()
            ->where('customer_id', $customerId)
            ->where('status', 'posted')
            ->whereDate('payment_date', '<', $dateFrom)
            ->sum('total_amount');

        $openingCreditReturns = (float) SalesReturn::query()
            ->where('customer_id', $customerId)
            ->whereDate('return_date', '<', $dateFrom)
            ->sum('total_amount');

        $openingBalance = $openingDebit - ($openingCreditPayments + $openingCreditReturns);
        $transactions = collect();

        $invoices = SalesInvoice::query()
            ->where('customer_id', $customerId)
            ->whereDate('invoice_date', '>=', $dateFrom)
            ->whereDate('invoice_date', '<=', $dateTo)
            ->get(['id', 'invoice_number', 'invoice_date', 'grand_total']);

        foreach ($invoices as $invoice) {
            $transactions->push([
                'date' => $invoice->invoice_date->toDateString(),
                'type' => 'invoice',
                'reference' => $invoice->invoice_number,
                'description' => 'Sales Invoice',
                'debit' => (float) $invoice->grand_total,
                'credit' => 0,
            ]);
        }

        $payments = CustomerPayment::query()
            ->where('customer_id', $customerId)
            ->where('status', 'posted')
            ->whereDate('payment_date', '>=', $dateFrom)
            ->whereDate('payment_date', '<=', $dateTo)
            ->get(['id', 'payment_number', 'payment_date', 'total_amount']);

        foreach ($payments as $payment) {
            $transactions->push([
                'date' => $payment->payment_date->toDateString(),
                'type' => 'payment',
                'reference' => $payment->payment_number,
                'description' => 'Customer Payment',
                'debit' => 0,
                'credit' => (float) $payment->total_amount,
            ]);
        }

        $returns = SalesReturn::query()
            ->where('customer_id', $customerId)
            ->whereDate('return_date', '>=', $dateFrom)
            ->whereDate('return_date', '<=', $dateTo)
            ->get(['id', 'rma_number', 'return_date', 'total_amount']);

        foreach ($returns as $return) {
            $transactions->push([
                'date' => $return->return_date->toDateString(),
                'type' => 'credit_note',
                'reference' => $return->rma_number,
                'description' => 'Sales Return / Credit Note',
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
            'aging' => $this->aging($customerId, $dateTo),
            'transactions' => $transactions->all(),
        ];
    }

    private function aging(int $customerId, string $dateTo): array
    {
        $buckets = [
            '0_30' => 0,
            '31_60' => 0,
            '61_90' => 0,
            'over_90' => 0,
        ];

        SalesInvoice::query()
            ->where('customer_id', $customerId)
            ->where('amount_due', '>', 0)
            ->whereDate('invoice_date', '<=', $dateTo)
            ->get(['due_date', 'amount_due'])
            ->each(function ($invoice) use (&$buckets, $dateTo) {
                $age = $invoice->due_date->diffInDays($dateTo, false);

                if ($age <= 30) {
                    $buckets['0_30'] += (float) $invoice->amount_due;
                } elseif ($age <= 60) {
                    $buckets['31_60'] += (float) $invoice->amount_due;
                } elseif ($age <= 90) {
                    $buckets['61_90'] += (float) $invoice->amount_due;
                } else {
                    $buckets['over_90'] += (float) $invoice->amount_due;
                }
            });

        return $buckets;
    }
}

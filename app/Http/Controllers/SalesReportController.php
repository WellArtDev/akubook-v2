<?php

namespace App\Http\Controllers;

use App\Models\SalesInvoice;
use App\Models\SalesOrder;
use App\Models\SalesOrderLine;
use App\Models\SalesQuotation;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SalesReportController extends Controller
{
    public function index(Request $request)
    {
        $validated = $request->validate([
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
        ]);

        $dateFrom = $validated['date_from'] ?? now()->startOfMonth()->toDateString();
        $dateTo = $validated['date_to'] ?? now()->toDateString();

        $salesOrders = SalesOrder::whereBetween('so_date', [$dateFrom, $dateTo]);
        $previousFrom = now()->parse($dateFrom)->subMonth()->toDateString();
        $previousTo = now()->parse($dateTo)->subMonth()->toDateString();
        $previousSales = (float) SalesOrder::whereBetween('so_date', [$previousFrom, $previousTo])->sum('grand_total');
        $currentSales = (float) $salesOrders->clone()->sum('grand_total');

        $summary = [
            'total_sales' => $currentSales,
            'transaction_count' => $salesOrders->clone()->count(),
            'average_order_value' => (float) ($salesOrders->clone()->avg('grand_total') ?? 0),
            'growth_percent' => $previousSales > 0 ? (($currentSales - $previousSales) / $previousSales) * 100 : 0,
        ];

        $byCustomer = SalesOrder::query()
            ->join('customers', 'sales_orders.customer_id', '=', 'customers.id')
            ->whereBetween('sales_orders.so_date', [$dateFrom, $dateTo])
            ->groupBy('customers.id', 'customers.name')
            ->selectRaw('customers.id, customers.name as customer_name, SUM(sales_orders.grand_total) as total_sales, COUNT(sales_orders.id) as order_count')
            ->orderByDesc('total_sales')
            ->limit(20)
            ->get();

        $byProduct = SalesOrderLine::query()
            ->join('sales_orders', 'sales_order_lines.sales_order_id', '=', 'sales_orders.id')
            ->whereBetween('sales_orders.so_date', [$dateFrom, $dateTo])
            ->groupBy('sales_order_lines.item_id', 'sales_order_lines.description')
            ->selectRaw('sales_order_lines.item_id, sales_order_lines.description as product_name, SUM(sales_order_lines.quantity) as quantity_sold, SUM(sales_order_lines.line_total) as revenue')
            ->orderByDesc('revenue')
            ->limit(20)
            ->get();

        $bySalesperson = SalesOrder::query()
            ->join('users', 'sales_orders.sales_person_id', '=', 'users.id')
            ->whereBetween('sales_orders.so_date', [$dateFrom, $dateTo])
            ->groupBy('users.id', 'users.name')
            ->selectRaw('users.id, users.name as salesperson_name, SUM(sales_orders.grand_total) as total_sales, COUNT(sales_orders.id) as order_count')
            ->orderByDesc('total_sales')
            ->limit(20)
            ->get();

        $quotationCount = SalesQuotation::whereBetween('quotation_date', [$dateFrom, $dateTo])->count();
        $convertedCount = SalesQuotation::whereBetween('quotation_date', [$dateFrom, $dateTo])->whereNotNull('converted_to_sales_order_id')->count();

        $pipeline = [
            'quotations_pending' => SalesQuotation::whereBetween('quotation_date', [$dateFrom, $dateTo])->where('status', 'pending_approval')->count(),
            'quotations_approved' => SalesQuotation::whereBetween('quotation_date', [$dateFrom, $dateTo])->where('status', 'approved')->count(),
            'quotations_converted' => $convertedCount,
            'sales_orders_pending' => SalesOrder::whereBetween('so_date', [$dateFrom, $dateTo])->where('status', 'pending_approval')->count(),
            'sales_orders_approved' => SalesOrder::whereBetween('so_date', [$dateFrom, $dateTo])->where('status', 'approved')->count(),
            'invoices_paid' => SalesInvoice::whereBetween('invoice_date', [$dateFrom, $dateTo])->where('status', 'paid')->count(),
            'invoices_unpaid' => SalesInvoice::whereBetween('invoice_date', [$dateFrom, $dateTo])->whereIn('status', ['sent', 'partially_paid'])->count(),
            'invoices_overdue' => SalesInvoice::whereBetween('invoice_date', [$dateFrom, $dateTo])->where('status', 'overdue')->count(),
            'conversion_rate' => $quotationCount > 0 ? ($convertedCount / $quotationCount) * 100 : 0,
        ];

        $agingRows = SalesInvoice::query()
            ->join('customers', 'sales_invoices.customer_id', '=', 'customers.id')
            ->whereIn('sales_invoices.status', ['sent', 'partially_paid', 'overdue'])
            ->where('sales_invoices.amount_due', '>', 0)
            ->whereBetween('sales_invoices.invoice_date', [$dateFrom, $dateTo])
            ->selectRaw('customers.name as customer_name, sales_invoices.amount_due, sales_invoices.due_date')
            ->get();

        $aging = [
            '0_30' => 0,
            '31_60' => 0,
            '61_90' => 0,
            'over_90' => 0,
            'by_customer' => [],
        ];

        foreach ($agingRows as $row) {
            $days = max(now()->diffInDays(now()->parse($row->due_date), false) * -1, 0);
            $amount = (float) $row->amount_due;
            if ($days <= 30) {
                $aging['0_30'] += $amount;
            } elseif ($days <= 60) {
                $aging['31_60'] += $amount;
            } elseif ($days <= 90) {
                $aging['61_90'] += $amount;
            } else {
                $aging['over_90'] += $amount;
            }
            $aging['by_customer'][$row->customer_name] = ($aging['by_customer'][$row->customer_name] ?? 0) + $amount;
        }

        return Inertia::render('SalesReports/Index', [
            'filters' => ['date_from' => $dateFrom, 'date_to' => $dateTo],
            'generated_at' => now()->toDateTimeString(),
            'summary' => $summary,
            'by_customer' => $byCustomer,
            'by_product' => $byProduct,
            'by_salesperson' => $bySalesperson,
            'pipeline' => $pipeline,
            'aging' => $aging,
        ]);
    }

    public function export(Request $request)
    {
        $validated = $request->validate([
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
        ]);

        $dateFrom = $validated['date_from'] ?? now()->startOfMonth()->toDateString();
        $dateTo = $validated['date_to'] ?? now()->toDateString();

        $rows = SalesOrder::query()
            ->with(['customer:id,name', 'salesPerson:id,name'])
            ->whereBetween('so_date', [$dateFrom, $dateTo])
            ->orderByDesc('so_date')
            ->get()
            ->map(fn (SalesOrder $order) => [
                'so_number' => $order->so_number,
                'so_date' => $order->so_date?->toDateString(),
                'customer' => $order->customer?->name,
                'salesperson' => $order->salesPerson?->name,
                'status' => $order->status,
                'grand_total' => (float) $order->grand_total,
            ])
            ->values();

        return response()->json([
            'period' => ['date_from' => $dateFrom, 'date_to' => $dateTo],
            'rows' => $rows,
        ]);
    }
}

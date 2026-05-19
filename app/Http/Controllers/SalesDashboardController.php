<?php

namespace App\Http\Controllers;

use App\Models\CustomerPayment;
use App\Models\SalesInvoice;
use App\Models\SalesOrder;
use App\Models\SalesOrderLine;
use App\Models\SalesQuotation;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SalesDashboardController extends Controller
{
    public function index(Request $request)
    {
        $validated = $request->validate([
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
        ]);

        $dateFrom = $validated['date_from'] ?? now()->startOfYear()->toDateString();
        $dateTo = $validated['date_to'] ?? now()->toDateString();

        $todaySales = (float) SalesOrder::whereDate('so_date', now()->toDateString())->sum('grand_total');
        $thisMonthSales = (float) SalesOrder::whereMonth('so_date', now()->month)
            ->whereYear('so_date', now()->year)
            ->sum('grand_total');
        $thisYearSales = (float) SalesOrder::whereYear('so_date', now()->year)->sum('grand_total');
        $pendingQuotations = SalesQuotation::whereIn('status', ['draft', 'sent'])->count();
        $pendingApprovals = SalesOrder::where('status', 'pending_approval')->count();
        $overdueInvoices = SalesInvoice::whereIn('status', ['sent', 'partially_paid', 'overdue'])
            ->where('amount_due', '>', 0)
            ->whereDate('due_date', '<', now()->toDateString())
            ->count();

        $salesTrend = collect(range(11, 0))->map(function ($monthsAgo) {
            $start = now()->subMonths($monthsAgo)->startOfMonth();
            $end = (clone $start)->endOfMonth();
            $previousStart = (clone $start)->subYear();
            $previousEnd = (clone $end)->subYear();

            return [
                'month' => $start->format('Y-m'),
                'total' => (float) SalesOrder::whereBetween('so_date', [$start->toDateString(), $end->toDateString()])->sum('grand_total'),
                'previous_year_total' => (float) SalesOrder::whereBetween('so_date', [$previousStart->toDateString(), $previousEnd->toDateString()])->sum('grand_total'),
            ];
        })->values();

        $topCustomers = SalesOrder::query()
            ->join('customers', 'customers.id', '=', 'sales_orders.customer_id')
            ->select('customers.id', 'customers.name')
            ->selectRaw('COALESCE(sum(sales_orders.grand_total),0) as total')
            ->selectRaw('count(sales_orders.id) as order_count')
            ->whereBetween('sales_orders.so_date', [$dateFrom, $dateTo])
            ->groupBy('customers.id', 'customers.name')
            ->orderByDesc('total')
            ->limit(10)
            ->get();

        $topProducts = SalesOrderLine::query()
            ->join('sales_orders', 'sales_orders.id', '=', 'sales_order_lines.sales_order_id')
            ->leftJoin('items', 'items.id', '=', 'sales_order_lines.item_id')
            ->select('sales_order_lines.item_id')
            ->selectRaw("COALESCE(items.name, sales_order_lines.description) as product_name")
            ->selectRaw('COALESCE(sum(sales_order_lines.quantity),0) as quantity')
            ->selectRaw('COALESCE(sum(sales_order_lines.line_total),0) as revenue')
            ->whereBetween('sales_orders.so_date', [$dateFrom, $dateTo])
            ->groupBy('sales_order_lines.item_id', 'items.name', 'sales_order_lines.description')
            ->orderByDesc('quantity')
            ->limit(10)
            ->get();

        $topSalespeople = SalesOrder::query()
            ->join('users', 'users.id', '=', 'sales_orders.sales_person_id')
            ->select('users.id', 'users.name')
            ->selectRaw('COALESCE(sum(sales_orders.grand_total),0) as total')
            ->selectRaw('count(sales_orders.id) as order_count')
            ->whereBetween('sales_orders.so_date', [$dateFrom, $dateTo])
            ->groupBy('users.id', 'users.name')
            ->orderByDesc('total')
            ->limit(10)
            ->get();

        $recentActivity = [
            'quotations' => SalesQuotation::with('customer:id,name')->latest('quotation_date')->limit(5)->get(['id', 'quotation_number', 'quotation_date', 'valid_until', 'customer_id', 'status', 'grand_total']),
            'orders' => SalesOrder::with('customer:id,name')->latest('so_date')->limit(5)->get(['id', 'so_number', 'so_date', 'customer_id', 'status', 'grand_total']),
            'invoices' => SalesInvoice::with('customer:id,name')->latest('invoice_date')->limit(5)->get(['id', 'invoice_number', 'invoice_date', 'customer_id', 'status', 'grand_total', 'amount_due']),
            'payments' => CustomerPayment::with('customer:id,name')->latest('payment_date')->limit(5)->get(['id', 'payment_number', 'payment_date', 'customer_id', 'status', 'total_amount']),
        ];

        return Inertia::render('SalesDashboard/Index', [
            'filters' => [
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
            ],
            'kpis' => [
                'today_sales' => $todaySales,
                'this_month_sales' => $thisMonthSales,
                'this_year_sales' => $thisYearSales,
                'pending_quotations' => $pendingQuotations,
                'pending_approvals' => $pendingApprovals,
                'overdue_invoices' => $overdueInvoices,
            ],
            'charts' => [
                'sales_trend' => $salesTrend,
                'top_customers' => $topCustomers,
                'top_products' => $topProducts,
                'top_salespeople' => $topSalespeople,
            ],
            'recent_activity' => $recentActivity,
            'alerts' => [
                'pending_approvals' => $pendingApprovals,
                'overdue_invoices' => $overdueInvoices,
            ],
            'generated_at' => now()->toDateTimeString(),
        ]);
    }
}

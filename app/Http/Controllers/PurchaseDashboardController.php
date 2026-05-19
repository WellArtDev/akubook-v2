<?php

namespace App\Http\Controllers;

use App\Models\PurchaseInvoice;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderLine;
use App\Models\PurchaseRequest;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PurchaseDashboardController extends Controller
{
    public function index(Request $request)
    {
        $validated = $request->validate([
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
        ]);

        $dateFrom = $validated['date_from'] ?? now()->startOfYear()->toDateString();
        $dateTo = $validated['date_to'] ?? now()->toDateString();

        $thisMonthPurchases = (float) PurchaseOrder::whereMonth('po_date', now()->month)
            ->whereYear('po_date', now()->year)
            ->sum('grand_total');

        $pendingPrs = PurchaseRequest::where('status', 'pending_approval')->count();
        $pendingPoApprovals = PurchaseOrder::where('status', 'pending_approval')->count();
        $overdueInvoices = PurchaseInvoice::whereIn('status', ['posted', 'partially_paid'])
            ->where('outstanding_amount', '>', 0)
            ->whereDate('due_date', '<', now()->toDateString())
            ->count();

        $purchaseTrend = collect(range(11, 0))->map(function ($monthsAgo) {
            $start = now()->subMonths($monthsAgo)->startOfMonth();
            $end = (clone $start)->endOfMonth();

            return [
                'month' => $start->format('Y-m'),
                'total' => (float) PurchaseOrder::whereBetween('po_date', [$start->toDateString(), $end->toDateString()])->sum('grand_total'),
            ];
        })->values();

        $topSuppliers = PurchaseOrder::query()
            ->join('suppliers', 'suppliers.id', '=', 'purchase_orders.supplier_id')
            ->select('suppliers.id', 'suppliers.name')
            ->selectRaw('COALESCE(sum(purchase_orders.grand_total),0) as total')
            ->whereBetween('purchase_orders.po_date', [$dateFrom, $dateTo])
            ->groupBy('suppliers.id', 'suppliers.name')
            ->orderByDesc('total')
            ->limit(10)
            ->get();

        $topProducts = PurchaseOrderLine::query()
            ->join('purchase_orders', 'purchase_orders.id', '=', 'purchase_order_lines.purchase_order_id')
            ->select('purchase_order_lines.product_code', 'purchase_order_lines.product_name')
            ->selectRaw('COALESCE(sum(purchase_order_lines.quantity),0) as total_qty')
            ->selectRaw('COALESCE(sum(purchase_order_lines.line_total),0) as total_amount')
            ->whereBetween('purchase_orders.po_date', [$dateFrom, $dateTo])
            ->groupBy('purchase_order_lines.product_code', 'purchase_order_lines.product_name')
            ->orderByDesc('total_amount')
            ->limit(10)
            ->get();

        return Inertia::render('PurchaseDashboard/Index', [
            'filters' => [
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
            ],
            'kpis' => [
                'this_month_purchases' => $thisMonthPurchases,
                'pending_prs' => $pendingPrs,
                'pending_po_approvals' => $pendingPoApprovals,
                'overdue_invoices' => $overdueInvoices,
            ],
            'charts' => [
                'purchase_trend' => $purchaseTrend,
                'top_suppliers' => $topSuppliers,
                'top_products' => $topProducts,
            ],
            'generated_at' => now()->toDateTimeString(),
        ]);
    }
}

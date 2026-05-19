<?php

namespace App\Http\Controllers;

use App\Models\DeliveryOrder;
use App\Models\GoodsReceipt;
use App\Models\PurchaseOrder;
use App\Models\SalesInvoice;
use App\Models\StockTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class OperationalReportController extends Controller
{
    public function index(Request $request)
    {
        $data = $request->validate([
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
        ]);

        $dateFrom = $data['date_from'] ?? now()->startOfMonth()->toDateString();
        $dateTo = $data['date_to'] ?? now()->toDateString();

        $sales = SalesInvoice::query()
            ->whereDate('invoice_date', '>=', $dateFrom)
            ->whereDate('invoice_date', '<=', $dateTo)
            ->selectRaw('status, COUNT(*) as count, COALESCE(SUM(grand_total), 0) as total')
            ->groupBy('status')
            ->orderBy('status')
            ->get();

        $purchaseOrders = PurchaseOrder::query()
            ->whereDate('po_date', '>=', $dateFrom)
            ->whereDate('po_date', '<=', $dateTo)
            ->selectRaw('status, COUNT(*) as count, COALESCE(SUM(grand_total), 0) as total')
            ->groupBy('status')
            ->orderBy('status')
            ->get();

        $goodsReceipts = GoodsReceipt::query()
            ->whereDate('gr_date', '>=', $dateFrom)
            ->whereDate('gr_date', '<=', $dateTo)
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->orderBy('status')
            ->get();

        $deliveryOrders = DeliveryOrder::query()
            ->whereDate('do_date', '>=', $dateFrom)
            ->whereDate('do_date', '<=', $dateTo)
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->orderBy('status')
            ->get();

        $stockMovements = StockTransaction::query()
            ->whereDate('movement_date', '>=', $dateFrom)
            ->whereDate('movement_date', '<=', $dateTo)
            ->select('movement_type')
            ->selectRaw('COUNT(*) as count')
            ->selectRaw('COALESCE(SUM(quantity_in), 0) as quantity_in')
            ->selectRaw('COALESCE(SUM(quantity_out), 0) as quantity_out')
            ->selectRaw('COALESCE(SUM(quantity_in - quantity_out), 0) as net_quantity')
            ->groupBy('movement_type')
            ->orderBy('movement_type')
            ->get();

        return Inertia::render('OperationalReports/Index', [
            'filters' => [
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
            ],
            'generated_at' => now()->toDateTimeString(),
            'summary' => [
                'sales_invoice_count' => (int) $sales->sum('count'),
                'sales_invoice_total' => (float) $sales->sum('total'),
                'purchase_order_count' => (int) $purchaseOrders->sum('count'),
                'purchase_order_total' => (float) $purchaseOrders->sum('total'),
                'goods_receipt_count' => (int) $goodsReceipts->sum('count'),
                'delivery_order_count' => (int) $deliveryOrders->sum('count'),
                'stock_movement_count' => (int) $stockMovements->sum('count'),
                'stock_net_quantity' => (float) $stockMovements->sum('net_quantity'),
            ],
            'sales' => $sales,
            'purchase_orders' => $purchaseOrders,
            'goods_receipts' => $goodsReceipts,
            'delivery_orders' => $deliveryOrders,
            'stock_movements' => $stockMovements,
        ]);
    }
}

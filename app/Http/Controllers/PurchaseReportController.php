<?php

namespace App\Http\Controllers;

use App\Models\PurchaseInvoice;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderLine;
use App\Models\SupplierPayment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class PurchaseReportController extends Controller
{
    public function index(Request $request)
    {
        $data = $request->validate([
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
        ]);

        $dateFrom = $data['date_from'] ?? now()->startOfMonth()->toDateString();
        $dateTo = $data['date_to'] ?? now()->toDateString();

        $purchaseSummary = PurchaseOrder::query()
            ->whereDate('po_date', '>=', $dateFrom)
            ->whereDate('po_date', '<=', $dateTo)
            ->selectRaw('status, COUNT(*) as count, COALESCE(SUM(grand_total), 0) as total')
            ->groupBy('status')
            ->orderBy('status')
            ->get();

        $bySupplier = PurchaseOrder::query()
            ->join('suppliers', 'suppliers.id', '=', 'purchase_orders.supplier_id')
            ->whereDate('purchase_orders.po_date', '>=', $dateFrom)
            ->whereDate('purchase_orders.po_date', '<=', $dateTo)
            ->selectRaw('suppliers.id as supplier_id, suppliers.name as supplier_name, COUNT(purchase_orders.id) as order_count, COALESCE(SUM(purchase_orders.grand_total), 0) as total')
            ->groupBy('suppliers.id', 'suppliers.name')
            ->orderByDesc('total')
            ->limit(20)
            ->get();

        $byProduct = PurchaseOrderLine::query()
            ->join('purchase_orders', 'purchase_orders.id', '=', 'purchase_order_lines.purchase_order_id')
            ->whereDate('purchase_orders.po_date', '>=', $dateFrom)
            ->whereDate('purchase_orders.po_date', '<=', $dateTo)
            ->selectRaw('purchase_order_lines.product_code, purchase_order_lines.product_name, COALESCE(SUM(purchase_order_lines.quantity), 0) as quantity, COALESCE(SUM(purchase_order_lines.line_total), 0) as total')
            ->groupBy('purchase_order_lines.product_code', 'purchase_order_lines.product_name')
            ->orderByDesc('total')
            ->limit(20)
            ->get();

        $byDepartment = PurchaseOrder::query()
            ->leftJoin('branches', 'branches.id', '=', 'purchase_orders.delivery_address_id')
            ->whereDate('purchase_orders.po_date', '>=', $dateFrom)
            ->whereDate('purchase_orders.po_date', '<=', $dateTo)
            ->selectRaw("COALESCE(branches.name, 'Unassigned') as department_name, COUNT(purchase_orders.id) as order_count, COALESCE(SUM(purchase_orders.grand_total), 0) as total")
            ->groupBy('branches.name')
            ->orderByDesc('total')
            ->get();

        $pipeline = PurchaseOrder::query()
            ->whereDate('po_date', '>=', $dateFrom)
            ->whereDate('po_date', '<=', $dateTo)
            ->selectRaw('status, COUNT(*) as count, COALESCE(SUM(grand_total), 0) as total')
            ->groupBy('status')
            ->orderBy('status')
            ->get();

        $apAging = PurchaseInvoice::query()
            ->with('supplier:id,name')
            ->where('outstanding_amount', '>', 0)
            ->whereIn('status', ['posted', 'partially_paid'])
            ->get()
            ->map(function (PurchaseInvoice $invoice) {
                $days = now()->startOfDay()->diffInDays($invoice->due_date ?? $invoice->invoice_date, false) * -1;

                return [
                    'invoice_id' => $invoice->id,
                    'invoice_number' => $invoice->invoice_number,
                    'supplier_name' => $invoice->supplier?->name,
                    'due_date' => optional($invoice->due_date)->toDateString(),
                    'outstanding_amount' => (float) $invoice->outstanding_amount,
                    'bucket' => $this->agingBucket($days),
                ];
            })
            ->groupBy('bucket')
            ->map(fn ($rows, $bucket) => [
                'bucket' => $bucket,
                'count' => $rows->count(),
                'total' => (float) $rows->sum('outstanding_amount'),
            ])
            ->values();

        $payments = SupplierPayment::query()
            ->whereDate('payment_date', '>=', $dateFrom)
            ->whereDate('payment_date', '<=', $dateTo)
            ->selectRaw('status, COUNT(*) as count, COALESCE(SUM(total_amount), 0) as total')
            ->groupBy('status')
            ->orderBy('status')
            ->get();

        return Inertia::render('PurchaseReports/Index', [
            'filters' => [
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
            ],
            'generated_at' => now()->toDateTimeString(),
            'summary' => [
                'purchase_order_count' => (int) $purchaseSummary->sum('count'),
                'purchase_order_total' => (float) $purchaseSummary->sum('total'),
                'supplier_count' => $bySupplier->count(),
                'ap_outstanding_total' => (float) $apAging->sum('total'),
                'payment_total' => (float) $payments->sum('total'),
            ],
            'purchase_summary' => $purchaseSummary,
            'by_supplier' => $bySupplier,
            'by_product' => $byProduct,
            'by_department' => $byDepartment,
            'pipeline' => $pipeline,
            'ap_aging' => $apAging,
            'payments' => $payments,
        ]);
    }

    public function export(Request $request)
    {
        $dateFrom = $request->input('date_from', now()->startOfMonth()->toDateString());
        $dateTo = $request->input('date_to', now()->toDateString());

        $rows = PurchaseOrder::with('supplier:id,name')
            ->whereDate('po_date', '>=', $dateFrom)
            ->whereDate('po_date', '<=', $dateTo)
            ->orderBy('po_date')
            ->get()
            ->map(fn (PurchaseOrder $order) => [
                'po_number' => $order->po_number,
                'po_date' => optional($order->po_date)->toDateString(),
                'supplier' => $order->supplier?->name,
                'status' => $order->status,
                'grand_total' => (float) $order->grand_total,
            ]);

        return response()->json([
            'period' => compact('dateFrom', 'dateTo'),
            'rows' => $rows,
        ]);
    }

    private function agingBucket(int $daysOverdue): string
    {
        if ($daysOverdue <= 0) {
            return 'current';
        }

        if ($daysOverdue <= 30) {
            return '1-30';
        }

        if ($daysOverdue <= 60) {
            return '31-60';
        }

        if ($daysOverdue <= 90) {
            return '61-90';
        }

        return '90+';
    }
}

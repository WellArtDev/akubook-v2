<?php

namespace App\Http\Controllers;

use App\Models\AttendanceRecord;
use App\Models\DashboardPreference;
use App\Models\PayrollRun;
use App\Models\StockTransaction;
use App\Models\Voucher;
use Illuminate\Http\Request;
use Inertia\Inertia;

class RoleDashboardController extends Controller
{
    public function index(Request $request)
    {
        $payload = $this->metricsPayload($request);

        return Inertia::render('Dashboards/RoleIndex', $payload);
    }

    public function metrics(Request $request)
    {
        return response()->json($this->metricsPayload($request));
    }

    public function preference(Request $request)
    {
        $data = $request->validate([
            'refresh_seconds' => ['required', 'integer', 'in:' . implode(',', DashboardPreference::REFRESH_INTERVALS)],
            'auto_refresh_enabled' => ['required', 'boolean'],
        ]);

        $preference = DashboardPreference::updateOrCreate(
            ['user_id' => $request->user()->id],
            $data
        );

        return response()->json([
            'refresh_seconds' => $preference->refresh_seconds,
            'auto_refresh_enabled' => $preference->auto_refresh_enabled,
        ]);
    }

    public function drilldown(Request $request, string $widget)
    {
        $data = $this->drilldownData($widget, $request);

        abort_if($data === null, 404);

        return Inertia::render('Dashboards/Drilldown', [
            'widget' => $widget,
            'title' => $data['title'],
            'columns' => $data['columns'],
            'rows' => $data['rows'],
            'summary' => $data['summary'],
            'filters' => $request->only(['date_from', 'date_to', 'search']),
        ]);
    }

    private function metricsPayload(Request $request): array
    {
        $role = $this->resolveRole((string) ($request->user()->email ?? ''));
        $preference = DashboardPreference::firstOrCreate(
            ['user_id' => $request->user()->id],
            ['refresh_seconds' => 60, 'auto_refresh_enabled' => true]
        );

        return [
            'role' => $role,
            'widgets' => $this->widgets($role),
            'generated_at' => now()->toDateTimeString(),
            'refresh_seconds' => $preference->refresh_seconds,
            'auto_refresh_enabled' => $preference->auto_refresh_enabled,
            'refresh_options' => DashboardPreference::REFRESH_INTERVALS,
        ];
    }

    private function resolveRole(string $email): string
    {
        $email = strtolower($email);

        return match (true) {
            str_contains($email, 'finance') => 'finance',
            str_contains($email, 'inventory') => 'inventory',
            str_contains($email, 'hr') => 'hr',
            str_contains($email, 'sales') => 'sales',
            default => 'general',
        };
    }

    private function widgets(string $role): array
    {
        $cashIn = (float) Voucher::where('status', 'posted')->where('voucher_type', 'receipt')->sum('amount');
        $cashOut = (float) Voucher::where('status', 'posted')->where('voucher_type', 'payment')->sum('amount');
        $stockNet = (float) StockTransaction::selectRaw('COALESCE(SUM(quantity_in - quantity_out),0) as qty')->value('qty');
        $attendancePresent = AttendanceRecord::where('status', 'present')->count();
        $payrollLast = PayrollRun::latest()->first();

        $map = [
            'finance' => [
                $this->widget('Cash In', $cashIn, 'cash-flow-reports.index', 'cash_receipts'),
                $this->widget('Cash Out', $cashOut, 'cash-flow-reports.index', 'cash_payments'),
                $this->widget('Latest Payroll Net', (float) ($payrollLast?->total_net_pay ?? 0), 'payroll-reports.index', 'latest_payroll_net'),
            ],
            'inventory' => [
                $this->widget('Stock Net Qty', $stockNet, 'stock-transactions.index', 'stock_movements'),
                $this->widget('Stock Opname', (float) \App\Models\StockOpname::count(), 'stock-opnames.index', 'stock_opnames'),
                $this->widget('Transfers', (float) \App\Models\StockTransfer::count(), 'stock-transfers.index', 'stock_transfers'),
            ],
            'hr' => [
                $this->widget('Active Employees', (float) \App\Models\Employee::where('employment_status', 'active')->count(), 'employees.index', 'active_employees'),
                $this->widget('Present Records', (float) $attendancePresent, 'attendance-reports.index', 'present_today'),
                $this->widget('Open Leaves', (float) \App\Models\LeaveRequest::where('status', 'pending')->count(), 'leave-requests.index', 'open_leaves'),
            ],
            'sales' => [
                $this->widget('Sales Invoices', (float) \App\Models\SalesInvoice::count(), 'sales-invoices.index', 'sales_invoices'),
                $this->widget('Faktur Issued', (float) \App\Models\FakturPajak::where('status', 'issued')->count(), 'faktur-pajaks.index', 'issued_faktur'),
                $this->widget('Quotations', (float) \App\Models\SalesQuotation::count(), 'sales-quotations.index', 'sales_quotations'),
            ],
            'general' => [
                $this->widget('Cash In', $cashIn, 'cash-flow-reports.index', 'cash_receipts'),
                $this->widget('Stock Net Qty', $stockNet, 'stock-transactions.index', 'stock_movements'),
                $this->widget('Active Employees', (float) \App\Models\Employee::where('employment_status', 'active')->count(), 'employees.index', 'active_employees'),
            ],
        ];

        return $map[$role] ?? $map['general'];
    }

    private function widget(string $title, float $value, string $route, string $key): array
    {
        return [
            'title' => $title,
            'value' => $value,
            'route' => $route,
            'widget_key' => $key,
            'drilldown_route' => 'role-dashboard.drilldown',
        ];
    }

    private function drilldownData(string $widget, Request $request): ?array
    {
        return match ($widget) {
            'cash_receipts' => $this->voucherRows('Cash Receipts', 'receipt', $request),
            'cash_payments' => $this->voucherRows('Cash Payments', 'payment', $request),
            'latest_payroll_net' => $this->payrollRows($request),
            'stock_movements' => $this->stockRows($request),
            'stock_opnames' => $this->stockOpnameRows($request),
            'stock_transfers' => $this->stockTransferRows($request),
            'active_employees' => $this->employeeRows($request),
            'present_today' => $this->attendanceRows($request),
            'open_leaves' => $this->leaveRows($request),
            'sales_invoices' => $this->salesInvoiceRows($request),
            'issued_faktur' => $this->fakturRows($request),
            'sales_quotations' => $this->quotationRows($request),
            default => null,
        };
    }

    private function voucherRows(string $title, string $type, Request $request): array
    {
        $rows = Voucher::with('counterpartAccount')
            ->where('status', 'posted')
            ->where('voucher_type', $type)
            ->when($request->filled('date_from'), fn ($query) => $query->whereDate('voucher_date', '>=', $request->date_from))
            ->when($request->filled('date_to'), fn ($query) => $query->whereDate('voucher_date', '<=', $request->date_to))
            ->when($request->filled('search'), fn ($query) => $query->where('voucher_number', 'like', '%' . $request->search . '%'))
            ->latest('voucher_date')
            ->limit(100)
            ->get()
            ->map(fn ($voucher) => [
                'number' => $voucher->voucher_number,
                'date' => $voucher->voucher_date?->toDateString(),
                'description' => $voucher->counterpartAccount?->name ?? '-',
                'amount' => (float) $voucher->amount,
                'status' => $voucher->status,
            ]);

        return $this->table($title, ['number', 'date', 'description', 'amount', 'status'], $rows->all(), ['count' => $rows->count(), 'total' => $rows->sum('amount')]);
    }

    private function payrollRows(Request $request): array
    {
        $run = PayrollRun::with('lines.employee')->latest()->first();
        $rows = collect($run?->lines ?? [])
            ->filter(fn ($line) => ! $request->filled('search') || str_contains(strtolower($line->employee?->full_name ?? ''), strtolower($request->search)) || str_contains(strtolower($line->employee?->employee_id ?? ''), strtolower($request->search)))
            ->map(fn ($line) => [
                'employee' => trim(($line->employee?->employee_id ?? '') . ' ' . ($line->employee?->full_name ?? '')),
                'earnings' => (float) $line->earning_total,
                'deductions' => (float) $line->deduction_total,
                'pph21' => (float) $line->pph21_amount,
                'net' => (float) ($line->net_pay_after_pph21 ?: $line->net_pay),
            ]);

        return $this->table('Latest Payroll Net', ['employee', 'earnings', 'deductions', 'pph21', 'net'], $rows->values()->all(), ['period' => $run?->period, 'count' => $rows->count(), 'total_net' => $rows->sum('net')]);
    }

    private function stockRows(Request $request): array
    {
        $rows = StockTransaction::with('item')
            ->when($request->filled('date_from'), fn ($query) => $query->whereDate('movement_date', '>=', $request->date_from))
            ->when($request->filled('date_to'), fn ($query) => $query->whereDate('movement_date', '<=', $request->date_to))
            ->when($request->filled('search'), fn ($query) => $query->whereHas('item', fn ($item) => $item->where('code', 'like', '%' . $request->search . '%')->orWhere('name', 'like', '%' . $request->search . '%')))
            ->latest('movement_date')
            ->limit(100)
            ->get()
            ->map(fn ($transaction) => [
                'date' => $transaction->movement_date?->toDateString(),
                'item' => trim(($transaction->item?->code ?? '') . ' ' . ($transaction->item?->name ?? '')),
                'type' => $transaction->movement_type,
                'in' => (float) $transaction->quantity_in,
                'out' => (float) $transaction->quantity_out,
            ]);

        return $this->table('Stock Movements', ['date', 'item', 'type', 'in', 'out'], $rows->all(), ['count' => $rows->count(), 'net_qty' => $rows->sum('in') - $rows->sum('out')]);
    }

    private function stockOpnameRows(Request $request): array
    {
        $rows = \App\Models\StockOpname::query()->latest('opname_date')->limit(100)->get()->map(fn ($opname) => ['number' => $opname->opname_number, 'date' => $opname->opname_date?->toDateString(), 'status' => $opname->status]);

        return $this->table('Stock Opnames', ['number', 'date', 'status'], $rows->all(), ['count' => $rows->count()]);
    }

    private function stockTransferRows(Request $request): array
    {
        $rows = \App\Models\StockTransfer::with(['fromBranch', 'toBranch'])->latest('transfer_date')->limit(100)->get()->map(fn ($transfer) => ['number' => $transfer->transfer_number, 'date' => $transfer->transfer_date?->toDateString(), 'from' => $transfer->fromBranch?->name, 'to' => $transfer->toBranch?->name, 'status' => $transfer->status]);

        return $this->table('Stock Transfers', ['number', 'date', 'from', 'to', 'status'], $rows->all(), ['count' => $rows->count()]);
    }

    private function employeeRows(Request $request): array
    {
        $rows = \App\Models\Employee::where('employment_status', 'active')
            ->when($request->filled('search'), fn ($query) => $query->where('employee_id', 'like', '%' . $request->search . '%')->orWhere('full_name', 'like', '%' . $request->search . '%'))
            ->orderBy('employee_id')
            ->limit(100)
            ->get()
            ->map(fn ($employee) => ['employee_id' => $employee->employee_id, 'name' => $employee->full_name, 'department' => $employee->department, 'position' => $employee->position]);

        return $this->table('Active Employees', ['employee_id', 'name', 'department', 'position'], $rows->all(), ['count' => $rows->count()]);
    }

    private function attendanceRows(Request $request): array
    {
        $rows = AttendanceRecord::with('employee')
            ->where('status', 'present')
            ->when($request->filled('date_from'), fn ($query) => $query->whereDate('attendance_date', '>=', $request->date_from))
            ->when($request->filled('date_to'), fn ($query) => $query->whereDate('attendance_date', '<=', $request->date_to))
            ->latest('attendance_date')
            ->limit(100)
            ->get()
            ->map(fn ($record) => ['date' => $record->attendance_date?->toDateString(), 'employee' => $record->employee?->full_name, 'hours' => (float) $record->work_hours, 'status' => $record->status]);

        return $this->table('Present Records', ['date', 'employee', 'hours', 'status'], $rows->all(), ['count' => $rows->count(), 'hours' => $rows->sum('hours')]);
    }

    private function leaveRows(Request $request): array
    {
        $rows = \App\Models\LeaveRequest::with('employee')->where('status', 'pending')->latest('start_date')->limit(100)->get()->map(fn ($leave) => ['employee' => $leave->employee?->full_name, 'type' => $leave->leave_type, 'start' => $leave->start_date?->toDateString(), 'end' => $leave->end_date?->toDateString(), 'days' => (float) $leave->total_days]);

        return $this->table('Open Leaves', ['employee', 'type', 'start', 'end', 'days'], $rows->all(), ['count' => $rows->count()]);
    }

    private function salesInvoiceRows(Request $request): array
    {
        $rows = \App\Models\SalesInvoice::with('customer')->latest('invoice_date')->limit(100)->get()->map(fn ($invoice) => ['number' => $invoice->invoice_number, 'date' => $invoice->invoice_date?->toDateString(), 'customer' => $invoice->customer?->name, 'total' => (float) $invoice->grand_total, 'status' => $invoice->status]);

        return $this->table('Sales Invoices', ['number', 'date', 'customer', 'total', 'status'], $rows->all(), ['count' => $rows->count(), 'total' => $rows->sum('total')]);
    }

    private function fakturRows(Request $request): array
    {
        $rows = \App\Models\FakturPajak::with('customer')->where('status', 'issued')->latest('faktur_date')->limit(100)->get()->map(fn ($faktur) => ['number' => $faktur->faktur_number, 'date' => $faktur->faktur_date?->toDateString(), 'customer' => $faktur->customer?->name, 'ppn' => (float) $faktur->ppn_amount, 'status' => $faktur->status]);

        return $this->table('Issued Faktur', ['number', 'date', 'customer', 'ppn', 'status'], $rows->all(), ['count' => $rows->count(), 'ppn' => $rows->sum('ppn')]);
    }

    private function quotationRows(Request $request): array
    {
        $rows = \App\Models\SalesQuotation::with('customer')->latest('quotation_date')->limit(100)->get()->map(fn ($quote) => ['number' => $quote->quotation_number, 'date' => $quote->quotation_date?->toDateString(), 'customer' => $quote->customer?->name, 'total' => (float) $quote->grand_total, 'status' => $quote->status]);

        return $this->table('Sales Quotations', ['number', 'date', 'customer', 'total', 'status'], $rows->all(), ['count' => $rows->count(), 'total' => $rows->sum('total')]);
    }

    private function table(string $title, array $columns, array $rows, array $summary): array
    {
        return compact('title', 'columns', 'rows', 'summary');
    }
}

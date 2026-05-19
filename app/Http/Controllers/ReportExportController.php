<?php

namespace App\Http\Controllers;

use App\Models\AttendanceRecord;
use App\Models\CustomReport;
use App\Models\EmployeeDocument;
use App\Models\LeaveRequest;
use App\Models\OvertimeRecord;
use App\Models\PayrollRun;
use App\Models\PurchaseOrder;
use App\Models\SalesInvoice;
use App\Models\StockTransaction;
use App\Models\Voucher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportExportController extends Controller
{
    public function customReport(CustomReport $customReport, Request $request)
    {
        $preview = $this->customReportPreview($customReport, $request);

        return response($this->toCsv($preview['columns'], $preview['rows']))
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename="custom-report-' . $customReport->code . '-' . now()->format('YmdHis') . '.csv"');
    }

    public function financial(Request $request)
    {
        $request->validate([
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
        ]);

        $dateFrom = $request->input('date_from', now()->startOfMonth()->toDateString());
        $dateTo = $request->input('date_to', now()->toDateString());

        $rows = DB::table('journal_entry_lines')
            ->join('journal_entries', 'journal_entries.id', '=', 'journal_entry_lines.journal_entry_id')
            ->join('accounts', 'accounts.id', '=', 'journal_entry_lines.account_id')
            ->where('journal_entries.status', 'posted')
            ->whereDate('journal_entries.journal_date', '>=', $dateFrom)
            ->whereDate('journal_entries.journal_date', '<=', $dateTo)
            ->whereNull('journal_entries.deleted_at')
            ->groupBy('accounts.code', 'accounts.name', 'accounts.type')
            ->selectRaw('accounts.code as account_code, accounts.name as account_name, accounts.type, SUM(journal_entry_lines.debit) as debit, SUM(journal_entry_lines.credit) as credit')
            ->orderBy('accounts.code')
            ->get()
            ->map(fn ($row) => [
                'account_code' => $row->account_code,
                'account_name' => $row->account_name,
                'type' => $row->type,
                'debit' => (float) $row->debit,
                'credit' => (float) $row->credit,
            ])
            ->all();

        return response($this->toCsv(['account_code', 'account_name', 'type', 'debit', 'credit'], $rows))
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename="financial-report-' . now()->format('YmdHis') . '.csv"');
    }

    public function payroll(Request $request)
    {
        $request->validate([
            'period' => ['nullable', 'regex:/^\d{4}-\d{2}$/'],
            'search' => ['nullable', 'string'],
            'status' => ['nullable', 'in:draft,calculated'],
        ]);

        $period = $request->input('period', now()->format('Y-m'));

        $run = PayrollRun::query()
            ->where('period', $period)
            ->latest('id')
            ->with(['lines.employee'])
            ->first();

        $lines = collect($run?->lines ?? [])
            ->when($request->filled('status'), fn ($collection) => $collection->where('status', $request->status))
            ->when($request->filled('search'), function ($collection) use ($request) {
                $search = strtolower($request->search);
                return $collection->filter(function ($line) use ($search) {
                    $employeeId = strtolower((string) ($line->employee->employee_id ?? ''));
                    $employeeName = strtolower((string) ($line->employee->full_name ?? ''));
                    return str_contains($employeeId, $search) || str_contains($employeeName, $search);
                });
            })
            ->map(fn ($line) => [
                'employee_id' => $line->employee->employee_id ?? '-',
                'employee_name' => $line->employee->full_name ?? '-',
                'status' => $line->status,
                'earning_total' => (float) $line->earning_total,
                'deduction_total' => (float) $line->deduction_total,
                'gross_pay' => (float) $line->gross_pay,
                'pph21_amount' => (float) $line->pph21_amount,
                'net_pay_after_pph21' => (float) $line->net_pay_after_pph21,
            ])
            ->values()
            ->all();

        return response($this->toCsv(['employee_id', 'employee_name', 'status', 'earning_total', 'deduction_total', 'gross_pay', 'pph21_amount', 'net_pay_after_pph21'], $lines))
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename="payroll-report-' . $period . '-' . now()->format('YmdHis') . '.csv"');
    }

    private function customReportPreview(CustomReport $report, Request $request): array
    {
        $sourceTables = [
            'employees' => 'employees',
            'sales_invoices' => 'sales_invoices',
            'purchase_orders' => 'purchase_orders',
            'vouchers' => 'vouchers',
            'attendance_records' => 'attendance_records',
        ];

        $searchColumns = [
            'employees' => ['employee_id', 'full_name'],
            'sales_invoices' => ['invoice_number', 'status'],
            'purchase_orders' => ['po_number', 'status'],
            'vouchers' => ['voucher_number', 'reference_number'],
            'attendance_records' => ['status'],
        ];

        $dateColumns = [
            'employees' => 'join_date',
            'sales_invoices' => 'invoice_date',
            'purchase_orders' => 'po_date',
            'vouchers' => 'voucher_date',
            'attendance_records' => 'attendance_date',
        ];

        $allowed = CustomReport::SOURCES[$report->source_key];
        $columns = array_values(array_intersect($report->selected_columns, $allowed));
        $table = $sourceTables[$report->source_key];
        $query = DB::table($table)->select($columns)->whereNull($table . '.deleted_at');

        $dateColumn = $dateColumns[$report->source_key];
        if ($request->filled('date_from')) {
            $query->whereDate($dateColumn, '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate($dateColumn, '<=', $request->date_to);
        }
        if ($request->filled('search')) {
            $search = '%' . strtolower($request->search) . '%';
            $query->where(function ($q) use ($report, $searchColumns, $search) {
                foreach ($searchColumns[$report->source_key] as $column) {
                    $q->orWhereRaw('LOWER(' . $column . ') LIKE ?', [$search]);
                }
            });
        }

        foreach (($report->default_filters ?? []) as $key => $value) {
            if ($value !== null && in_array($key, $allowed, true)) {
                $query->where($key, $value);
            }
        }

        $rows = $query->limit(1000)->get()->map(fn ($row) => (array) $row)->values()->all();

        return ['columns' => $columns, 'rows' => $rows];
    }

    private function toCsv(array $columns, array $rows): string
    {
        $stream = fopen('php://temp', 'r+');
        fputcsv($stream, $columns);
        foreach ($rows as $row) {
            $line = [];
            foreach ($columns as $column) {
                $line[] = $row[$column] ?? null;
            }
            fputcsv($stream, $line);
        }
        rewind($stream);
        $csv = stream_get_contents($stream) ?: '';
        fclose($stream);

        return $csv;
    }
}

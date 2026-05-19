<?php

namespace App\Http\Controllers;

use App\Models\PayrollBankTransfer;
use App\Models\PayrollRun;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class PayrollBankTransferController extends Controller
{
    public function index(Request $request)
    {
        $transfers = PayrollBankTransfer::with('creator')
            ->when($request->filled('period'), fn ($query) => $query->where('period', $request->period))
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = '%' . str_replace(['%', '_'], ['\\%', '\\_'], $request->search) . '%';
                $query->where('transfer_number', 'like', $search);
            })
            ->latest()
            ->paginate(50)
            ->withQueryString();

        return Inertia::render('PayrollBankTransfers/Index', [
            'transfers' => $transfers,
            'filters' => $request->only(['period', 'search']),
        ]);
    }

    public function create(Request $request)
    {
        $period = $request->input('period', now()->format('Y-m'));
        $run = PayrollRun::with('lines.employee')->where('period', $period)->latest()->first();

        return Inertia::render('PayrollBankTransfers/Create', [
            'period' => $period,
            'payrollRun' => $run,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'period' => ['required', 'regex:/^\d{4}-\d{2}$/'],
        ]);

        $run = PayrollRun::with('lines.employee')->where('period', $data['period'])->latest()->firstOrFail();

        $transfer = DB::transaction(function () use ($run, $data) {
            $transfer = PayrollBankTransfer::create([
                'transfer_number' => PayrollBankTransfer::generateNumber(),
                'period' => $data['period'],
                'status' => 'generated',
                'created_by' => Auth::id(),
                'generated_at' => now(),
                'metadata' => ['payroll_run_id' => $run->id],
            ]);

            $rows = [];
            $success = 0;
            $failed = 0;
            $total = 0;
            $lineNumber = 1;

            foreach ($run->lines as $line) {
                $employee = $line->employee;
                $amount = (float) ($line->net_pay_after_pph21 ?: $line->net_pay);
                $failure = null;

                if (! $employee?->bank_name || ! $employee?->bank_account_number || ! $employee?->bank_account_holder) {
                    $failure = 'Employee bank data incomplete';
                }

                if ($amount <= 0) {
                    $failure = 'Transfer amount must be greater than zero';
                }

                $status = $failure ? 'failed' : 'success';
                if ($status === 'success') {
                    $success++;
                    $total += $amount;
                } else {
                    $failed++;
                }

                $rows[] = [
                    'employee_code' => $employee?->employee_id,
                    'employee_name' => $employee?->full_name,
                    'bank_name' => $employee?->bank_name,
                    'bank_account_number' => $employee?->bank_account_number,
                    'bank_account_holder' => $employee?->bank_account_holder,
                    'amount' => $amount,
                    'status' => $status,
                    'failure_reason' => $failure,
                ];

                $transfer->lines()->create([
                    'employee_id' => $line->employee_id,
                    'line_number' => $lineNumber++,
                    'employee_code' => $employee?->employee_id ?? '-',
                    'employee_name' => $employee?->full_name ?? '-',
                    'bank_name' => $employee?->bank_name,
                    'bank_account_number' => $employee?->bank_account_number,
                    'bank_account_holder' => $employee?->bank_account_holder,
                    'amount' => $amount,
                    'status' => $status,
                    'failure_reason' => $failure,
                ]);
            }

            $transfer->update([
                'row_count' => count($rows),
                'success_count' => $success,
                'failed_count' => $failed,
                'total_amount' => $total,
                'csv_content' => $this->toCsv($rows),
            ]);

            return $transfer;
        });

        return redirect()->route('payroll-bank-transfers.show', $transfer)->with('success', 'Bank transfer file generated.');
    }

    public function show(PayrollBankTransfer $payrollBankTransfer)
    {
        $payrollBankTransfer->load(['lines.employee', 'creator']);

        return Inertia::render('PayrollBankTransfers/Show', [
            'transfer' => $payrollBankTransfer,
        ]);
    }

    public function download(PayrollBankTransfer $payrollBankTransfer)
    {
        return response($payrollBankTransfer->csv_content ?? '', 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $payrollBankTransfer->transfer_number . '.csv"',
        ]);
    }

    private function toCsv(array $rows): string
    {
        $handle = fopen('php://temp', 'r+');
        fputcsv($handle, ['employee_code', 'employee_name', 'bank_name', 'bank_account_number', 'bank_account_holder', 'amount', 'status', 'failure_reason']);

        foreach ($rows as $row) {
            fputcsv($handle, $row);
        }

        rewind($handle);
        return stream_get_contents($handle) ?: '';
    }
}

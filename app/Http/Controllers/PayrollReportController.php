<?php

namespace App\Http\Controllers;

use App\Models\PayrollRun;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PayrollReportController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'period' => ['nullable', 'regex:/^\d{4}-\d{2}$/'],
            'search' => ['nullable', 'string'],
            'status' => ['nullable', 'in:draft,calculated'],
        ]);

        $period = $request->input('period', now()->format('Y-m'));

        $run = PayrollRun::query()
            ->with(['lines.employee'])
            ->where('period', $period)
            ->latest('id')
            ->first();

        $rows = collect($run?->lines ?? [])
            ->filter(function ($line) use ($request) {
                if ($request->filled('status') && $line->status !== $request->status) {
                    return false;
                }

                if ($request->filled('search')) {
                    $search = mb_strtolower($request->search);
                    $employeeId = mb_strtolower($line->employee?->employee_id ?? '');
                    $fullName = mb_strtolower($line->employee?->full_name ?? '');
                    return str_contains($employeeId, $search) || str_contains($fullName, $search);
                }

                return true;
            })
            ->map(function ($line) {
                return [
                    'id' => $line->id,
                    'employee_id' => $line->employee?->employee_id,
                    'employee_name' => $line->employee?->full_name,
                    'status' => $line->status,
                    'earning_total' => (float) $line->earning_total,
                    'deduction_total' => (float) $line->deduction_total,
                    'gross_pay' => (float) $line->gross_pay,
                    'pph21_amount' => (float) ($line->pph21_amount ?? 0),
                    'net_pay_after_pph21' => (float) ($line->net_pay_after_pph21 ?? $line->net_pay),
                ];
            })
            ->values();

        $summary = [
            'employee_count' => $rows->count(),
            'total_earnings' => $rows->sum('earning_total'),
            'total_deductions' => $rows->sum('deduction_total'),
            'total_pph21' => $rows->sum('pph21_amount'),
            'total_net_pay_after_pph21' => $rows->sum('net_pay_after_pph21'),
        ];

        return Inertia::render('PayrollReports/Index', [
            'period' => $period,
            'filters' => $request->only(['period', 'search', 'status']),
            'run' => $run ? [
                'id' => $run->id,
                'run_number' => $run->run_number,
                'period' => $run->period,
                'status' => $run->status,
            ] : null,
            'rows' => $rows,
            'summary' => $summary,
        ]);
    }
}

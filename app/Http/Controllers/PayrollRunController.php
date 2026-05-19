<?php

namespace App\Http\Controllers;

use App\Models\AttendanceRecord;
use App\Models\Employee;
use App\Models\OvertimeRecord;
use App\Models\PayrollRun;
use App\Models\PayrollRunLine;
use App\Models\SalaryComponent;
use App\Services\AuditLogger;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class PayrollRunController extends Controller
{
    public function __construct(private readonly AuditLogger $auditLogger)
    {
    }

    public function index(Request $request)
    {
        $period = $request->input('period', now()->format('Y-m'));

        if ($request->boolean('run')) {
            $payrollRun = $this->runPayroll($period);

            $this->auditLogger->log(
                eventKey: 'payroll_run.executed',
                entityType: 'payroll_run',
                entityId: $payrollRun->id,
                action: 'execute',
                actorUserId: Auth::id(),
                oldValues: null,
                newValues: $payrollRun->only(['period', 'status', 'total_net_pay']),
                metadata: ['period' => $period],
                request: $request,
                isSensitive: true,
                sensitivityLevel: 'critical',
                sensitivityReason: 'payroll_execution'
            );
        }

        $runs = PayrollRun::query()
            ->with('creator')
            ->when($request->filled('period'), fn ($q) => $q->where('period', $request->period))
            ->latest('period')
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        $activeRun = PayrollRun::query()
            ->with(['lines.employee'])
            ->where('period', $period)
            ->first();

        return Inertia::render('PayrollRuns/Index', [
            'runs' => $runs,
            'activeRun' => $activeRun,
            'period' => $period,
            'summary' => [
                'employee_count' => $activeRun?->lines->count() ?? 0,
                'total_earnings' => (float) ($activeRun?->total_earnings ?? 0),
                'total_deductions' => (float) ($activeRun?->total_deductions ?? 0),
                'total_net_pay' => (float) ($activeRun?->total_net_pay ?? 0),
            ],
        ]);
    }

    private function runPayroll(string $period): PayrollRun
    {
        $employees = Employee::query()
            ->where('employment_status', 'active')
            ->orderBy('employee_id')
            ->get(['id', 'employee_id', 'full_name']);

        $components = SalaryComponent::query()
            ->where('is_active', true)
            ->orderBy('component_type')
            ->orderBy('code')
            ->get(['id', 'code', 'name', 'component_type', 'calculation_method', 'default_amount', 'default_percentage']);

        [$startDate, $endDate] = $this->periodRange($period);

        return DB::transaction(function () use ($period, $employees, $components, $startDate, $endDate) {
            $run = PayrollRun::query()->firstOrCreate(
                ['period' => $period],
                [
                    'run_number' => PayrollRun::generateNumber(),
                    'status' => 'draft',
                    'created_by' => Auth::id(),
                ]
            );

            $run->updated_by = Auth::id();
            $run->save();

            $baseEarning = (float) $components
                ->where('component_type', 'earning')
                ->where('calculation_method', 'fixed')
                ->sum(fn ($component) => (float) $component->default_amount);

            $overtimeRate = (float) optional($components->firstWhere('code', 'OVERTIME'))->default_amount;
            $absencePenalty = (float) optional($components->firstWhere('code', 'ABSENCE'))->default_amount;

            $totalEarnings = 0.0;
            $totalDeductions = 0.0;
            $totalGross = 0.0;
            $totalNet = 0.0;

            $pph21Brackets = [
                ['limit' => 5000000, 'rate' => 0.05],
                ['limit' => 10000000, 'rate' => 0.10],
                ['limit' => 20000000, 'rate' => 0.15],
                ['limit' => null, 'rate' => 0.20],
            ];

            foreach ($employees as $employee) {
                $presentDays = AttendanceRecord::query()
                    ->where('employee_id', $employee->id)
                    ->whereDate('attendance_date', '>=', $startDate)
                    ->whereDate('attendance_date', '<=', $endDate)
                    ->where('status', 'present')
                    ->count();

                $incompleteDays = AttendanceRecord::query()
                    ->where('employee_id', $employee->id)
                    ->whereDate('attendance_date', '>=', $startDate)
                    ->whereDate('attendance_date', '<=', $endDate)
                    ->where('status', 'incomplete')
                    ->count();

                $absentDays = AttendanceRecord::query()
                    ->where('employee_id', $employee->id)
                    ->whereDate('attendance_date', '>=', $startDate)
                    ->whereDate('attendance_date', '<=', $endDate)
                    ->where('status', 'absent')
                    ->count();

                $attendanceHours = (float) AttendanceRecord::query()
                    ->where('employee_id', $employee->id)
                    ->whereDate('attendance_date', '>=', $startDate)
                    ->whereDate('attendance_date', '<=', $endDate)
                    ->sum('work_hours');

                $approvedOvertimeHours = (float) OvertimeRecord::query()
                    ->where('employee_id', $employee->id)
                    ->whereBetween('overtime_date', [$startDate, $endDate])
                    ->where('status', 'approved')
                    ->sum('hours');

                $earning = 0.0;
                $deduction = 0.0;
                $snapshot = [];

                foreach ($components as $component) {
                    $amount = $component->calculation_method === 'percentage'
                        ? round($baseEarning * ((float) $component->default_percentage / 100), 2)
                        : (float) $component->default_amount;

                    if ($component->component_type === 'earning') {
                        $earning += $amount;
                    } else {
                        $deduction += $amount;
                    }

                    $snapshot[] = [
                        'code' => $component->code,
                        'name' => $component->name,
                        'type' => $component->component_type,
                        'method' => $component->calculation_method,
                        'amount' => $amount,
                    ];
                }

                $overtimeAmount = round($approvedOvertimeHours * $overtimeRate, 2);
                if ($overtimeAmount > 0) {
                    $earning += $overtimeAmount;
                    $snapshot[] = [
                        'code' => 'OVERTIME_HOURS',
                        'name' => 'Attendance Overtime Hours',
                        'type' => 'earning',
                        'method' => 'attendance',
                        'amount' => $overtimeAmount,
                    ];
                }

                $absenceAmount = round(($absentDays + $incompleteDays) * $absencePenalty, 2);
                if ($absenceAmount > 0) {
                    $deduction += $absenceAmount;
                    $snapshot[] = [
                        'code' => 'ABSENCE_DAYS',
                        'name' => 'Attendance Absence/Incomplete Days',
                        'type' => 'deduction',
                        'method' => 'attendance',
                        'amount' => $absenceAmount,
                    ];
                }

                $gross = round($earning, 2);
                $net = round($gross - $deduction, 2);
                $pph21TaxableIncome = max($net, 0);
                [$pph21Amount, $pph21Trace] = $this->calculatePph21($pph21TaxableIncome, $pph21Brackets);
                $netPayAfterPph21 = round($net - $pph21Amount, 2);

                $snapshot[] = [
                    'code' => 'PPH21',
                    'name' => 'PPh21 Tax',
                    'type' => 'tax',
                    'method' => 'progressive_monthly',
                    'amount' => $pph21Amount,
                    'taxable_income' => $pph21TaxableIncome,
                    'brackets' => $pph21Trace,
                ];

                PayrollRunLine::query()->updateOrCreate(
                    [
                        'payroll_run_id' => $run->id,
                        'employee_id' => $employee->id,
                    ],
                    [
                        'present_days' => $presentDays,
                        'incomplete_days' => $incompleteDays,
                        'absent_days' => $absentDays,
                        'attendance_work_hours' => round($attendanceHours, 2),
                        'approved_overtime_hours' => round($approvedOvertimeHours, 2),
                        'pph21_taxable_income' => round($pph21TaxableIncome, 2),
                        'pph21_amount' => $pph21Amount,
                        'earning_total' => $gross,
                        'deduction_total' => round($deduction, 2),
                        'gross_pay' => $gross,
                        'net_pay' => $net,
                        'net_pay_after_pph21' => $netPayAfterPph21,
                        'component_snapshot' => $snapshot,
                        'status' => 'calculated',
                    ]
                );

                $totalEarnings += $gross;
                $totalDeductions += $deduction;
                $totalGross += $gross;
                $totalNet += $net;
            }

            $run->update([
                'status' => 'calculated',
                'total_earnings' => round($totalEarnings, 2),
                'total_deductions' => round($totalDeductions, 2),
                'total_gross_pay' => round($totalGross, 2),
                'total_net_pay' => round($totalNet, 2),
                'updated_by' => Auth::id(),
            ]);

            return $run->fresh();
        });
    }

    private function periodRange(string $period): array
    {
        $start = Carbon::createFromFormat('Y-m', $period)->startOfMonth()->toDateString();
        $end = Carbon::createFromFormat('Y-m', $period)->endOfMonth()->toDateString();

        return [$start, $end];
    }

    private function calculatePph21(float $taxableIncome, array $brackets): array
    {
        $remaining = $taxableIncome;
        $tax = 0.0;
        $trace = [];

        foreach ($brackets as $bracket) {
            if ($remaining <= 0) {
                break;
            }

            $portion = $bracket['limit'] === null ? $remaining : min($remaining, $bracket['limit']);
            $lineTax = round($portion * $bracket['rate'], 2);

            $trace[] = [
                'portion' => round($portion, 2),
                'rate' => $bracket['rate'],
                'tax' => $lineTax,
            ];

            $tax += $lineTax;
            $remaining -= $portion;
        }

        return [round($tax, 2), $trace];
    }
}

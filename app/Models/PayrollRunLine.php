<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PayrollRunLine extends Model
{
    use HasFactory;

    protected $fillable = [
        'payroll_run_id',
        'employee_id',
        'present_days',
        'incomplete_days',
        'absent_days',
        'attendance_work_hours',
        'approved_overtime_hours',
        'pph21_taxable_income',
        'pph21_amount',
        'earning_total',
        'deduction_total',
        'gross_pay',
        'net_pay',
        'net_pay_after_pph21',
        'component_snapshot',
        'status',
    ];

    protected $casts = [
        'present_days' => 'integer',
        'incomplete_days' => 'integer',
        'absent_days' => 'integer',
        'attendance_work_hours' => 'decimal:2',
        'approved_overtime_hours' => 'decimal:2',
        'pph21_taxable_income' => 'decimal:2',
        'pph21_amount' => 'decimal:2',
        'earning_total' => 'decimal:2',
        'deduction_total' => 'decimal:2',
        'gross_pay' => 'decimal:2',
        'net_pay' => 'decimal:2',
        'net_pay_after_pph21' => 'decimal:2',
        'component_snapshot' => 'array',
    ];

    public function payrollRun(): BelongsTo
    {
        return $this->belongsTo(PayrollRun::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class CustomReport extends Model
{
    use HasFactory, SoftDeletes;

    public const SOURCES = [
        'employees' => ['employee_id', 'full_name', 'employment_status', 'department', 'position', 'join_date'],
        'sales_invoices' => ['invoice_number', 'invoice_date', 'status', 'grand_total'],
        'purchase_orders' => ['po_number', 'po_date', 'status', 'grand_total'],
        'vouchers' => ['voucher_number', 'voucher_date', 'voucher_type', 'status', 'amount'],
        'attendance_records' => ['attendance_date', 'status', 'work_hours'],
    ];

    protected $fillable = [
        'code',
        'name',
        'source_key',
        'selected_columns',
        'default_filters',
        'is_active',
        'description',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'selected_columns' => 'array',
        'default_filters' => 'array',
        'is_active' => 'boolean',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}

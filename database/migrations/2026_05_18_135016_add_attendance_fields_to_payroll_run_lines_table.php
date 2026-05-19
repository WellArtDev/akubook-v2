<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payroll_run_lines', function (Blueprint $table) {
            $table->unsignedInteger('present_days')->default(0)->after('employee_id');
            $table->unsignedInteger('incomplete_days')->default(0)->after('present_days');
            $table->unsignedInteger('absent_days')->default(0)->after('incomplete_days');
            $table->decimal('attendance_work_hours', 8, 2)->default(0)->after('absent_days');
            $table->decimal('approved_overtime_hours', 8, 2)->default(0)->after('attendance_work_hours');
        });
    }

    public function down(): void
    {
        Schema::table('payroll_run_lines', function (Blueprint $table) {
            $table->dropColumn([
                'present_days',
                'incomplete_days',
                'absent_days',
                'attendance_work_hours',
                'approved_overtime_hours',
            ]);
        });
    }
};

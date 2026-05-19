<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('payroll_run_lines', function (Blueprint $table) {
            $table->decimal('pph21_taxable_income', 15, 2)->default(0)->after('approved_overtime_hours');
            $table->decimal('pph21_amount', 15, 2)->default(0)->after('pph21_taxable_income');
            $table->decimal('net_pay_after_pph21', 15, 2)->default(0)->after('net_pay');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payroll_run_lines', function (Blueprint $table) {
            $table->dropColumn(['pph21_taxable_income', 'pph21_amount', 'net_pay_after_pph21']);
        });
    }
};

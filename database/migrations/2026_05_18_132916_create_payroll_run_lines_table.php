<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payroll_run_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payroll_run_id')->constrained('payroll_runs')->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained('employees')->restrictOnDelete();
            $table->decimal('earning_total', 15, 2)->default(0);
            $table->decimal('deduction_total', 15, 2)->default(0);
            $table->decimal('gross_pay', 15, 2)->default(0);
            $table->decimal('net_pay', 15, 2)->default(0);
            $table->json('component_snapshot')->nullable();
            $table->enum('status', ['draft', 'calculated'])->default('calculated');
            $table->timestamps();

            $table->unique(['payroll_run_id', 'employee_id']);
            $table->index(['employee_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payroll_run_lines');
    }
};

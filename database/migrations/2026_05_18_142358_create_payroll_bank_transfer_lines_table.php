<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payroll_bank_transfer_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payroll_bank_transfer_id')->constrained('payroll_bank_transfers')->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained('employees')->restrictOnDelete();
            $table->unsignedInteger('line_number');
            $table->string('employee_code');
            $table->string('employee_name');
            $table->string('bank_name')->nullable();
            $table->string('bank_account_number')->nullable();
            $table->string('bank_account_holder')->nullable();
            $table->decimal('amount', 15, 2)->default(0);
            $table->enum('status', ['success', 'failed'])->default('failed');
            $table->string('failure_reason')->nullable();
            $table->timestamps();

            $table->index(['payroll_bank_transfer_id', 'line_number']);
            $table->index(['employee_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payroll_bank_transfer_lines');
    }
};

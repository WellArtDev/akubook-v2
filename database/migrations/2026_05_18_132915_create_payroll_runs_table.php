<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payroll_runs', function (Blueprint $table) {
            $table->id();
            $table->string('run_number', 50)->unique();
            $table->string('period', 7);
            $table->enum('status', ['draft', 'calculated'])->default('draft');
            $table->decimal('total_earnings', 15, 2)->default(0);
            $table->decimal('total_deductions', 15, 2)->default(0);
            $table->decimal('total_gross_pay', 15, 2)->default(0);
            $table->decimal('total_net_pay', 15, 2)->default(0);
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->unique('period');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payroll_runs');
    }
};

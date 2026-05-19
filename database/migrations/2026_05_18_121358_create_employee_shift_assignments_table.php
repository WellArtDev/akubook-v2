<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employee_shift_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->restrictOnDelete();
            $table->foreignId('work_shift_id')->constrained('work_shifts')->restrictOnDelete();
            $table->date('effective_date');
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['employee_id', 'status']);
            $table->index(['work_shift_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_shift_assignments');
    }
};

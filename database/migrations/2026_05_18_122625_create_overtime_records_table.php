<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('overtime_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->restrictOnDelete();
            $table->foreignId('attendance_record_id')->nullable()->constrained('attendance_records')->nullOnDelete();
            $table->date('overtime_date');
            $table->dateTime('start_at');
            $table->dateTime('end_at');
            $table->decimal('hours', 8, 2)->default(0);
            $table->enum('status', ['pending', 'approved', 'rejected', 'cancelled'])->default('pending');
            $table->text('reason')->nullable();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('rejected_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('rejected_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->foreignId('cancelled_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('cancelled_at')->nullable();
            $table->text('cancellation_reason')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['employee_id', 'overtime_date']);
            $table->index(['status', 'overtime_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('overtime_records');
    }
};

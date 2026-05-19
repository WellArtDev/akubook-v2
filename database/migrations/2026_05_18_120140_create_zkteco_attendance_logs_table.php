<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('zkteco_attendance_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('zkteco_device_id')->constrained('zkteco_devices')->restrictOnDelete();
            $table->string('employee_identifier', 50);
            $table->timestamp('punch_at');
            $table->enum('punch_type', ['check_in', 'check_out']);
            $table->foreignId('employee_id')->nullable()->constrained('employees')->nullOnDelete();
            $table->foreignId('attendance_record_id')->nullable()->constrained('attendance_records')->nullOnDelete();
            $table->boolean('is_mapped')->default(false);
            $table->string('source_key')->unique();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->timestamps();
            $table->index(['employee_identifier', 'punch_at']);
            $table->index(['employee_id', 'is_mapped']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('zkteco_attendance_logs');
    }
};

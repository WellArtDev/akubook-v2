<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('offline_attendance_syncs', function (Blueprint $table) {
            $table->id();
            $table->string('sync_key')->unique();
            $table->foreignId('employee_id')->nullable()->constrained('employees')->nullOnDelete();
            $table->string('employee_identifier', 50);
            $table->enum('clock_type', ['check_in', 'check_out']);
            $table->timestamp('clock_at');
            $table->enum('status', ['pending', 'synced', 'failed'])->default('synced');
            $table->nullableMorphs('source');
            $table->text('failure_reason')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['employee_identifier', 'clock_at']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('offline_attendance_syncs');
    }
};

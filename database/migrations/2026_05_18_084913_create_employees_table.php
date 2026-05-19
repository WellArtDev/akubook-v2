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
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->string('employee_id', 50)->unique();
            $table->string('full_name');
            $table->string('email')->unique();
            $table->string('phone', 30)->nullable();
            $table->date('join_date');
            $table->enum('employment_status', ['active', 'inactive', 'resigned'])->default('active');
            $table->string('department', 100)->nullable();
            $table->string('position', 100)->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['employment_status', 'join_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payroll_bank_transfers', function (Blueprint $table) {
            $table->id();
            $table->string('transfer_number')->unique();
            $table->string('period', 7);
            $table->enum('status', ['draft', 'generated'])->default('draft');
            $table->unsignedInteger('row_count')->default(0);
            $table->unsignedInteger('success_count')->default(0);
            $table->unsignedInteger('failed_count')->default(0);
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->longText('csv_content')->nullable();
            $table->json('metadata')->nullable();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->timestamp('generated_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['period', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payroll_bank_transfers');
    }
};

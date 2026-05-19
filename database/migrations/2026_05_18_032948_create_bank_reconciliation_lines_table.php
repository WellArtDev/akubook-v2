<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bank_reconciliation_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bank_reconciliation_id')->constrained('bank_reconciliations')->cascadeOnDelete();
            $table->unsignedInteger('line_number');
            $table->date('transaction_date');
            $table->string('description');
            $table->decimal('debit', 15, 2)->default(0);
            $table->decimal('credit', 15, 2)->default(0);
            $table->string('reference_number', 100)->nullable();
            $table->boolean('is_matched')->default(false);
            $table->string('matched_reference_type', 100)->nullable();
            $table->unsignedBigInteger('matched_reference_id')->nullable();
            $table->timestamp('matched_at')->nullable();
            $table->foreignId('matched_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->index(['bank_reconciliation_id', 'line_number']);
            $table->index('is_matched');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bank_reconciliation_lines');
    }
};

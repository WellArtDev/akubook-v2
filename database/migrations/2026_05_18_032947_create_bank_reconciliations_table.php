<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bank_reconciliations', function (Blueprint $table) {
            $table->id();
            $table->string('reconciliation_number', 50)->unique();
            $table->foreignId('bank_account_id')->constrained('bank_accounts')->restrictOnDelete();
            $table->date('statement_start_date');
            $table->date('statement_end_date');
            $table->date('reconciliation_date');
            $table->decimal('statement_opening_balance', 15, 2)->default(0);
            $table->decimal('statement_closing_balance', 15, 2)->default(0);
            $table->decimal('matched_debit_total', 15, 2)->default(0);
            $table->decimal('matched_credit_total', 15, 2)->default(0);
            $table->decimal('system_balance', 15, 2)->default(0);
            $table->decimal('difference', 15, 2)->default(0);
            $table->enum('status', ['draft', 'reconciled'])->default('draft');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->foreignId('reconciled_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reconciled_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['bank_account_id', 'statement_start_date', 'statement_end_date']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bank_reconciliations');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vouchers', function (Blueprint $table) {
            $table->id();
            $table->string('voucher_number', 50)->unique();
            $table->enum('voucher_type', ['payment', 'receipt']);
            $table->date('voucher_date');
            $table->enum('cash_bank_type', ['cash', 'bank']);
            $table->unsignedBigInteger('cash_bank_account_id');
            $table->foreignId('counterpart_account_id')->constrained('accounts')->restrictOnDelete();
            $table->decimal('amount', 20, 2);
            $table->string('reference_number', 100)->nullable();
            $table->text('notes')->nullable();
            $table->enum('status', ['draft', 'posted', 'cancelled'])->default('draft');
            $table->foreignId('journal_entry_id')->nullable()->constrained('journal_entries')->nullOnDelete();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('posted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('posted_at')->nullable();
            $table->foreignId('cancelled_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['voucher_type', 'status']);
            $table->index(['voucher_date']);
            $table->index(['cash_bank_type', 'cash_bank_account_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vouchers');
    }
};

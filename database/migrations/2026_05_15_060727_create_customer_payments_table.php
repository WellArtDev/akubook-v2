<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customer_payments', function (Blueprint $table) {
            $table->id();
            $table->string('payment_number', 50)->unique();
            $table->date('payment_date');
            $table->foreignId('customer_id')->constrained('customers');
            $table->enum('payment_method', ['cash', 'bank_transfer', 'check', 'credit_card', 'giro']);
            $table->unsignedBigInteger('bank_account_id')->nullable();
            $table->string('reference_number', 100)->nullable();
            $table->decimal('total_amount', 15, 2);
            $table->decimal('allocated_amount', 15, 2)->default(0);
            $table->decimal('unapplied_amount', 15, 2)->default(0);
            $table->enum('status', ['draft', 'posted', 'reconciled', 'voided'])->default('draft');
            $table->foreignId('journal_entry_id')->nullable()->constrained('journal_entries');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->foreignId('updated_by')->nullable()->constrained('users');
            $table->timestamps();
            $table->softDeletes();

            $table->index('payment_number');
            $table->index('customer_id');
            $table->index('payment_date');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_payments');
    }
};

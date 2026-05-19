<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('supplier_payments', function (Blueprint $table) {
            $table->id();
            $table->string('payment_number', 50)->unique();
            $table->date('payment_date');
            $table->foreignId('supplier_id')->constrained('suppliers');
            $table->enum('payment_method', ['cash', 'bank_transfer', 'check', 'giro']);
            $table->unsignedBigInteger('bank_account_id')->nullable();
            $table->string('reference_number', 100)->nullable();
            $table->decimal('total_amount', 15, 2);
            $table->decimal('allocated_amount', 15, 2)->default(0);
            $table->decimal('unapplied_amount', 15, 2)->default(0);
            $table->enum('status', ['draft', 'posted', 'voided'])->default('draft');
            $table->foreignId('journal_entry_id')->nullable()->constrained('journal_entries');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->foreignId('updated_by')->nullable()->constrained('users');
            $table->timestamps();
            $table->softDeletes();

            $table->index('payment_number');
            $table->index('supplier_id');
            $table->index('payment_date');
            $table->index('status');
        });

        Schema::create('supplier_payment_allocations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supplier_payment_id')->constrained('supplier_payments')->cascadeOnDelete();
            $table->foreignId('purchase_invoice_id')->constrained('purchase_invoices');
            $table->decimal('allocated_amount', 15, 2);
            $table->timestamps();

            $table->unique(['supplier_payment_id', 'purchase_invoice_id'], 'supplier_payment_invoice_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('supplier_payment_allocations');
        Schema::dropIfExists('supplier_payments');
    }
};

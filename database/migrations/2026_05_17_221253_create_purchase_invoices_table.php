<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchase_invoices', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number', 50)->unique();
            $table->date('invoice_date');
            $table->date('due_date');
            $table->foreignId('goods_receipt_id')->constrained('goods_receipts')->restrictOnDelete();
            $table->foreignId('purchase_order_id')->constrained('purchase_orders')->restrictOnDelete();
            $table->foreignId('supplier_id')->constrained('suppliers')->restrictOnDelete();
            $table->string('supplier_invoice_number', 100)->nullable();
            $table->string('tax_invoice_number', 100)->nullable();
            $table->boolean('generate_tax_invoice')->default(false);
            $table->text('notes')->nullable();
            $table->enum('status', ['draft', 'posted', 'partially_paid', 'paid', 'cancelled'])->default('draft');
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->decimal('tax_amount', 15, 2)->default(0);
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->decimal('paid_amount', 15, 2)->default(0);
            $table->decimal('outstanding_amount', 15, 2)->default(0);
            $table->foreignId('journal_entry_id')->nullable()->constrained('journal_entries')->nullOnDelete();
            $table->timestamp('posted_at')->nullable();
            $table->foreignId('posted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['invoice_date', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_invoices');
    }
};

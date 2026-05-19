<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchase_invoice_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_invoice_id')->constrained('purchase_invoices')->cascadeOnDelete();
            $table->foreignId('goods_receipt_line_id')->constrained('goods_receipt_lines')->restrictOnDelete();
            $table->foreignId('purchase_order_line_id')->constrained('purchase_order_lines')->restrictOnDelete();
            $table->unsignedInteger('line_number');
            $table->string('product_code', 50)->nullable();
            $table->string('product_name');
            $table->text('description')->nullable();
            $table->decimal('ordered_quantity', 15, 3);
            $table->decimal('received_quantity', 15, 3);
            $table->decimal('previously_invoiced_quantity', 15, 3)->default(0);
            $table->decimal('remaining_to_invoice_quantity', 15, 3)->default(0);
            $table->decimal('invoice_quantity', 15, 3);
            $table->string('unit', 20);
            $table->decimal('unit_price', 15, 2);
            $table->decimal('tax_percentage', 5, 2)->default(11);
            $table->decimal('tax_amount', 15, 2)->default(0);
            $table->decimal('line_total', 15, 2)->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['purchase_invoice_id', 'line_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_invoice_lines');
    }
};

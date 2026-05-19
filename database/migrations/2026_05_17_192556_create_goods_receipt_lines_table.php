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
        Schema::create('goods_receipt_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('goods_receipt_id')->constrained('goods_receipts')->cascadeOnDelete();
            $table->foreignId('purchase_order_line_id')->constrained('purchase_order_lines')->restrictOnDelete();
            $table->unsignedInteger('line_number');
            $table->string('product_code', 50)->nullable();
            $table->string('product_name');
            $table->text('description')->nullable();
            $table->decimal('po_quantity', 15, 3);
            $table->decimal('previously_received_quantity', 15, 3)->default(0);
            $table->decimal('remaining_quantity', 15, 3)->default(0);
            $table->decimal('receipt_quantity', 15, 3);
            $table->decimal('accepted_quantity', 15, 3)->default(0);
            $table->decimal('rejected_quantity', 15, 3)->default(0);
            $table->string('unit', 20);
            $table->text('inspection_notes')->nullable();
            $table->timestamps();

            $table->index(['goods_receipt_id', 'line_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('goods_receipt_lines');
    }
};

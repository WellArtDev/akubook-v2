<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchase_return_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_return_id')->constrained('purchase_returns')->cascadeOnDelete();
            $table->foreignId('purchase_invoice_line_id')->constrained('purchase_invoice_lines')->restrictOnDelete();
            $table->unsignedInteger('line_number');
            $table->unsignedBigInteger('product_id')->nullable();
            $table->string('product_name');
            $table->decimal('return_quantity', 15, 3);
            $table->decimal('accepted_quantity', 15, 3)->default(0);
            $table->decimal('rejected_quantity', 15, 3)->default(0);
            $table->string('unit', 20);
            $table->decimal('unit_price', 20, 2);
            $table->decimal('tax_percentage', 8, 2)->default(11);
            $table->decimal('tax_amount', 20, 2)->default(0);
            $table->decimal('line_total', 20, 2)->default(0);
            $table->text('inspection_notes')->nullable();
            $table->timestamps();

            $table->index(['purchase_return_id', 'line_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_return_lines');
    }
};

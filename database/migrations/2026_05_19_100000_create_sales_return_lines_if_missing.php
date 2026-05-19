<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('sales_return_lines')) {
            return;
        }

        Schema::create('sales_return_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sales_return_id')->constrained('sales_returns')->cascadeOnDelete();
            $table->foreignId('sales_invoice_line_id')->constrained('sales_invoice_lines')->restrictOnDelete();
            $table->unsignedBigInteger('product_id')->nullable();
            $table->string('product_name');
            $table->decimal('return_quantity', 15, 3);
            $table->decimal('accepted_quantity', 15, 3)->default(0);
            $table->decimal('rejected_quantity', 15, 3)->default(0);
            $table->decimal('unit_price', 15, 2);
            $table->decimal('tax_amount', 15, 2)->default(0);
            $table->decimal('line_total', 15, 2);
            $table->text('inspection_notes')->nullable();
            $table->timestamps();

            $table->index('sales_return_id');
            $table->index('sales_invoice_line_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales_return_lines');
    }
};

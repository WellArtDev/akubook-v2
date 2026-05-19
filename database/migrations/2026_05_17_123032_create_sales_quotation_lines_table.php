<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sales_quotation_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sales_quotation_id')->constrained('sales_quotations')->onDelete('cascade');
            $table->integer('line_number');
            $table->foreignId('item_id')->constrained()->onDelete('restrict');
            $table->text('description')->nullable();
            $table->decimal('quantity', 15, 3);
            $table->string('unit', 20);
            $table->decimal('unit_price', 15, 2);
            $table->decimal('discount_percentage', 5, 2)->default(0);
            $table->decimal('discount_amount', 15, 2)->default(0);
            $table->decimal('tax_percentage', 5, 2)->default(11);
            $table->decimal('tax_amount', 15, 2)->default(0);
            $table->decimal('line_total', 15, 2)->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales_quotation_lines');
    }
};

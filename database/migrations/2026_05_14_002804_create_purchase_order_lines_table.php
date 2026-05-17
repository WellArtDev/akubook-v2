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
        Schema::create('purchase_order_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_order_id')->constrained()->onDelete('cascade');
            $table->integer('line_number');
            $table->foreignId('item_id')->constrained()->onDelete('restrict');
            $table->text('description')->nullable();
            $table->decimal('quantity', 15, 3);
            $table->string('unit', 20);
            $table->decimal('unit_price', 15, 2);
            $table->decimal('tax_amount', 15, 2)->default(0);
            $table->decimal('line_total', 15, 2);
            $table->decimal('received_quantity', 15, 3)->default(0);
            $table->decimal('invoiced_quantity', 15, 3)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_order_lines');
    }
};

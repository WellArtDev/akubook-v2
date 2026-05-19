<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('delivery_order_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('delivery_order_id')->constrained('delivery_orders')->cascadeOnDelete();
            $table->foreignId('sales_order_line_id')->constrained('sales_order_lines')->restrictOnDelete();
            $table->unsignedInteger('line_number');
            $table->foreignId('item_id')->constrained('items')->restrictOnDelete();
            $table->text('description')->nullable();
            $table->decimal('so_quantity', 15, 3);
            $table->decimal('previously_delivered_quantity', 15, 3)->default(0);
            $table->decimal('remaining_quantity', 15, 3);
            $table->decimal('delivery_quantity', 15, 3);
            $table->string('unit', 20);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('sales_order_line_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('delivery_order_lines');
    }
};

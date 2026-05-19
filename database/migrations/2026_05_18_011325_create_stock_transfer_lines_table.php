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
        Schema::create('stock_transfer_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stock_transfer_id')->constrained('stock_transfers')->cascadeOnDelete();
            $table->foreignId('item_id')->constrained('items')->restrictOnDelete();
            $table->unsignedInteger('line_number');
            $table->decimal('quantity', 15, 3);
            $table->string('unit', 30)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['stock_transfer_id', 'item_id']);
            $table->index(['stock_transfer_id', 'line_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_transfer_lines');
    }
};

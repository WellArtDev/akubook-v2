<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_opname_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stock_opname_id')->constrained('stock_opnames')->cascadeOnDelete();
            $table->foreignId('item_id')->constrained('items')->restrictOnDelete();
            $table->decimal('system_quantity', 15, 3)->default(0);
            $table->decimal('physical_quantity', 15, 3)->default(0);
            $table->decimal('variance_quantity', 15, 3)->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['stock_opname_id', 'item_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_opname_lines');
    }
};

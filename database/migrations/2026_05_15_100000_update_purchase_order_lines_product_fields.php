<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchase_order_lines', function (Blueprint $table) {
            // Drop item_id FK
            $table->dropForeign(['item_id']);
            $table->dropColumn('item_id');
            
            // Add product fields
            $table->string('product_code', 50)->nullable()->after('line_number');
            $table->string('product_name', 255)->after('product_code');
        });
    }

    public function down(): void
    {
        Schema::table('purchase_order_lines', function (Blueprint $table) {
            $table->dropColumn(['product_code', 'product_name']);
            $table->foreignId('item_id')->after('line_number')->constrained()->onDelete('restrict');
        });
    }
};

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
        Schema::table('items', function (Blueprint $table) {
            $table->string('category', 100)->nullable()->after('name');
            $table->enum('inventory_type', ['stock', 'non_stock'])->default('stock')->after('item_type');
            $table->string('valuation_method', 30)->default('moving_average')->after('inventory_type');
            $table->decimal('minimum_stock', 15, 3)->default(0)->after('selling_price');
            $table->decimal('reorder_point', 15, 3)->default(0)->after('minimum_stock');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('items', function (Blueprint $table) {
            $table->dropColumn([
                'category',
                'inventory_type',
                'valuation_method',
                'minimum_stock',
                'reorder_point',
            ]);
        });
    }
};

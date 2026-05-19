<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales_invoice_lines', function (Blueprint $table) {
            $table->foreignId('delivery_order_line_id')
                ->nullable()
                ->after('sales_order_line_id')
                ->constrained('delivery_order_lines')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('sales_invoice_lines', function (Blueprint $table) {
            $table->dropConstrainedForeignId('delivery_order_line_id');
        });
    }
};

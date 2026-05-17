<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customer_payment_allocations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_payment_id')->constrained('customer_payments')->onDelete('cascade');
            $table->foreignId('sales_invoice_id')->constrained('sales_invoices');
            $table->decimal('allocated_amount', 15, 2);
            $table->timestamps();

            $table->index(['customer_payment_id', 'sales_invoice_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_payment_allocations');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('faktur_pajaks', function (Blueprint $table) {
            $table->id();
            $table->string('faktur_number', 50)->unique();
            $table->date('faktur_date');
            $table->foreignId('sales_invoice_id')->constrained('sales_invoices')->restrictOnDelete();
            $table->foreignId('customer_id')->constrained('customers')->restrictOnDelete();
            $table->decimal('dpp', 15, 2)->default(0);
            $table->decimal('ppn_amount', 15, 2)->default(0);
            $table->decimal('grand_total', 15, 2)->default(0);
            $table->enum('status', ['draft', 'issued', 'cancelled'])->default('draft');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->foreignId('issued_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('issued_at')->nullable();
            $table->foreignId('cancelled_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['faktur_date', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('faktur_pajaks');
    }
};

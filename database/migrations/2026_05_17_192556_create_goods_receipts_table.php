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
        Schema::create('goods_receipts', function (Blueprint $table) {
            $table->id();
            $table->string('gr_number', 50)->unique();
            $table->date('gr_date');
            $table->foreignId('purchase_order_id')->constrained('purchase_orders')->restrictOnDelete();
            $table->foreignId('supplier_id')->constrained('suppliers')->restrictOnDelete();
            $table->foreignId('receive_location_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->enum('status', ['draft', 'received', 'cancelled'])->default('draft');
            $table->string('reference_number', 100)->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('received_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('received_at')->nullable();
            $table->foreignId('cancelled_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('cancelled_at')->nullable();
            $table->text('cancellation_reason')->nullable();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['gr_date', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('goods_receipts');
    }
};

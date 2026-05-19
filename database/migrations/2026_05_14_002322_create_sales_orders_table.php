<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sales_orders', function (Blueprint $table) {
            $table->id();
            $table->string('so_number', 50)->unique();
            $table->date('so_date');
            $table->foreignId('customer_id')->constrained()->onDelete('restrict');
            $table->foreignId('branch_id')->nullable()->constrained('branches')->onDelete('restrict');
            $table->string('customer_po_number', 100)->nullable();
            $table->foreignId('sales_person_id')->constrained('users')->onDelete('restrict');
            $table->string('payment_terms', 50)->nullable();
            $table->string('delivery_terms', 100)->nullable();
            $table->foreignId('delivery_address_id')->nullable()->constrained('branches')->onDelete('restrict');
            $table->date('requested_delivery_date')->nullable();
            $table->text('notes')->nullable();
            $table->enum('status', ['draft', 'pending_approval', 'approved', 'in_progress', 'completed', 'cancelled'])->default('draft');
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->decimal('discount_amount', 15, 2)->default(0);
            $table->decimal('tax_amount', 15, 2)->default(0);
            $table->decimal('grand_total', 15, 2)->default(0);
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->boolean('approval_required')->default(false);
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('restrict');
            $table->timestamp('approved_at')->nullable();
            $table->boolean('credit_check_passed')->default(true);
            $table->text('credit_check_notes')->nullable();
            $table->foreignId('created_by')->constrained('users')->onDelete('restrict');
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['so_date', 'status']);
            $table->index('customer_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales_orders');
    }
};

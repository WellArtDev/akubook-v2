<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sales_quotations', function (Blueprint $table) {
            $table->id();
            $table->string('quotation_number', 50)->unique();
            $table->date('quotation_date');
            $table->date('valid_until');
            $table->foreignId('customer_id')->constrained()->onDelete('restrict');
            $table->foreignId('customer_contact_id')->nullable()->constrained('customer_contacts')->onDelete('set null');
            $table->string('reference', 100)->nullable();
            $table->foreignId('sales_person_id')->constrained('users')->onDelete('restrict');
            $table->string('payment_terms', 50)->nullable();
            $table->string('delivery_terms', 100)->nullable();
            $table->text('notes')->nullable();
            $table->enum('status', ['draft', 'sent', 'approved', 'rejected', 'expired', 'converted', 'revised'])->default('draft');
            $table->enum('discount_type', ['percentage', 'amount'])->default('percentage');
            $table->decimal('discount_value', 15, 2)->default(0);
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->decimal('discount_amount', 15, 2)->default(0);
            $table->decimal('subtotal_after_discount', 15, 2)->default(0);
            $table->decimal('tax_amount', 15, 2)->default(0);
            $table->decimal('grand_total', 15, 2)->default(0);
            $table->foreignId('original_quotation_id')->nullable()->constrained('sales_quotations')->onDelete('set null');
            $table->unsignedInteger('revision_number')->default(0);
            $table->foreignId('converted_to_sales_order_id')->nullable()->constrained('sales_orders')->onDelete('set null');
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->timestamp('expired_at')->nullable();
            $table->timestamp('converted_at')->nullable();
            $table->foreignId('created_by')->constrained('users')->onDelete('restrict');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('restrict');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['quotation_date', 'status']);
            $table->index('valid_until');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales_quotations');
    }
};

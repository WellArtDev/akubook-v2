<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('delivery_orders', function (Blueprint $table) {
            $table->id();
            $table->string('do_number', 50)->unique();
            $table->date('do_date');
            $table->foreignId('sales_order_id')->constrained('sales_orders')->restrictOnDelete();
            $table->foreignId('customer_id')->constrained('customers')->restrictOnDelete();
            $table->foreignId('delivery_address_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->date('delivery_date')->nullable();
            $table->string('driver_name', 100)->nullable();
            $table->string('vehicle_number', 50)->nullable();
            $table->text('notes')->nullable();
            $table->enum('status', ['draft', 'ready_to_ship', 'in_transit', 'delivered', 'cancelled'])->default('draft');
            $table->string('received_by', 100)->nullable();
            $table->timestamp('received_at')->nullable();
            $table->string('signature_path')->nullable();
            $table->text('pod_notes')->nullable();
            $table->foreignId('cancelled_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('cancelled_at')->nullable();
            $table->text('cancellation_reason')->nullable();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'do_date']);
            $table->index('delivery_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('delivery_orders');
    }
};

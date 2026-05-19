<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchase_order_approvals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_order_id')->constrained('purchase_orders')->cascadeOnDelete();
            $table->foreignId('submitted_by')->constrained('users')->restrictOnDelete();
            $table->timestamp('submitted_at');
            $table->json('approval_reasons')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('comments')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->timestamps();

            $table->index('status');
            $table->index('submitted_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_order_approvals');
    }
};

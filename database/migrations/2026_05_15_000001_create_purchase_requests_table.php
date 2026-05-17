<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchase_requests', function (Blueprint $table) {
            $table->id();
            $table->string('pr_number', 50)->unique();
            $table->date('pr_date');
            $table->foreignId('department_id')->constrained('departments');
            $table->date('required_date');
            $table->text('justification')->nullable();
            $table->enum('status', [
                'draft',
                'pending_approval',
                'approved',
                'rejected',
                'converted',
                'cancelled'
            ])->default('draft');
            $table->decimal('total_estimated_amount', 15, 2)->default(0);
            $table->foreignId('approved_by')->nullable()->constrained('users');
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->foreignId('updated_by')->nullable()->constrained('users');
            $table->timestamps();
            $table->softDeletes();

            $table->index('pr_number');
            $table->index('pr_date');
            $table->index('status');
            $table->index('department_id');
        });

        Schema::create('purchase_request_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_request_id')->constrained('purchase_requests')->onDelete('cascade');
            $table->integer('line_number');
            $table->string('product_code', 50)->nullable(); // Temporary: will be FK to products later
            $table->string('product_name', 255);
            $table->text('description')->nullable();
            $table->decimal('quantity', 15, 3);
            $table->string('unit', 20);
            $table->decimal('estimated_price', 15, 2);
            $table->decimal('line_total', 15, 2);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['purchase_request_id', 'line_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_request_lines');
        Schema::dropIfExists('purchase_requests');
    }
};

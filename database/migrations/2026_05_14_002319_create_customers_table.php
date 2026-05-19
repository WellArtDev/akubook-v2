<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();
            $table->string('name');
            $table->string('category', 50)->default('retail');
            $table->string('tax_id')->nullable();
            $table->string('tax_type', 20)->default('non_pkp');
            $table->string('phone', 50)->nullable();
            $table->string('email')->nullable();
            $table->string('website')->nullable();
            $table->decimal('credit_limit', 20, 2)->default(0);
            $table->integer('payment_terms')->default(0);
            $table->decimal('outstanding_balance', 20, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->foreignId('updated_by')->nullable()->constrained('users');
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['category', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('salary_components', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();
            $table->string('name');
            $table->enum('component_type', ['earning', 'deduction']);
            $table->enum('calculation_method', ['fixed', 'percentage'])->default('fixed');
            $table->decimal('default_amount', 15, 2)->default(0);
            $table->decimal('default_percentage', 8, 4)->default(0);
            $table->boolean('is_taxable')->default(false);
            $table->boolean('is_active')->default(true);
            $table->foreignId('account_id')->nullable()->constrained('accounts')->nullOnDelete();
            $table->text('description')->nullable();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['component_type', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('salary_components');
    }
};

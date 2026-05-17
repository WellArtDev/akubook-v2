<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('accounts', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->unique();
            $table->string('name');
            $table->enum('type', ['asset', 'liability', 'equity', 'revenue', 'expense'])->index();
            $table->enum('category', ['current_asset', 'fixed_asset', 'current_liability', 'long_term_liability', 'equity', 'operating_revenue', 'other_revenue', 'operating_expense', 'other_expense'])->nullable();
            $table->foreignId('parent_id')->nullable()->constrained('accounts')->nullOnDelete();
            $table->integer('level')->default(1);
            $table->boolean('is_header')->default(false);
            $table->boolean('is_active')->default(true);
            $table->text('description')->nullable();
            $table->decimal('balance', 20, 2)->default(0);
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['type', 'is_active']);
            $table->index('parent_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('accounts');
    }
};

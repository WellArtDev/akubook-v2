<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fixed_assets', function (Blueprint $table) {
            $table->id();
            $table->string('asset_code', 50)->unique();
            $table->string('name', 255);
            $table->string('category', 100)->nullable();
            $table->date('acquisition_date');
            $table->decimal('acquisition_cost', 15, 2);
            $table->unsignedInteger('useful_life_months');
            $table->decimal('residual_value', 15, 2)->default(0);
            $table->enum('status', ['active', 'inactive', 'disposed'])->default('active');
            $table->foreignId('asset_account_id')->constrained('accounts')->restrictOnDelete();
            $table->foreignId('accumulated_depreciation_account_id')->constrained('accounts')->restrictOnDelete();
            $table->foreignId('depreciation_expense_account_id')->constrained('accounts')->restrictOnDelete();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'acquisition_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fixed_assets');
    }
};

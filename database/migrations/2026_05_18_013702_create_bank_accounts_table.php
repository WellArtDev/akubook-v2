<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bank_accounts', function (Blueprint $table) {
            $table->id();
            $table->string('code', 30)->unique();
            $table->string('name');
            $table->string('bank_name');
            $table->string('account_number', 100)->unique();
            $table->string('account_holder');
            $table->foreignId('account_id')->constrained('accounts')->restrictOnDelete();
            $table->decimal('opening_balance', 20, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->text('description')->nullable();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index('is_active');
            $table->index('bank_name');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bank_accounts');
    }
};

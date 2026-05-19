<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tax_configurations', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();
            $table->string('name');
            $table->enum('tax_type', ['ppn_out', 'ppn_in', 'withholding']);
            $table->decimal('rate', 8, 4);
            $table->foreignId('account_id')->constrained('accounts')->restrictOnDelete();
            $table->boolean('is_default')->default(false);
            $table->boolean('is_active')->default(true);
            $table->text('description')->nullable();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['tax_type', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tax_configurations');
    }
};

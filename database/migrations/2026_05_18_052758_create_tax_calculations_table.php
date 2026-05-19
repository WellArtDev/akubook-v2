<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tax_calculations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tax_configuration_id')->constrained('tax_configurations')->restrictOnDelete();
            $table->enum('tax_type', ['ppn_out', 'ppn_in', 'withholding']);
            $table->decimal('taxable_amount', 18, 2);
            $table->boolean('is_inclusive')->default(false);
            $table->decimal('rate', 8, 4);
            $table->decimal('dpp', 18, 2);
            $table->decimal('tax_amount', 18, 2);
            $table->decimal('grand_total', 18, 2);
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->timestamps();

            $table->index(['tax_type', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tax_calculations');
    }
};

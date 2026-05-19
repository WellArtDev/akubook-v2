<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('e_faktur_export_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('e_faktur_export_id')->constrained('e_faktur_exports')->cascadeOnDelete();
            $table->foreignId('faktur_pajak_id')->constrained('faktur_pajaks')->restrictOnDelete();
            $table->unsignedInteger('line_number');
            $table->string('faktur_number', 50);
            $table->date('faktur_date');
            $table->string('customer_name', 255);
            $table->string('customer_tax_id', 100)->nullable();
            $table->decimal('dpp', 15, 2);
            $table->decimal('ppn_amount', 15, 2);
            $table->decimal('grand_total', 15, 2);
            $table->timestamps();

            $table->index(['e_faktur_export_id', 'line_number']);
            $table->unique(['e_faktur_export_id', 'faktur_pajak_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('e_faktur_export_lines');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('asset_depreciations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fixed_asset_id')->constrained('fixed_assets')->cascadeOnDelete();
            $table->string('period', 7);
            $table->decimal('monthly_depreciation', 15, 2);
            $table->decimal('accumulated_depreciation', 15, 2);
            $table->decimal('book_value_end', 15, 2);
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->timestamps();

            $table->unique(['fixed_asset_id', 'period']);
            $table->index('period');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asset_depreciations');
    }
};

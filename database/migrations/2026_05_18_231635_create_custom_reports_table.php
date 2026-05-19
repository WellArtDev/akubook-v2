<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('custom_reports', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();
            $table->string('name', 150);
            $table->string('source_key', 50);
            $table->json('selected_columns');
            $table->json('default_filters')->nullable();
            $table->boolean('is_active')->default(true);
            $table->text('description')->nullable();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['source_key', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('custom_reports');
    }
};

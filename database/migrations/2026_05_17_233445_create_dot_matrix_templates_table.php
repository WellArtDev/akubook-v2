<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dot_matrix_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('document_type', 50);
            $table->string('paper_size', 50)->default('continuous_9_5x11');
            $table->unsignedInteger('columns')->default(80);
            $table->unsignedInteger('rows')->default(66);
            $table->json('margins')->nullable();
            $table->json('field_map');
            $table->boolean('is_default')->default(false);
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['document_type', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dot_matrix_templates');
    }
};

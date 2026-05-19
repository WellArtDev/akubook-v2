<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('print_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('print_draft_id')->constrained('print_drafts')->cascadeOnDelete();
            $table->string('document_type', 50);
            $table->unsignedBigInteger('document_id');
            $table->foreignId('dot_matrix_template_id')->constrained('dot_matrix_templates')->restrictOnDelete();
            $table->foreignId('printed_by')->constrained('users')->restrictOnDelete();
            $table->timestamp('printed_at');
            $table->json('output_metadata')->nullable();
            $table->timestamps();

            $table->index(['document_type', 'document_id']);
            $table->index('printed_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('print_histories');
    }
};

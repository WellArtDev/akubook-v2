<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('print_drafts', function (Blueprint $table) {
            $table->id();
            $table->string('draft_number', 50)->unique();
            $table->string('document_type', 50);
            $table->unsignedBigInteger('document_id');
            $table->foreignId('dot_matrix_template_id')->constrained('dot_matrix_templates')->restrictOnDelete();
            $table->json('override_payload');
            $table->enum('status', ['draft', 'ready'])->default('draft');
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['document_type', 'document_id']);
            $table->index(['status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('print_drafts');
    }
};

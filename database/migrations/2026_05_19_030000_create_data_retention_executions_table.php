<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('data_retention_executions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('data_retention_policy_id')->constrained('data_retention_policies')->restrictOnDelete();
            $table->string('mode', 20);
            $table->string('entity_type', 100);
            $table->string('action', 30);
            $table->date('cutoff_date');
            $table->unsignedInteger('candidate_count')->default(0);
            $table->unsignedInteger('processed_count')->default(0);
            $table->string('status', 30)->default('completed');
            $table->json('summary')->nullable();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->timestamps();

            $table->index(['entity_type', 'status']);
            $table->index(['mode', 'action']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('data_retention_executions');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('data_retention_policies', function (Blueprint $table) {
            $table->id();
            $table->string('policy_key', 100)->unique();
            $table->string('entity_type', 100);
            $table->unsignedInteger('retention_days');
            $table->enum('action', ['archive', 'delete'])->default('archive');
            $table->boolean('is_active')->default(true);
            $table->text('description')->nullable();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['entity_type', 'is_active']);
            $table->index('action');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('data_retention_policies');
    }
};

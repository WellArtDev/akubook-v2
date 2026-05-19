<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('approval_workflows', function (Blueprint $table) {
            $table->id();
            $table->string('workflow_key', 100)->unique();
            $table->string('entity_type', 100);
            $table->decimal('min_amount', 15, 2)->default(0);
            $table->decimal('max_amount', 15, 2)->nullable();
            $table->unsignedInteger('required_level')->default(1);
            $table->boolean('is_active')->default(true);
            $table->text('description')->nullable();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['entity_type', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('approval_workflows');
    }
};

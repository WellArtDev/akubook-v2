<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sensitive_alerts', function (Blueprint $table) {
            $table->id();
            $table->string('idempotency_key', 120)->unique();
            $table->string('window', 40);
            $table->timestamp('window_start');
            $table->timestamp('window_end');
            $table->unsignedInteger('high_count')->default(0);
            $table->unsignedInteger('threshold')->default(0);
            $table->json('top_entities')->nullable();
            $table->string('status', 30)->default('triggered');
            $table->timestamp('generated_at');
            $table->foreignId('generated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['window_start', 'window_end']);
            $table->index(['status', 'generated_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sensitive_alerts');
    }
};

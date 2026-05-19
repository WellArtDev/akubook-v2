<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('offline_sync_events', function (Blueprint $table) {
            $table->id();
            $table->string('client_event_id')->unique();
            $table->string('entity', 50);
            $table->string('action', 50);
            $table->json('payload');
            $table->enum('status', ['synced', 'duplicate', 'failed'])->default('synced');
            $table->text('failure_reason')->nullable();
            $table->nullableMorphs('source');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['entity', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('offline_sync_events');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('audit_logs', function (Blueprint $table) {
            $table->string('event_key', 100)->nullable()->after('event');
            $table->string('entity_type', 100)->nullable()->after('event_key');
            $table->unsignedBigInteger('entity_id')->nullable()->after('entity_type');
            $table->string('action', 30)->nullable()->after('entity_id');
            $table->foreignId('actor_user_id')->nullable()->after('action')->constrained('users')->nullOnDelete();
            $table->timestamp('occurred_at')->nullable()->after('actor_user_id');
            $table->json('metadata')->nullable()->after('new_values');
            $table->index(['entity_type', 'entity_id']);
            $table->index('event_key');
            $table->index('occurred_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('audit_logs', function (Blueprint $table) {
            $table->dropIndex(['entity_type', 'entity_id']);
            $table->dropIndex(['event_key']);
            $table->dropIndex(['occurred_at']);
            $table->dropConstrainedForeignId('actor_user_id');
            $table->dropColumn(['event_key', 'entity_type', 'entity_id', 'action', 'occurred_at', 'metadata']);
        });
    }
};

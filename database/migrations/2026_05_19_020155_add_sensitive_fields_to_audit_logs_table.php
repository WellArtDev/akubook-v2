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
            $table->boolean('is_sensitive')->default(false)->after('action');
            $table->string('sensitivity_level', 30)->nullable()->after('is_sensitive');
            $table->string('sensitivity_reason')->nullable()->after('sensitivity_level');
            $table->index(['is_sensitive', 'occurred_at']);
            $table->index('sensitivity_level');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('audit_logs', function (Blueprint $table) {
            $table->dropIndex(['is_sensitive', 'occurred_at']);
            $table->dropIndex(['sensitivity_level']);
            $table->dropColumn(['is_sensitive', 'sensitivity_level', 'sensitivity_reason']);
        });
    }
};

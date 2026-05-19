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
        Schema::table('asset_depreciations', function (Blueprint $table) {
            $table->foreignId('journal_entry_id')->nullable()->after('book_value_end')->constrained('journal_entries')->nullOnDelete();
            $table->timestamp('journal_posted_at')->nullable()->after('journal_entry_id');
            $table->index(['period', 'journal_entry_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('asset_depreciations', function (Blueprint $table) {
            $table->dropIndex(['period', 'journal_entry_id']);
            $table->dropConstrainedForeignId('journal_entry_id');
            $table->dropColumn('journal_posted_at');
        });
    }
};

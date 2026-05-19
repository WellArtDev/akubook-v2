<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('asset_disposals', function (Blueprint $table) {
            $table->id();
            $table->string('disposal_number', 50)->unique();
            $table->date('disposal_date');
            $table->foreignId('fixed_asset_id')->constrained('fixed_assets')->restrictOnDelete();
            $table->decimal('acquisition_cost', 15, 2);
            $table->decimal('accumulated_depreciation', 15, 2)->default(0);
            $table->decimal('book_value', 15, 2)->default(0);
            $table->decimal('proceeds_amount', 15, 2)->default(0);
            $table->foreignId('proceeds_account_id')->nullable()->constrained('accounts')->nullOnDelete();
            $table->foreignId('gain_loss_account_id')->constrained('accounts')->restrictOnDelete();
            $table->enum('status', ['draft', 'posted'])->default('draft');
            $table->foreignId('journal_entry_id')->nullable()->constrained('journal_entries')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->foreignId('posted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('posted_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['disposal_date', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asset_disposals');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('compliance_export_packs', function (Blueprint $table) {
            $table->id();
            $table->string('pack_number', 40)->unique();
            $table->date('period_start');
            $table->date('period_end');
            $table->string('status', 30)->default('generated');
            $table->json('record_counts');
            $table->json('metadata');
            $table->longText('payload_json');
            $table->foreignId('generated_by')->constrained('users')->restrictOnDelete();
            $table->timestamp('generated_at');
            $table->timestamps();
            $table->index(['period_start', 'period_end']);
            $table->index(['status', 'generated_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('compliance_export_packs');
    }
};

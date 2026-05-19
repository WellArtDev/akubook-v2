<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employee_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->restrictOnDelete();
            $table->string('document_type', 50);
            $table->string('document_number', 100);
            $table->date('issue_date');
            $table->date('expiry_date')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['employee_id', 'status']);
            $table->index(['document_type', 'status']);
            $table->unique(['employee_id', 'document_type', 'document_number'], 'employee_doc_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_documents');
    }
};

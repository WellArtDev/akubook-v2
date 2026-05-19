<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('suppliers', function (Blueprint $table) {
            // Rename code to supplier_code
            $table->renameColumn('code', 'supplier_code');
            
            // Add new fields
            $table->string('category', 100)->nullable()->after('name');
            $table->enum('tax_type', ['pkp', 'non_pkp'])->default('non_pkp')->after('tax_id');
            $table->string('payment_terms', 50)->nullable()->after('tax_type');
            $table->string('website')->nullable()->after('email');
            $table->decimal('delivery_rating', 3, 2)->default(0)->after('notes');
            $table->decimal('quality_rating', 3, 2)->default(0)->after('delivery_rating');
            $table->decimal('total_purchase_amount', 15, 2)->default(0)->after('quality_rating');
            $table->date('last_purchase_date')->nullable()->after('total_purchase_amount');
            $table->foreignId('created_by')->nullable()->constrained('users')->after('last_purchase_date');
            $table->foreignId('updated_by')->nullable()->constrained('users')->after('created_by');
            $table->softDeletes()->after('updated_at');
            
            // Drop old fields
            $table->dropColumn(['contact_person', 'address', 'city', 'payment_terms_days', 'is_active']);
        });
    }

    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() === 'sqlite') {
            return;
        }

        Schema::table('suppliers', function (Blueprint $table) {
            // Restore old fields
            $table->string('contact_person')->nullable();
            $table->text('address')->nullable();
            $table->string('city')->nullable();
            $table->integer('payment_terms_days')->default(0);
            $table->boolean('is_active')->default(true);

            // Drop new fields
            $table->dropColumn([
                'category', 'tax_type', 'payment_terms', 'website',
                'delivery_rating', 'quality_rating', 'total_purchase_amount',
                'last_purchase_date', 'created_by', 'updated_by', 'deleted_at'
            ]);

            // Rename back
            $table->renameColumn('supplier_code', 'code');
        });
    }
};

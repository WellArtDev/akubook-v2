<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::getConnection()->getDriverName() !== 'sqlite') {
            Schema::table('customers', function (Blueprint $table) {
                if (Schema::hasColumn('customers', 'customer_type')) {
                    $table->dropColumn('customer_type');
                }
                if (Schema::hasColumn('customers', 'contact_person')) {
                    $table->dropColumn('contact_person');
                }
                if (Schema::hasColumn('customers', 'address')) {
                    $table->dropColumn('address');
                }
                if (Schema::hasColumn('customers', 'city')) {
                    $table->dropColumn('city');
                }
                if (Schema::hasColumn('customers', 'payment_terms_days')) {
                    $table->dropColumn('payment_terms_days');
                }
            });
        }

        Schema::table('customers', function (Blueprint $table) {
            if (! Schema::hasColumn('customers', 'category')) {
                $table->string('category', 50)->default('retail')->after('name');
            }
            if (! Schema::hasColumn('customers', 'tax_type')) {
                $table->string('tax_type', 20)->default('non_pkp')->after('tax_id');
            }
            if (! Schema::hasColumn('customers', 'website')) {
                $table->string('website')->nullable()->after('email');
            }
            if (! Schema::hasColumn('customers', 'payment_terms')) {
                $table->integer('payment_terms')->default(0)->after('credit_limit');
            }
            if (! Schema::hasColumn('customers', 'outstanding_balance')) {
                $table->decimal('outstanding_balance', 20, 2)->default(0)->after('payment_terms');
            }
        });
    }

    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() === 'sqlite') {
            return;
        }

        Schema::table('customers', function (Blueprint $table) {
            $table->enum('customer_type', ['individual', 'company'])->default('company');
            $table->string('contact_person')->nullable();
            $table->text('address')->nullable();
            $table->string('city')->nullable();
            $table->integer('payment_terms_days')->default(0);
            $table->dropColumn(['category', 'tax_type', 'website', 'payment_terms', 'outstanding_balance']);
        });
    }
};

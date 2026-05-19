<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customer_addresses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->onDelete('cascade');
            $table->enum('address_type', ['billing', 'shipping', 'both']);
            $table->text('street_address');
            $table->string('city', 100);
            $table->string('province', 100);
            $table->string('postal_code', 20)->nullable();
            $table->string('country', 100)->default('Indonesia');
            $table->boolean('is_default')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_addresses');
    }
};

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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('provider_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->string('buyer_sku_code')->unique(); // our code, per Digiflazz "buyer_sku_code"
            $table->string('name');
            $table->string('brand')->nullable();
            $table->string('type')->nullable();
            $table->decimal('price_beli', 15, 2); // cost price from provider price-list
            $table->decimal('price_jual', 15, 2); // base selling price before price_level markup
            $table->boolean('unlimited_stock')->default(true);
            $table->integer('stock')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};

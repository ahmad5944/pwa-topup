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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete(); // nullable: guest checkout
            $table->foreignId('product_id')->constrained();
            $table->string('target_number');
            $table->string('status')->default('pending'); // pending|processing|success|failed|refund
            $table->decimal('price', 15, 2);
            $table->string('ref_id')->unique(); // our idempotency key sent to Digiflazz
            $table->string('provider_ref_id')->nullable(); // provider's serial number ("sn")
            $table->string('invoice_no')->unique();
            $table->timestamps();

            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};

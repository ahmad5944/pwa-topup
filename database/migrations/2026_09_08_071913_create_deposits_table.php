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
        Schema::create('deposits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->decimal('amount', 15, 2);
            $table->string('method'); // midtrans_va, midtrans_qris, midtrans_ewallet, manual_transfer
            $table->string('status')->default('pending'); // pending|success|failed
            $table->string('order_id')->unique(); // sent to Midtrans as order_id, idempotency key
            $table->string('gateway_ref_id')->nullable(); // Midtrans transaction_id
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete(); // manual approval
            $table->timestamps();

            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('deposits');
    }
};

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
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone')->nullable()->unique()->after('email');
            $table->decimal('balance', 15, 2)->default(0)->after('phone');
            // FK constraint added in create_price_levels_table migration, which runs after this one.
            $table->unsignedBigInteger('price_level_id')->nullable()->after('balance');
            $table->index('price_level_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['phone', 'balance', 'price_level_id']);
        });
    }
};

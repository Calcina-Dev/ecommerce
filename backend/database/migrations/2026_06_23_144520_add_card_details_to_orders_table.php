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
        Schema::table('orders', function (Blueprint $table) {
            $table->string('card_brand')->nullable();
            $table->string('card_last_digits', 4)->nullable();
            $table->string('card_country', 2)->nullable();
            $table->boolean('is_foreign_card')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['card_brand', 'card_last_digits', 'card_country', 'is_foreign_card']);
        });
    }
};

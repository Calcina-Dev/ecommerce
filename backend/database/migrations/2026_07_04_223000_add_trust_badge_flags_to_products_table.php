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
        Schema::table('products', function (Blueprint $table) {
            $table->boolean('show_gmp_badge')->default(true)->after('is_featured');
            $table->boolean('show_fefo_badge')->default(true)->after('show_gmp_badge');
            $table->boolean('show_shipping_badge')->default(true)->after('show_fefo_badge');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['show_gmp_badge', 'show_fefo_badge', 'show_shipping_badge']);
        });
    }
};

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
        Schema::table('shipping_methods', function (Blueprint $table) {
            $table->dropColumn('cost');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->decimal('shipping_cost', 10, 2)->default(0)->after('tracking_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('shipping_cost');
        });

        Schema::table('shipping_methods', function (Blueprint $table) {
            $table->decimal('cost', 10, 2)->default(0);
        });
    }
};

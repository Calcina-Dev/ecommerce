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
            $table->decimal('total_cost', 10, 2)->default(0)->after('total_amount');
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->decimal('unit_cost', 10, 2)->default(0)->after('price');
        });
    }

    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropColumn('unit_cost');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('total_cost');
        });
    }
};

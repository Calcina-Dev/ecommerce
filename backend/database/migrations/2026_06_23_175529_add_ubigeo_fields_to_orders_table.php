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
            $table->string('shipping_department')->nullable()->after('shipping_address');
            $table->string('shipping_province')->nullable()->after('shipping_department');
            $table->string('shipping_district')->nullable()->after('shipping_province');
            $table->string('shipping_postal_code')->nullable()->after('shipping_district');
            $table->string('shipping_city')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'shipping_department',
                'shipping_province',
                'shipping_district',
                'shipping_postal_code'
            ]);
            $table->string('shipping_city')->nullable(false)->change();
        });
    }
};

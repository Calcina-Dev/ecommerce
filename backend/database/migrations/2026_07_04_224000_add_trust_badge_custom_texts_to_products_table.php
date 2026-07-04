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
            $table->string('badge_1_title')->nullable()->after('show_gmp_badge');
            $table->string('badge_1_subtitle')->nullable()->after('badge_1_title');
            $table->string('badge_2_title')->nullable()->after('show_fefo_badge');
            $table->string('badge_2_subtitle')->nullable()->after('badge_2_title');
            $table->string('badge_3_title')->nullable()->after('show_shipping_badge');
            $table->string('badge_3_subtitle')->nullable()->after('badge_3_title');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn([
                'badge_1_title', 'badge_1_subtitle',
                'badge_2_title', 'badge_2_subtitle',
                'badge_3_title', 'badge_3_subtitle',
            ]);
        });
    }
};

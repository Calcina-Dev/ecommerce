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
        Schema::table('order_notes', function (Blueprint $table) {
            $table->string('type')->default('system')->after('content'); // 'system', 'private', 'customer'
        });

        // Set type based on old is_system
        \Illuminate\Support\Facades\DB::table('order_notes')
            ->where('is_system', false)
            ->update(['type' => 'private']);

        Schema::table('order_notes', function (Blueprint $table) {
            $table->dropColumn('is_system');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_notes', function (Blueprint $table) {
            $table->boolean('is_system')->default(false)->after('content');
        });

        \Illuminate\Support\Facades\DB::table('order_notes')
            ->where('type', 'system')
            ->update(['is_system' => true]);

        Schema::table('order_notes', function (Blueprint $table) {
            $table->dropColumn('type');
        });
    }
};

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
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->string('document_series')->nullable();
            $table->string('document_number')->nullable();
        });

        Schema::table('warehouse_transfers', function (Blueprint $table) {
            $table->string('document_series')->nullable();
            $table->string('document_number')->nullable();
        });

        Schema::table('stock_movements', function (Blueprint $table) {
            $table->string('document_series')->nullable();
            $table->string('document_number')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->dropColumn(['document_series', 'document_number']);
        });

        Schema::table('warehouse_transfers', function (Blueprint $table) {
            $table->dropColumn(['document_series', 'document_number']);
        });

        Schema::table('stock_movements', function (Blueprint $table) {
            $table->dropColumn(['document_series', 'document_number']);
        });
    }
};

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
            $table->string('document_type')->nullable()->after('payment_status');
            $table->string('document_series')->nullable()->after('document_type');
            $table->string('document_number')->nullable()->after('document_series');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['document_type', 'document_series', 'document_number']);
        });
    }
};

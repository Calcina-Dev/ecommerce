<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Añadir campo de palabras clave (tags / keywords) para búsquedas específicas como "dolor muscular"
     * y crear índice de trigramas GIN en PostgreSQL para búsquedas ilike instantáneas.
     */
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->text('keywords')->nullable()->after('description');
        });

        // Crear índice GIN de trigramas en keywords para búsquedas parciales (ILIKE '%term%') veloces
        DB::statement('CREATE EXTENSION IF NOT EXISTS pg_trgm;');
        DB::statement('CREATE INDEX IF NOT EXISTS idx_products_keywords_trgm ON products USING gin (keywords gin_trgm_ops);');
    }

    public function down(): void
    {
        DB::statement('DROP INDEX IF EXISTS idx_products_keywords_trgm;');

        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('keywords');
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Habilitar extensión pg_trgm y crear índice GIN de trigramas
     * para búsquedas ILIKE '%term%' ultrarrápidas en el nombre del producto.
     */
    public function up(): void
    {
        DB::statement('CREATE EXTENSION IF NOT EXISTS pg_trgm;');
        DB::statement('CREATE INDEX IF NOT EXISTS idx_products_name_trgm ON products USING gin (name gin_trgm_ops);');
    }

    public function down(): void
    {
        DB::statement('DROP INDEX IF EXISTS idx_products_name_trgm;');
    }
};

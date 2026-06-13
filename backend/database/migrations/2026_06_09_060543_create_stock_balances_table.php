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
        Schema::create('stock_balances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('warehouse_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('batch_id')->nullable()->constrained()->cascadeOnDelete();
            $table->integer('on_hand')->default(0);
            $table->timestamps();
            
            // Allow multiple products in same warehouse if batch is different, or single if batch is null
            // We shouldn't put a strict unique constraint on just product+warehouse anymore if batch matters.
            // Actually, we can just let it exist or make a unique index on warehouse, product, and batch.
            // Since batch_id can be null, in some DBs unique on null allows multiples. 
            // Better to handle it at app level.
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_balances');
    }
};

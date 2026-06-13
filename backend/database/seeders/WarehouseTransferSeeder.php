<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Models\Warehouse;
use App\Models\WarehouseTransfer;
use App\Models\Product;

class WarehouseTransferSeeder extends Seeder
{
    public function run(): void
    {
        // Find stock balances that have stock
        $availableStock = \App\Models\StockBalance::where('on_hand', '>', 0)->inRandomOrder()->take(3)->get();
        
        $warehouses = Warehouse::all();

        if ($warehouses->count() < 2 || $availableStock->isEmpty()) return;

        foreach ($availableStock as $index => $stock) {
            $from = $stock->warehouse;
            $to = $warehouses->where('id', '!=', $from->id)->random();

            $transfer = WarehouseTransfer::create([
                'from_warehouse_id' => $from->id,
                'to_warehouse_id' => $to->id,
                'status' => 'DRAFT',
                'notes' => 'Transferencia de prueba ' . ($index + 1),
            ]);

            $transfer->items()->create([
                'product_id' => $stock->product_id,
                'quantity' => min(rand(2, 5), $stock->on_hand),
            ]);

            $transfer->update(['status' => 'COMPLETED']);
        }
    }
}

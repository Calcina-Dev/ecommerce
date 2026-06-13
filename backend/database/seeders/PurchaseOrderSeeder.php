<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Models\Product;

class PurchaseOrderSeeder extends Seeder
{
    public function run(): void
    {
        // First ensure we have a supplier
        $supplier = Supplier::firstOrCreate(
            ['email' => 'proveedor@test.com'],
            ['name' => 'Proveedor de Prueba', 'phone' => '999888777']
        );

        $products = Product::inRandomOrder()->take(5)->get();
        if ($products->isEmpty()) return;

        for ($i = 0; $i < 3; $i++) {
            $po = PurchaseOrder::create([
                'supplier_id' => $supplier->id,
                'status' => 'DRAFT',
                'total_amount' => rand(100, 1000),
                'notes' => 'Orden de compra de prueba',
            ]);

            $qty = rand(10, 50);
            $cost = rand(10, 50);
            $po->items()->create([
                'product_id' => $products->random()->id,
                'quantity' => $qty,
                'unit_cost' => $cost,
                'subtotal' => $qty * $cost,
            ]);
        }
    }
}

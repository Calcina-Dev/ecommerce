<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class InventorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Obtener el almacén principal y el usuario admin
        $warehouse = \App\Models\Warehouse::first();
        $admin = \App\Models\User::first();
        
        if (!$warehouse || !$admin) {
            $this->command->warn('No warehouse or admin user found. Skipping InventorySeeder.');
            return;
        }

        $products = \App\Models\Product::all();
        
        if ($products->isEmpty()) {
            $this->command->warn('No products found. Skipping InventorySeeder.');
            return;
        }

        $faker = \Faker\Factory::create();

        // Let's create at least 10 batches and movements
        $totalSeeded = 0;

        foreach ($products as $product) {
            if ($totalSeeded >= 12) break;

            // Generate 1 or 2 batches per product
            $numBatches = rand(1, 2);
            
            for ($i = 0; $i < $numBatches; $i++) {
                $batch = \App\Models\Batch::create([
                    'product_id' => $product->id,
                    'batch_number' => 'LOTE-' . strtoupper($faker->bothify('??###')),
                    'expiration_date' => now()->addMonths(rand(3, 24)),
                    'status' => 'active',
                ]);

                $initialQuantity = rand(20, 100);

                // Create StockBalance
                \App\Models\StockBalance::create([
                    'warehouse_id' => $warehouse->id,
                    'product_id' => $product->id,
                    'batch_id' => $batch->id,
                    'on_hand' => $initialQuantity,
                ]);

                // Record the movement (Initial Adjustment)
                \App\Models\StockMovement::create([
                    'warehouse_id' => $warehouse->id,
                    'product_id' => $product->id,
                    'batch_id' => $batch->id,
                    'user_id' => $admin->id,
                    'type' => 'IN',
                    'quantity' => $initialQuantity,
                    'reason' => 'Ajuste Inicial',
                    'reference_type' => null,
                    'reference_id' => null,
                    'notes' => 'Carga inicial de inventario (migración)',
                ]);
                
                $totalSeeded++;
            }
        }
    }
}

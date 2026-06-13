<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PurchaseInvoiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $supplier = \App\Models\Supplier::firstOrCreate(
            ['email' => 'proveedor@vitamins.com'],
            ['name' => 'Proveedor Global Vitamins', 'phone' => '123456789']
        );
        $warehouse = \App\Models\Warehouse::first();
        $products = \App\Models\Product::take(5)->get();

        if (!$warehouse || $products->isEmpty()) return;

        $faker = \Faker\Factory::create();

        for ($i = 0; $i < 10; $i++) {
            $invoice = \App\Models\PurchaseInvoice::create([
                'supplier_id' => $supplier->id,
                'warehouse_id' => $warehouse->id,
                'document_number' => 'FAC-' . $faker->unique()->numerify('######'),
                'issue_date' => now()->subDays(rand(1, 30)),
                'total_amount' => rand(1000, 5000),
                'status' => 'DRAFT', // We save as DRAFT first
            ]);

            $numLines = rand(1, 3);
            for ($j = 0; $j < $numLines; $j++) {
                $product = $products->random();
                $qty = rand(10, 50);
                $cost = rand(10, 50);
                \App\Models\PurchaseInvoiceLine::create([
                    'purchase_invoice_id' => $invoice->id,
                    'product_id' => $product->id,
                    'batch_number' => 'LOTE-INV-' . strtoupper($faker->bothify('??###')),
                    'expiration_date' => now()->addMonths(rand(6, 24)),
                    'quantity' => $qty,
                    'unit_cost' => $cost,
                    'subtotal' => $qty * $cost,
                ]);
            }

            // Update to VALID to trigger observer and create stock
            $invoice->update(['status' => 'VALID']);
        }
    }
}

<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Supplier;
use App\Models\Warehouse;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\PurchaseInvoice;
use App\Models\PurchaseInvoiceLine;

class PurchaseFlowSeeder extends Seeder
{
    public function run(): void
    {
        // Limpiar toda la data de compras/inventario
        DB::statement('SET session_replication_role = replica;');
        DB::table('purchase_invoice_lines')->truncate();
        DB::table('purchase_invoices')->truncate();
        DB::table('purchase_order_items')->truncate();
        DB::table('purchase_orders')->truncate();
        DB::table('stock_movements')->truncate();
        DB::table('stock_balances')->truncate();
        DB::table('batches')->truncate();
        DB::table('document_series')->whereIn('document_type', ['ORDEN_COMPRA', 'NOTA_INGRESO'])->update(['current_number' => 0]);
        DB::statement('SET session_replication_role = DEFAULT;');

        $supplier = Supplier::firstOrCreate(
            ['email' => 'proveedor@vitamins.com'],
            ['name' => 'Proveedor Global Vitamins', 'phone' => '123456789']
        );
        $warehouse = Warehouse::first();
        $products = Product::all();

        if (!$warehouse || $products->isEmpty()) return;

        $faker = \Faker\Factory::create();

        // 1. Asegurar que TODOS los productos tengan una orden de compra recibida
        $productChunks = $products->chunk(8); // Agrupar de 8 en 8 por Orden de Compra
        
        foreach ($productChunks as $index => $chunk) {
            $pastDate = now()->subDays(rand(5, 30));
            $order = PurchaseOrder::create([
                'supplier_id' => $supplier->id,
                'status' => 'draft',
                'expected_delivery_date' => $pastDate->copy()->addDays(rand(1, 3)),
                'notes' => 'Pedido de abastecimiento inicial ' . ($index + 1),
                'created_at' => $pastDate,
                'updated_at' => $pastDate,
            ]);
            $order->update(['status' => 'sent']); // Simulamos que se envió

            $total = 0;
            $items = [];
            
            foreach ($chunk as $product) {
                $qty = rand(100, 250);
                $cost = rand(10, 50);
                $subtotal = $qty * $cost;
                $total += $subtotal;
                
                $items[] = PurchaseOrderItem::create([
                    'purchase_order_id' => $order->id,
                    'product_id' => $product->id,
                    'quantity' => $qty,
                    'unit_cost' => $cost,
                    'subtotal' => $subtotal,
                ]);
            }
            $order->update(['total_amount' => $total]);

            // Probabilidad de tener flete y descuento
            $shippingCost = rand(1, 100) <= 60 ? rand(20, 150) : 0; // 60% prob de flete
            $discount = rand(1, 100) <= 30 ? rand(10, 50) : 0; // 30% prob de descuento

            // Ahora recibimos la orden completamente a través de una factura
            $invoiceDate = $pastDate->copy()->addDays(rand(1, 3));
            $invoice = PurchaseInvoice::create([
                'purchase_order_id' => $order->id,
                'supplier_id' => $supplier->id,
                'warehouse_id' => $warehouse->id,
                'document_number' => 'FAC-' . $faker->unique()->numerify('######'),
                'issue_date' => $invoiceDate,
                'total_amount' => $total + $shippingCost - $discount,
                'shipping_cost' => $shippingCost,
                'discount' => $discount,
                'status' => 'DRAFT',
                'created_at' => $invoiceDate,
                'updated_at' => $invoiceDate,
            ]);

            foreach ($items as $item) {
                PurchaseInvoiceLine::create([
                    'purchase_invoice_id' => $invoice->id,
                    'product_id' => $item->product_id,
                    'batch_number' => 'LOTE-' . strtoupper($faker->bothify('??###')),
                    'expiration_date' => now()->addMonths(rand(6, 24)),
                    'quantity' => $item->quantity,
                    'unit_cost' => $item->unit_cost,
                    'subtotal' => $item->subtotal,
                ]);
            }
            // Validar factura (esto crea el stock movement, lote, y balance y actualiza el stock del producto)
            $invoice->update(['status' => 'VALID']);

            // Actualizar created_at de los stock_movements para reflejar el historial real
            DB::table('stock_movements')
                ->where('reference_type', PurchaseInvoice::class)
                ->where('reference_id', $invoice->id)
                ->update(['created_at' => $invoiceDate, 'updated_at' => $invoiceDate]);
            
            // Actualizar la orden a completada
            $order->update(['status' => 'completed']);
        }

        // 2. Crear un par de Órdenes de Compra en estado DRAFT o SENT (Esperando recepción) aleatorias recientes
        for ($i = 0; $i < 3; $i++) {
            $recentDate = now()->subDays(rand(0, 4));
            $order = PurchaseOrder::create([
                'supplier_id' => $supplier->id,
                'status' => $i === 0 ? 'draft' : 'sent',
                'expected_delivery_date' => $recentDate->copy()->addDays(rand(2, 7)),
                'notes' => 'Pedido en espera de recepción ' . ($i+1),
                'created_at' => $recentDate,
                'updated_at' => $recentDate,
            ]);

            $total = 0;
            $numLines = rand(1, 4);
            for ($j = 0; $j < $numLines; $j++) {
                $product = $products->random();
                $qty = rand(20, 50);
                $cost = rand(15, 30);
                $subtotal = $qty * $cost;
                $total += $subtotal;
                
                PurchaseOrderItem::create([
                    'purchase_order_id' => $order->id,
                    'product_id' => $product->id,
                    'quantity' => $qty,
                    'unit_cost' => $cost,
                    'subtotal' => $subtotal,
                ]);
            }
            $order->update(['total_amount' => $total]);
        }
    }
}

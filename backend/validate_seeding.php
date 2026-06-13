<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Product;
use App\Models\StockBalance;
use App\Models\StockMovement;
use App\Models\Sale;
use App\Models\Order;
use App\Models\PurchaseInvoice;

echo "=== REPORTE DE VALIDACIÓN (1 MES) ===\n\n";

// 1. Validar Stock (StockBalance vs StockMovements vs Product->stock)
echo "1. Validando Inventario...\n";
$products = Product::all();
$inventoryErrors = 0;
foreach ($products as $product) {
    $calculatedFromMovements = StockMovement::where('product_id', $product->id)->get()->sum(function($mov) {
        return $mov->type === 'IN' ? $mov->quantity : -$mov->quantity;
    });
    
    $calculatedFromBalances = StockBalance::where('product_id', $product->id)->sum('on_hand');
    
    if ($calculatedFromMovements != $calculatedFromBalances || $product->stock != $calculatedFromBalances) {
        echo "[ERROR] Producto {$product->id}: Movements({$calculatedFromMovements}) != Balances({$calculatedFromBalances}) != ProductStock({$product->stock})\n";
        $inventoryErrors++;
    }
}
if ($inventoryErrors === 0) {
    echo "[OK] Todo el stock cuadra perfectamente entre movimientos, balances y tabla de productos.\n";
}

// 2. Validar Prorrateo de Compras
echo "\n2. Validando Prorrateo de Compras...\n";
$invoices = PurchaseInvoice::where('status', 'VALID')->get();
$prorrateoErrors = 0;
foreach ($invoices as $invoice) {
    $lines = $invoice->lines;
    $movements = StockMovement::where('reference_type', PurchaseInvoice::class)
        ->where('reference_id', $invoice->id)->get();
        
    $totalMovementsCost = $movements->sum('total_cost');
    $expectedTotal = $invoice->total_amount;
    
    // Allow small rounding differences (0.05)
    if (abs($totalMovementsCost - $expectedTotal) > 0.05) {
        echo "[ERROR] Factura {$invoice->document_number}: Total Factura ({$expectedTotal}) != Total Ingresado a Kardex ({$totalMovementsCost})\n";
        $prorrateoErrors++;
    }
}
if ($prorrateoErrors === 0) {
    echo "[OK] Todos los costos de flete y descuentos se han prorrateado e ingresado al Kardex sin pérdida de valor.\n";
}

// 3. Validar Fechas de Movimientos
echo "\n3. Validando Línea de Tiempo...\n";
$oldestMovement = StockMovement::orderBy('created_at', 'asc')->first();
$newestMovement = StockMovement::orderBy('created_at', 'desc')->first();
if ($oldestMovement && $newestMovement) {
    echo "[INFO] Los movimientos abarcan desde {$oldestMovement->created_at->toDateString()} hasta {$newestMovement->created_at->toDateString()}.\n";
} else {
    echo "[ERROR] No hay movimientos.\n";
}

// 4. Validar Ventas y Órdenes
echo "\n4. Validando Ventas...\n";
$totalSales = Sale::count();
$totalOrders = Order::count();
echo "[INFO] Se han generado {$totalSales} ventas POS y {$totalOrders} órdenes web.\n";

$ordersWithShippingCost = Order::where('shipping_cost', '>', 0)->count();
echo "[INFO] Órdenes web con costo de flete asumido: {$ordersWithShippingCost}.\n";

// 5. Validar Costos Promedio y Precios Sugeridos
echo "\n5. Validando Precios Sugeridos...\n";
$product = Product::has('stockBalances')->first();
if ($product) {
    echo "[INFO] Producto de prueba: {$product->name}\n";
    echo "       Costo Promedio de Ingreso: S/ " . number_format($product->average_entry_cost, 2) . "\n";
    echo "       Precio Sugerido (+60%): S/ " . number_format($product->recommended_price, 2) . "\n";
    echo "       Precio Actual: S/ " . number_format($product->price, 2) . "\n";
}

echo "\n=====================================\n";

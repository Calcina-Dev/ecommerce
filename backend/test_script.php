<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$purchaseOrderId = 7;
$order = \App\Models\PurchaseOrder::with('items')->find($purchaseOrderId);
if (!$order) { echo "Order not found\n"; exit; }

$receivedQuantities = \Illuminate\Support\Facades\DB::table('purchase_invoice_lines')
    ->join('purchase_invoices', 'purchase_invoice_lines.purchase_invoice_id', '=', 'purchase_invoices.id')
    ->where('purchase_invoices.purchase_order_id', $purchaseOrderId)
    ->where('purchase_invoices.status', 'VALID')
    ->groupBy('purchase_invoice_lines.product_id')
    ->select('purchase_invoice_lines.product_id', \Illuminate\Support\Facades\DB::raw('SUM(purchase_invoice_lines.quantity) as total_received'))
    ->pluck('total_received', 'product_id');

dump($receivedQuantities);

$isCompleted = true;
$hasAnyReceived = false;

foreach ($order->items as $item) {
    $received = $receivedQuantities->get($item->product_id, 0);
    dump("Item " . $item->product_id . " quantity: " . $item->quantity . " received: " . $received);
    if ($received > 0) {
        $hasAnyReceived = true;
    }
    if ($received < $item->quantity) {
        $isCompleted = false;
    }
}

echo "isCompleted: " . ($isCompleted ? 'true' : 'false') . "\n";
echo "hasAnyReceived: " . ($hasAnyReceived ? 'true' : 'false') . "\n";

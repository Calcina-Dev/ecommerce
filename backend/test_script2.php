<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$obs = new \App\Observers\PurchaseInvoiceObserver();
$m = new ReflectionMethod($obs, 'updatePurchaseOrderStatus');
$m->setAccessible(true);
$m->invoke($obs, 7);

$order = \App\Models\PurchaseOrder::find(7);
echo "Status is: " . $order->status . "\n";

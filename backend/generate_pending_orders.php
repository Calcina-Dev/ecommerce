<?php

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Str;

$products = Product::inRandomOrder()->take(5)->get();
$user = User::first() ?? User::factory()->create();

for ($i = 0; $i < 5; $i++) {
    $order = Order::create([
        'user_id' => $user->id,
        'order_number' => 'WEB-' . strtoupper(Str::random(8)),
        'status' => 'pending',
        'total_amount' => 0,
        'shipping_address' => 'Av. Falsa 123',
        'shipping_city' => 'Lima',
        'shipping_name' => 'Cliente de Prueba ' . $i,
        'shipping_email' => 'cliente'.$i.'@prueba.com',
        'shipping_phone' => '99988877'.$i,
        'payment_method' => 'Credit Card',
        'payment_status' => 'paid',
    ]);

    $total = 0;
    foreach ($products->random(rand(1, 3)) as $product) {
        $qty = rand(1, 3);
        $order->items()->create([
            'product_id' => $product->id,
            'product_name' => $product->name,
            'quantity' => $qty,
            'price' => $product->price,
            'subtotal' => $qty * $product->price,
        ]);
        $total += $qty * $product->price;
    }

    $order->update(['total_amount' => $total]);
}

echo "5 ordenes pendientes generadas.\n";

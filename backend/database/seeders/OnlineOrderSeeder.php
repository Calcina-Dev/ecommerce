<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use App\Models\Coupon;
use Carbon\Carbon;
use Illuminate\Support\Str;

class OnlineOrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $products = Product::where('is_active', true)->whereHas('stockBalances', function($q) {
            $q->where('on_hand', '>', 0);
        })->get();

        if ($products->isEmpty()) {
            $this->command->warn('No hay productos con stock para crear órdenes online.');
            return;
        }

        $users = User::where('role', '!=', 'admin')->get();
        if ($users->isEmpty()) {
            $users = collect([User::first()]);
        }

        $coupons = Coupon::where('is_active', true)->get();
        $paymentMethods = ['mercadopago', 'paypal', 'stripe'];
        
        // Distribution of target statuses:
        // Mostly delivered/shipped, some processing, few pending_payment
        $targetStatuses = array_merge(
            array_fill(0, 20, 'delivered'),
            array_fill(0, 10, 'shipped'),
            array_fill(0, 5, 'processing'),
            array_fill(0, 5, 'pending_payment')
        );

        // Array fijo de ubigeos para evitar leer el JSON de 2MB en memoria y causar OOM
        $ubigeos = [
            ['department' => 'Lima', 'province' => 'Lima', 'district' => 'Miraflores', 'ubigeo' => '150122'],
            ['department' => 'Lima', 'province' => 'Lima', 'district' => 'San Isidro', 'ubigeo' => '150131'],
            ['department' => 'Arequipa', 'province' => 'Arequipa', 'district' => 'Arequipa', 'ubigeo' => '040101'],
            ['department' => 'Cusco', 'province' => 'Cusco', 'district' => 'Wanchaq', 'ubigeo' => '080108'],
            ['department' => 'Piura', 'province' => 'Piura', 'district' => 'Piura', 'ubigeo' => '200101'],
        ];

        for ($i = 0; $i < 40; $i++) {
            $user = $users->random();
            $orderDate = Carbon::now()->subDays(rand(0, 30))->subHours(rand(0, 24));
            $targetStatus = $targetStatuses[$i];
            
            // Si es pending_payment, el payment_status es pending. De lo contrario, paid.
            $paymentStatus = $targetStatus === 'pending_payment' ? 'pending' : 'paid';

            $paymentMethod = $paymentMethods[array_rand($paymentMethods)];
            $cardBrand = null;
            $cardBin = null;
            $cardLastDigits = null;
            $cardCountry = null;
            $isForeignCard = false;

            // Force some processing orders to be foreign izipay payments
            if ($targetStatus === 'processing' && rand(1, 100) <= 50) {
                $paymentMethod = 'izipay';
                $cardBrand = ['VISA', 'MASTERCARD'][array_rand(['VISA', 'MASTERCARD'])];
                $cardBin = str_pad(rand(400000, 599999), 6, '0', STR_PAD_LEFT);
                $cardLastDigits = str_pad(rand(1000, 9999), 4, '0', STR_PAD_LEFT);
                $cardCountry = ['US', 'FR', 'GB', 'AR'][array_rand(['US', 'FR', 'GB', 'AR'])];
                $isForeignCard = true;
            } elseif ($paymentStatus === 'paid') {
                $cardBrand = ['visa', 'mastercard', 'amex'][array_rand(['visa', 'mastercard', 'amex'])];
                $cardBin = rand(400000, 599999);
                $cardLastDigits = str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT);
                $cardCountry = rand(1, 100) > 10 ? 'PE' : 'US';
                $isForeignCard = $cardCountry !== 'PE';
            }

            // Seleccionar ubigeo al azar
            $ubigeoData = null;
            if (!empty($ubigeos)) {
                $ubigeoData = $ubigeos[array_rand($ubigeos)];
            }

            $order = Order::create([
                'user_id' => $user->id,
                'order_number' => 'WEB-' . strtoupper(Str::random(8)),
                'status' => 'pending', // Starts pending to allow observers to work if we jump to shipped
                'total_amount' => 0,
                'total_cost' => 0,
                'shipping_cost' => 10.00,
                'shipping_name' => $user->name,
                'shipping_email' => $user->email,
                'shipping_phone' => '9' . rand(10000000, 99999999),
                'shipping_address' => 'Av. Falsa ' . rand(100, 999),
                'shipping_city' => $ubigeoData ? $ubigeoData['department'] : 'Lima',
                'shipping_department' => $ubigeoData ? $ubigeoData['department'] : 'Lima',
                'shipping_province' => $ubigeoData ? $ubigeoData['province'] : 'Lima',
                'shipping_district' => $ubigeoData ? $ubigeoData['district'] : 'Lima',
                'shipping_ubigeo' => $ubigeoData ? $ubigeoData['ubigeo'] : '150101',
                'shipping_reference' => 'Cerca al parque',
                'payment_method' => $paymentMethod,
                'payment_status' => $paymentStatus,
                'card_brand' => $cardBrand,
                'card_bin' => $cardBin,
                'card_last_digits' => $cardLastDigits,
                'card_country' => $cardCountry,
                'is_foreign_card' => $isForeignCard,
                'created_at' => $orderDate,
                'updated_at' => $orderDate,
            ]);

            $numItems = rand(1, 4);
            $orderTotal = 0;

            $selectedProducts = $products->shuffle()->take($numItems);

            foreach ($selectedProducts as $product) {
                $available = $product->stockBalances()->sum('on_hand');
                if ($available <= 0) continue;
                $qty = rand(1, min(3, $available));
                $subtotal = $product->price * $qty;

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'price' => $product->price,
                    'quantity' => $qty,
                    'subtotal' => $subtotal,
                    'unit_cost' => 0, 
                ]);

                $orderTotal += $subtotal;
            }

            $discountAmount = 0;
            $couponId = null;

            // 30% chance of applying a coupon
            if (rand(1, 100) <= 30 && $coupons->isNotEmpty()) {
                $coupon = $coupons->random();
                $couponId = $coupon->id;
                
                if ($coupon->type === 'percentage') {
                    $discountAmount = ($orderTotal * $coupon->value) / 100;
                } else {
                    $discountAmount = $coupon->value;
                }
                
                if ($discountAmount > $orderTotal) {
                    $discountAmount = $orderTotal;
                }
                
                $coupon->increment('times_used');
            }

            $finalTotal = $orderTotal - $discountAmount;

            $order->updateQuietly([
                'total_amount' => $finalTotal,
                'coupon_id' => $couponId,
                'discount_amount' => $discountAmount,
            ]);

            // Now trigger the status change so Observer handles inventory if shipped/delivered
            if ($targetStatus !== 'pending') {
                if (in_array($targetStatus, ['shipped', 'delivered'])) {
                    $order->shipping_method_id = rand(1, 3); // Assuming 3 methods exist
                    $order->tracking_code = 'TRACK' . rand(10000, 99999);
                    $order->shipping_cost = rand(10, 50); // Cost for the company
                }
                
                $order->status = $targetStatus;
                $order->save();

                // Simulamos log de pago en la pasarela si el estado de pago es 'paid'
                if ($order->payment_status === 'paid' && in_array($order->payment_method, ['izipay', 'mercadopago'])) {
                    $brand = ['VISA', 'MASTERCARD', 'AMEX'][array_rand(['VISA', 'MASTERCARD', 'AMEX'])];
                    $lastDigits = rand(1000, 9999);
                    $txId = 'TX-' . strtoupper(Str::random(10));
                    \App\Models\OrderNote::create([
                        'order_id' => $order->id,
                        'user_id' => null,
                        'content' => "Pago completado por {$order->payment_method} (Simulado). Transacción: {$txId}. Tarjeta: {$brand} terminada en {$lastDigits}. País: PE.",
                        'type' => 'system',
                        'created_at' => $orderDate,
                        'updated_at' => $orderDate,
                    ]);
                }
                
                // Update dates for stock movements to reflect past dates
                \Illuminate\Support\Facades\DB::table('stock_movements')
                    ->where('reference_type', \App\Models\Order::class)
                    ->where('reference_id', $order->id)
                    ->update(['created_at' => $orderDate, 'updated_at' => $orderDate]);
            }
        }

        $this->command->info('40 órdenes online generadas exitosamente con variedad de estados, cupones y envíos.');
    }
}

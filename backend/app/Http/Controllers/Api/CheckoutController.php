<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Http;
use App\Models\Coupon;
use Carbon\Carbon;

class CheckoutController extends Controller
{
    public function validateCoupon(Request $request)
    {
        $request->validate([
            'coupon_code' => 'required|string',
            'total_amount' => 'required|numeric|min:0',
        ]);

        $coupon = Coupon::where('code', strtoupper($request->coupon_code))->first();

        if (!$coupon) {
            return response()->json(['valid' => false, 'message' => 'Cupón no encontrado.'], 404);
        }

        if (!$coupon->is_active) {
            return response()->json(['valid' => false, 'message' => 'El cupón no está activo.'], 400);
        }

        if ($coupon->valid_from && Carbon::now()->lt($coupon->valid_from)) {
            return response()->json(['valid' => false, 'message' => 'El cupón aún no es válido.'], 400);
        }

        if ($coupon->valid_until && Carbon::now()->gt($coupon->valid_until)) {
            return response()->json(['valid' => false, 'message' => 'El cupón ha expirado.'], 400);
        }

        if ($coupon->usage_limit !== null && $coupon->times_used >= $coupon->usage_limit) {
            return response()->json(['valid' => false, 'message' => 'El cupón ha alcanzado su límite de uso.'], 400);
        }

        $discount = 0;
        if ($coupon->type === 'percentage') {
            $discount = ($request->total_amount * $coupon->value) / 100;
        } elseif ($coupon->type === 'fixed') {
            $discount = $coupon->value;
        }

        // Evitar que el descuento sea mayor al total
        if ($discount > $request->total_amount) {
            $discount = $request->total_amount;
        }

        return response()->json([
            'valid' => true,
            'discount' => round($discount, 2),
            'coupon_id' => $coupon->id,
            'message' => 'Cupón aplicado correctamente.',
        ]);
    }

    public function checkout(Request $request)
    {
        $request->validate([
            'shipping_name' => 'required|string|max:255',
            'shipping_email' => 'required|email|max:255',
            'shipping_phone' => 'required|string|digits:9',
            'shipping_address' => 'required|string|max:255',
            'shipping_department' => 'required|string|max:255',
            'shipping_province' => 'required|string|max:255',
            'shipping_district' => 'required|string|max:255',
            'shipping_postal_code' => 'required|string|max:20',
            'items' => 'required|array|min:1',
            'items.*.id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'coupon_code' => 'nullable|string',
            'payment_method' => 'nullable|string|in:mercadopago,izipay',
        ]);

        try {
            DB::beginTransaction();

            $totalAmount = 0;
            $orderItemsData = [];

            // 1. Validar productos y calcular total real desde Base de Datos
            foreach ($request->items as $item) {
                $product = Product::findOrFail($item['id']);
                
                // Asegurar que hay stock (opcional en MVP, pero buena práctica)
                if ($product->stock < $item['quantity']) {
                    throw new \Exception("No hay stock suficiente para el producto: {$product->name}");
                }

                $subtotal = $product->price * $item['quantity'];
                $totalAmount += $subtotal;

                $orderItemsData[] = [
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'price' => $product->price, // Guardamos la foto del precio actual
                    'quantity' => $item['quantity'],
                    'subtotal' => $subtotal,
                ];
            }

            $discountAmount = 0;
            $couponId = null;
            $appliedCoupon = null;

            if ($request->coupon_code) {
                $appliedCoupon = Coupon::where('code', strtoupper($request->coupon_code))->where('is_active', true)->first();
                if ($appliedCoupon) {
                    if ($appliedCoupon->valid_from && Carbon::now()->lt($appliedCoupon->valid_from)) {
                        throw new \Exception("El cupón aún no es válido.");
                    }
                    if ($appliedCoupon->valid_until && Carbon::now()->gt($appliedCoupon->valid_until)) {
                        throw new \Exception("El cupón ha expirado.");
                    }
                    if ($appliedCoupon->usage_limit !== null && $appliedCoupon->times_used >= $appliedCoupon->usage_limit) {
                        throw new \Exception("El cupón ha alcanzado su límite de uso.");
                    }

                    if ($appliedCoupon->type === 'percentage') {
                        $discountAmount = ($totalAmount * $appliedCoupon->value) / 100;
                    } elseif ($appliedCoupon->type === 'fixed') {
                        $discountAmount = $appliedCoupon->value;
                    }
                    if ($discountAmount > $totalAmount) {
                        $discountAmount = $totalAmount;
                    }
                    $couponId = $appliedCoupon->id;
                    $totalAmount -= $discountAmount;
                }
            }

            // 2. Crear la Orden Principal
            $userId = auth('sanctum')->id();
            $user = auth('sanctum')->user();
            
            if ($user && empty($user->phone) && !empty($request->shipping_phone)) {
                $user->update(['phone' => $request->shipping_phone]);
            }

            $order = Order::create([
                'user_id' => $userId, // Nullable si es invitado
                'order_number' => 'ORD-' . strtoupper(Str::random(8)),
                'status' => 'pending_payment',
                'total_amount' => $totalAmount,
                'shipping_name' => $request->shipping_name,
                'shipping_email' => $request->shipping_email,
                'shipping_phone' => $request->shipping_phone,
                'shipping_address' => $request->shipping_address,
                'shipping_department' => $request->shipping_department,
                'shipping_province' => $request->shipping_province,
                'shipping_district' => $request->shipping_district,
                'shipping_postal_code' => $request->shipping_postal_code,
                'coupon_id' => $couponId,
                'discount_amount' => $discountAmount,
            ]);

            if ($appliedCoupon) {
                $appliedCoupon->increment('times_used');
            }

            // 3. Insertar Detalle de la Orden
            foreach ($orderItemsData as $itemData) {
                $order->items()->create($itemData);
            }

            DB::commit();

            // 4. Enviar notificación a los administradores
            $admins = \App\Models\User::whereIn('role', ['admin', 'employee'])->get();
            if ($admins->count() > 0) {
                \Filament\Notifications\Notification::make()
                    ->title('¡Nueva Venta Web!')
                    ->body("Orden {$order->order_number} por S/ " . number_format($order->total_amount, 2))
                    ->icon('heroicon-o-shopping-bag')
                    ->success()
                    ->sendToDatabase($admins);
            }

            $paymentMethod = $request->payment_method ?? 'izipay';

            if ($paymentMethod === 'izipay') {
                $izipayService = new \App\Services\IzipayService();
                $formToken = $izipayService->createPaymentFormToken(
                    $order->total_amount,
                    $order->order_number,
                    $request->shipping_email,
                    $request->shipping_name
                );

                return response()->json([
                    'message' => 'Orden creada exitosamente',
                    'order_number' => $order->order_number,
                    'order_id' => $order->id,
                    'form_token' => $formToken,
                    'payment_method' => 'izipay',
                ], 201);
            }

            // Fallback a Mercado Pago
            $mpAccessToken = env('MERCADOPAGO_ACCESS_TOKEN', 'TEST-7590855325992440-060820-21a719c8f8c47a544c80302ed1918a22-140228811');
            
            $mpItems = array_map(function($item) {
                return [
                    'title' => $item['product_name'],
                    'quantity' => $item['quantity'],
                    'unit_price' => (float) $item['price'],
                    'currency_id' => 'PEN',
                ];
            }, $orderItemsData);

            $frontendUrl = env('NEXT_PUBLIC_FRONTEND_URL', 'http://localhost:3000');
            $preferenceData = [
                'items' => $mpItems,
                'external_reference' => $order->order_number,
                'payer' => [
                    'name' => $request->shipping_name,
                    'email' => $request->shipping_email,
                ],
                'back_urls' => [
                    'success' => "{$frontendUrl}/checkout/success",
                    'failure' => "{$frontendUrl}/checkout/success",
                    'pending' => "{$frontendUrl}/checkout/success",
                ],
                'auto_return' => 'approved',
            ];

            $response = Http::withToken($mpAccessToken)->post('https://api.mercadopago.com/checkout/preferences', $preferenceData);
            
            $initPoint = null;

            if ($response->failed()) {
                if (app()->environment('local') && $response->status() === 401) {
                    $initPoint = "http://localhost:3000/checkout/success?status=approved&external_reference=" . $order->order_number;
                } else {
                    throw new \Exception('Error al contactar a Mercado Pago: ' . $response->body());
                }
            } else {
                $preference = $response->json();
                $initPoint = $preference['init_point'] ?? null;
            }

            return response()->json([
                'message' => 'Orden creada exitosamente',
                'order_number' => $order->order_number,
                'order_id' => $order->id,
                'init_point' => $initPoint,
                'payment_method' => 'mercadopago',
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Error al procesar la orden: ' . $e->getMessage()], 422);
        }
    }

    public function verifyIzipay(Request $request, \App\Services\IzipayService $izipayService)
    {
        $postData = $request->all();

        $answer = json_decode($postData['kr-answer'] ?? '{}', true);
        $transactionUuid = $answer['transactions'][0]['uuid'] ?? null;

        if (!$transactionUuid) {
            return response()->json(['error' => 'No transaction UUID found'], 400);
        }

        // Fetch transaction securely from Izipay API
        $transaction = $izipayService->getTransaction($transactionUuid);

        if (!$transaction) {
            return response()->json(['error' => 'Transaction not found in Izipay'], 404);
        }

        $transactionStatus = $transaction['status'] ?? null;
        $orderId = $transaction['orderDetails']['orderId'] ?? null;
        $transactionAmount = $transaction['amount'] ?? 0;

        $order = Order::where('order_number', $orderId)->first();

        if (!$order) {
            return response()->json(['error' => 'Order not found'], 404);
        }

        // Idempotency: skip if already paid
        if ($order->payment_status === 'paid') {
            return response()->json(['status' => 'OK']);
        }

        if ($transactionStatus === 'PAID' || $transactionStatus === 'AUTHORIZED') {
            // Verify amount (Izipay returns amount in cents)
            $expectedAmountInCents = (int) round($order->total_amount * 100);
            if ((int)$transactionAmount !== $expectedAmountInCents) {
                \Illuminate\Support\Facades\Log::warning("Izipay Amount mismatch on order {$orderId}. Expected: {$expectedAmountInCents}, Received: {$transactionAmount}");
                return response()->json(['error' => 'Amount mismatch'], 400);
            }

            // Extract card details
            $cardDetails = $transaction['transactionDetails']['cardDetails'] ?? [];
            $cardBrand = $cardDetails['brand'] ?? $cardDetails['effectiveBrand'] ?? $cardDetails['scheme'] ?? 'Desconocida';
            $pan = $cardDetails['pan'] ?? null;
            $cardBin = $pan ? substr($pan, 0, 6) : null;
            $cardLastDigits = $pan ? substr($pan, -4) : null;
            $cardCountry = $cardDetails['country'] ?? null;
            $isForeignCard = $cardCountry && strtoupper($cardCountry) !== 'PE';

            $order->update([
                'status' => 'processing',
                'payment_status' => 'paid',
                'payment_method' => 'izipay',
                'card_brand' => $cardBrand,
                'card_bin' => $cardBin,
                'card_last_digits' => $cardLastDigits,
                'card_country' => $cardCountry,
                'is_foreign_card' => $isForeignCard,
                'gateway_transaction_id' => $transactionUuid,
            ]);

            \App\Models\OrderNote::create([
                'order_id' => $order->id,
                'content' => "Pago completado por Izipay (Frontend). Transacción: {$transactionUuid}. Tarjeta: {$cardBrand} terminada en {$cardLastDigits}. País: {$cardCountry}.",
                'type' => 'system',
            ]);
            $admins = \App\Models\User::whereIn('role', ['admin', 'employee'])->get();
            if ($admins->count() > 0) {
                \Filament\Notifications\Notification::make()
                    ->title('¡Pago Recibido (Izipay)!')
                    ->body("La orden {$order->order_number} ha sido pagada.")
                    ->icon('heroicon-o-check-circle')
                    ->success()
                    ->sendToDatabase($admins);
            }
        } elseif ($transactionStatus === 'CANCELED' || $transactionStatus === 'UNPAID' || $transactionStatus === 'REFUSED') {
            $order->update([
                'status' => 'cancelled',
                'payment_status' => 'failed',
            ]);
            return response()->json(['error' => 'Payment refused', 'status' => $transactionStatus], 400);
        } else {
            // If the status is running, created, or anything else not finalized, we return 400
            // so the frontend knows the payment isn't fully approved yet and won't redirect to success.
            return response()->json(['error' => 'Payment not finalized', 'status' => $transactionStatus], 400);
        }

        return response()->json(['status' => 'OK']);
    }
}

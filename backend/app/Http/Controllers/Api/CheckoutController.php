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
            'shipping_phone' => 'required|string|max:20',
            'shipping_address' => 'required|string|max:255',
            'shipping_city' => 'required|string|max:255',
            'items' => 'required|array|min:1',
            'items.*.id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'coupon_code' => 'nullable|string',
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
                'shipping_city' => $request->shipping_city,
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

            // 5. Crear Preferencia en Mercado Pago
            $mpAccessToken = env('MERCADOPAGO_ACCESS_TOKEN', 'TEST-7590855325992440-060820-21a719c8f8c47a544c80302ed1918a22-140228811');
            
            $mpItems = array_map(function($item) {
                return [
                    'title' => $item['product_name'],
                    'quantity' => $item['quantity'],
                    'unit_price' => (float) $item['price'],
                    'currency_id' => 'PEN',
                ];
            }, $orderItemsData);

            $preferenceData = [
                'items' => $mpItems,
                'external_reference' => $order->order_number,
                'payer' => [
                    'name' => $request->shipping_name,
                    'email' => $request->shipping_email,
                ],
                'back_urls' => [
                    'success' => "http://localhost:3000/checkout/success",
                    'failure' => "http://localhost:3000/checkout/success",
                    'pending' => "http://localhost:3000/checkout/success",
                ],
                'auto_return' => 'approved',
            ];

            $response = Http::withToken($mpAccessToken)->post('https://api.mercadopago.com/checkout/preferences', $preferenceData);
            
            $initPoint = null;

            if ($response->failed()) {
                // Si el token es inválido y estamos en local, simulamos la redirección para no bloquear el desarrollo
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
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Error al procesar la orden: ' . $e->getMessage()], 422);
        }
    }
}

<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Order;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        // Vincular automáticamente órdenes pasadas hechas como invitado (user_id NULL) con el correo del usuario
        if ($user && !empty($user->email)) {
            Order::whereNull('user_id')
                ->where('shipping_email', $user->email)
                ->update(['user_id' => $user->id]);
        }

        $orders = Order::with(['items.product', 'coupon', 'shippingMethod'])
            ->where(function ($query) use ($user) {
                $query->where('user_id', $user->id)
                      ->orWhere('shipping_email', $user->email);
            })
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($orders);
    }

    public function tracking($order_number)
    {
        // Sanitizar: trim, uppercase, y remover caracteres no permitidos
        $sanitized = strtoupper(trim($order_number));
        
        $order = Order::with(['items.product', 'notes' => function ($query) {
            $query->where('type', 'customer')->orderBy('created_at', 'desc');
        }, 'shippingMethod'])
        ->whereRaw('UPPER(order_number) = ?', [$sanitized])
        ->first();

        if (!$order) {
            return response()->json(['message' => 'Order not found'], 404);
        }

        // Filtrar PII: no exponer dirección exacta, teléfono ni DNI en endpoint público
        $safeOrder = $order->toArray();
        unset(
            $safeOrder['shipping_phone'],
            $safeOrder['shipping_address'],
            $safeOrder['shipping_postal_code'],
            $safeOrder['dni'],
            $safeOrder['ruc'],
            $safeOrder['user_id'],
            $safeOrder['total_cost'],
            $safeOrder['document_series'],
            $safeOrder['document_number']
        );
        $safeOrder['shipping_location'] = trim(($order->shipping_district ?? '') . ', ' . ($order->shipping_province ?? '') . ', ' . ($order->shipping_department ?? ''), ', ');

        return response()->json($safeOrder);
    }
}

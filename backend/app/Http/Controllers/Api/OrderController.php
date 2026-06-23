<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Order;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $orders = Order::with(['items.product', 'coupon', 'shippingMethod'])
            ->where('user_id', $request->user()->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($orders);
    }

    public function tracking($order_number)
    {
        $order = Order::with(['items.product', 'notes' => function ($query) {
            $query->where('type', 'customer')->orderBy('created_at', 'desc');
        }, 'shippingMethod'])
        ->where('order_number', $order_number)
        ->first();

        if (!$order) {
            return response()->json(['message' => 'Order not found'], 404);
        }

        return response()->json($order);
    }
}

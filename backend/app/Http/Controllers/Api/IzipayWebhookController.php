<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\IzipayService;
use App\Models\Order;

class IzipayWebhookController extends Controller
{
    public function handleWebhook(Request $request, IzipayService $izipayService)
    {
        $postData = $request->all();

        // 1. Validate the webhook signature
        if (!$izipayService->checkHash($postData)) {
            return response()->json(['error' => 'Invalid signature'], 400);
        }

        // 2. Decode the answer
        $answer = json_decode($postData['kr-answer'], true);
        if (!$answer) {
            return response()->json(['error' => 'Invalid payload'], 400);
        }

        $orderStatus = $answer['orderStatus'];
        $orderId = $answer['orderDetails']['orderId'];

        $order = Order::where('order_number', $orderId)->first();

        if (!$order) {
            return response()->json(['error' => 'Order not found'], 404);
        }

        // 3. Update order status based on Izipay status
        if ($orderStatus === 'PAID') {
            $order->update([
                'status' => 'processing',
                'payment_status' => 'paid',
                'payment_method' => 'izipay',
            ]);
            // Here you could dispatch an event to send an email, etc.
        } elseif ($orderStatus === 'CANCELED' || $orderStatus === 'UNPAID') {
            $order->update([
                'status' => 'cancelled',
                'payment_status' => 'failed',
            ]);
        }

        // Return 200 OK so Izipay knows we received it
        return response()->json(['status' => 'OK']);
    }
}

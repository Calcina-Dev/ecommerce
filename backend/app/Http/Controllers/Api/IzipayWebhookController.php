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

        $orderStatus = $answer['orderStatus'] ?? null;
        $orderId = $answer['orderDetails']['orderId'] ?? null;
        $orderTotalAmount = $answer['orderDetails']['orderTotalAmount'] ?? 0;

        $order = Order::where('order_number', $orderId)->first();

        if (!$order) {
            return response()->json(['error' => 'Order not found'], 404);
        }

        // Idempotency check
        if ($order->payment_status === 'paid') {
            return response()->json(['status' => 'OK']);
        }

        // 3. Update order status based on Izipay status
        if ($orderStatus === 'PAID') {
            // Verify amount (Izipay returns amount in cents)
            $expectedAmountInCents = (int) round($order->total_amount * 100);
            if ((int)$orderTotalAmount !== $expectedAmountInCents) {
                \Illuminate\Support\Facades\Log::warning("Webhook Izipay Amount mismatch on order {$orderId}. Expected: {$expectedAmountInCents}, Received: {$orderTotalAmount}");
                return response()->json(['error' => 'Amount mismatch'], 400);
            }

            // Extract card details
            $transaction = $answer['transactions'][0] ?? [];
            $cardDetails = $transaction['transactionDetails']['cardDetails'] ?? [];
            $cardBrand = $cardDetails['brand'] ?? $cardDetails['effectiveBrand'] ?? $cardDetails['scheme'] ?? 'Desconocida';
            $pan = $cardDetails['pan'] ?? null;
            $cardBin = $pan ? substr($pan, 0, 6) : null;
            $cardLastDigits = $pan ? substr($pan, -4) : null;
            $cardCountry = $cardDetails['country'] ?? null;
            $isForeignCard = $cardCountry && strtoupper($cardCountry) !== 'PE';
            $transactionUuid = $transaction['uuid'] ?? null;

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
            
            $admins = \App\Models\User::whereIn('role', ['admin', 'employee'])->get();
            if ($admins->count() > 0) {
                \Filament\Notifications\Notification::make()
                    ->title('¡Pago Recibido (Izipay)!')
                    ->body("La orden {$order->order_number} ha sido pagada.")
                    ->icon('heroicon-o-check-circle')
                    ->success()
                    ->sendToDatabase($admins);
            }
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

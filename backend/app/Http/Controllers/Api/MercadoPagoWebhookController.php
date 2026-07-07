<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class MercadoPagoWebhookController extends Controller
{
    public function handleWebhook(Request $request)
    {
        Log::info('Mercado Pago Webhook Received', $request->all());

        $topic = $request->input('type') ?? $request->input('topic');
        $id = $request->input('data.id') ?? $request->input('id');

        if ($topic === 'payment' && $id) {
            try {
                $mpAccessToken = env('MERCADOPAGO_ACCESS_TOKEN');

                if (empty($mpAccessToken)) {
                    Log::error('MERCADOPAGO_ACCESS_TOKEN no está configurado en el entorno.');
                    return response()->json(['success' => true]);
                }

                $response = Http::withToken($mpAccessToken)->get("https://api.mercadopago.com/v1/payments/{$id}");

                if ($response->successful()) {
                    $paymentInfo = $response->json();
                    $orderNumber = $paymentInfo['external_reference'] ?? null;
                    $status = $paymentInfo['status'] ?? null;

                    if ($orderNumber) {
                        $order = Order::where('order_number', $orderNumber)->first();
                        if ($order) {
                            // Idempotencia: si ya está pagada, no reprocesar
                            if ($order->payment_status === 'paid') {
                                Log::info("Webhook MP: Orden {$orderNumber} ya estaba pagada. Ignorando duplicado.");
                                return response()->json(['success' => true]);
                            }

                            if ($status === 'approved') {
                                // Validar monto pagado vs total de la orden
                                $paidAmount = (float) ($paymentInfo['transaction_amount'] ?? 0);
                                if (round($paidAmount, 2) < round((float) $order->total_amount, 2)) {
                                    Log::warning("Mercado Pago monto insuficiente en orden {$orderNumber}. Esperado: {$order->total_amount}, Recibido: {$paidAmount}");
                                    return response()->json(['error' => 'Amount mismatch'], 400);
                                }

                                $order->update([
                                    'status' => 'processing',
                                    'payment_status' => 'paid',
                                    'payment_method' => 'mercadopago',
                                    'gateway_transaction_id' => (string) $id,
                                ]);

                                Log::info("Webhook MP: Orden {$orderNumber} marcada como pagada. Monto: {$paidAmount}");
                            } elseif (in_array($status, ['rejected', 'cancelled', 'refunded', 'charged_back'])) {
                                $order->update([
                                    'status' => 'cancelled',
                                    'payment_status' => 'failed',
                                ]);
                                Log::info("Webhook MP: Orden {$orderNumber} marcada como fallida. Status MP: {$status}");
                            }
                        } else {
                            Log::warning("Webhook MP: Orden no encontrada para external_reference: {$orderNumber}");
                        }
                    }
                } else {
                    Log::error("Webhook MP: Error al consultar pago {$id}. HTTP Status: {$response->status()}");
                }
            } catch (\Exception $e) {
                Log::error('Error procesando webhook de MP: ' . $e->getMessage());
            }
        }

        return response()->json(['success' => true]);
    }
}

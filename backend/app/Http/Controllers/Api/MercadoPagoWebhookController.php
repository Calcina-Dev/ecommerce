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

        // MercadoPago envía `type` y `data.id` o a veces `action` y `data.id`
        $topic = $request->input('type') ?? $request->input('topic');
        $id = $request->input('data.id') ?? $request->input('id');

        if ($topic === 'payment' && $id) {
            try {
                $mpAccessToken = env('MERCADOPAGO_ACCESS_TOKEN', 'TEST-7590855325992440-060820-21a719c8f8c47a544c80302ed1918a22-140228811');
                
                // Consultar a la API de MP para obtener el detalle del pago y asegurar que sea real
                $response = Http::withToken($mpAccessToken)->get("https://api.mercadopago.com/v1/payments/{$id}");
                
                if ($response->successful()) {
                    $paymentInfo = $response->json();
                    
                    $orderNumber = $paymentInfo['external_reference'] ?? null;
                    $status = $paymentInfo['status'] ?? null;

                    if ($orderNumber) {
                        $order = Order::where('order_number', $orderNumber)->first();
                        if ($order) {
                            if ($status === 'approved') {
                                $order->update([
                                    'status' => 'processing',
                                    'payment_status' => 'paid',
                                    'payment_method' => 'mercadopago',
                                ]);
                            } elseif ($status === 'rejected' || $status === 'cancelled') {
                                $order->update([
                                    'status' => 'cancelled',
                                    'payment_status' => 'failed',
                                ]);
                            }
                        }
                    }
                }
            } catch (\Exception $e) {
                Log::error('Error procesando webhook de MP: ' . $e->getMessage());
            }
        }

        return response()->json(['success' => true]);
    }
}

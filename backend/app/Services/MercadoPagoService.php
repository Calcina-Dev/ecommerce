<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class MercadoPagoService
{
    protected $accessToken;

    public function __construct()
    {
        $this->accessToken = env('MERCADOPAGO_ACCESS_TOKEN', 'TEST-7590855325992440-060820-21a719c8f8c47a544c80302ed1918a22-140228811');
    }

    /**
     * Refunds a payment using the Mercado Pago API.
     */
    public function refundPayment(string $paymentId)
    {
        $idempotencyKey = uniqid('refund_', true);

        $response = Http::withToken($this->accessToken)
            ->withHeaders([
                'X-Idempotency-Key' => $idempotencyKey
            ])
            ->post("https://api.mercadopago.com/v1/payments/{$paymentId}/refunds");

        if ($response->successful()) {
            return true;
        }

        $error = $response->json();
        throw new \Exception('Mercado Pago Refund Error: ' . ($error['message'] ?? $response->body()));
    }
}

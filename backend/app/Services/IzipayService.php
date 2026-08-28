<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class IzipayService
{
    protected $clientId;
    protected $clientSecret;
    protected $endpoint;
    protected $hmacKey;

    public function __construct()
    {
        $this->clientId = config('services.izipay.client_id');
        $this->clientSecret = config('services.izipay.client_secret');
        $this->endpoint = config('services.izipay.endpoint');
        $this->hmacKey = config('services.izipay.hmac_key');
    }

    /**
     * Generates a formToken for the given order amount and order ID.
     */
    public function createPaymentFormToken(float $amount, string $orderId, string $customerEmail, string $customerName)
    {
        // Izipay expects the amount in the smallest currency unit (e.g. cents)
        // Since it's PEN (Soles), multiply by 100
        $amountInCents = (int) round($amount * 100);

        $response = Http::withBasicAuth($this->clientId, $this->clientSecret)
            ->post("{$this->endpoint}/api-payment/V4/Charge/CreatePayment", [
                'amount' => $amountInCents,
                'currency' => 'PEN',
                'orderId' => $orderId,
                'customer' => [
                    'email' => $customerEmail,
                    'billingDetails' => [
                        'firstName' => $customerName,
                    ]
                ],
            ]);

        if ($response->successful()) {
            $data = $response->json();
            if ($data['status'] === 'SUCCESS') {
                return $data['answer']['formToken'];
            }
            throw new \Exception('Izipay Error: ' . json_encode($data));
        }

        throw new \Exception('Failed to connect to Izipay API: ' . $response->body());
    }

    /**
     * Validates the IPN webhook signature using HMAC-SHA-256
     */
    public function checkHash(array $postData): bool
    {
        if (!isset($postData['kr-hash']) || !isset($postData['kr-answer'])) {
            return false;
        }

        $answer = $postData['kr-answer'];
        $hash = $postData['kr-hash'];

        $hashKeyField = $postData['kr-hash-key'] ?? null;
        $keysToTry = [];

        if ($hashKeyField === 'sha256_hmac') {
            $keysToTry[] = $this->hmacKey;
        } elseif ($hashKeyField === 'password') {
            $keysToTry[] = $this->clientSecret;
        } else {
            // En el retorno del navegador (JS) suele usarse la contraseña, en el IPN la hmacKey
            $keysToTry[] = $this->clientSecret;
            $keysToTry[] = $this->hmacKey;
        }

        foreach ($keysToTry as $key) {
            $calculatedHash = hash_hmac('sha256', $answer, $key);
            if (hash_equals($calculatedHash, $hash)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Retrieves transaction details from Izipay API using the transaction UUID.
     */
    public function getTransaction(string $transactionUuid)
    {
        $response = Http::withBasicAuth($this->clientId, $this->clientSecret)
            ->post("{$this->endpoint}/api-payment/V4/Transaction/Get", [
                'uuid' => $transactionUuid
            ]);

        if ($response->successful()) {
            $data = $response->json();
            if ($data['status'] === 'SUCCESS') {
                return $data['answer'];
            }
        }
        
        return null;
    }

    /**
     * Cancels or refunds a transaction using the transaction UUID.
     */
    public function refundTransaction(string $transactionUuid)
    {
        $response = Http::withBasicAuth($this->clientId, $this->clientSecret)
            ->post("{$this->endpoint}/api-payment/V4/Transaction/CancelOrRefund", [
                'uuid' => $transactionUuid
            ]);

        if ($response->successful()) {
            $data = $response->json();
            if ($data['status'] === 'SUCCESS') {
                return true;
            }
            throw new \Exception('Izipay Refund Error: ' . ($data['answer']['errorMessage'] ?? json_encode($data)));
        }

        throw new \Exception('Failed to connect to Izipay API for refund: ' . $response->body());
    }
}

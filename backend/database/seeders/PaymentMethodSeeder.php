<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Models\PaymentMethod;

class PaymentMethodSeeder extends Seeder
{
    public function run(): void
    {
        $methods = [
            // POS
            ['name' => 'Efectivo', 'code' => 'CASH', 'scope' => 'pos'],
            ['name' => 'Yape', 'code' => 'YAPE', 'scope' => 'pos'],
            ['name' => 'Plin', 'code' => 'PLIN', 'scope' => 'pos'],
            ['name' => 'Tarjeta Visa/Mastercard (POS)', 'code' => 'CARD', 'scope' => 'pos'],
            ['name' => 'Transferencia BCP', 'code' => 'TRANSFER_BCP', 'scope' => 'pos'],
            ['name' => 'Transferencia Interbank', 'code' => 'TRANSFER_IBK', 'scope' => 'pos'],
            
            // WEB
            ['name' => 'Mercado Pago', 'code' => 'mercadopago', 'scope' => 'web'],
            ['name' => 'Izipay', 'code' => 'izipay', 'scope' => 'web'],
            ['name' => 'PayPal', 'code' => 'paypal', 'scope' => 'web'],
            ['name' => 'Stripe', 'code' => 'stripe', 'scope' => 'web'],
        ];

        foreach ($methods as $method) {
            PaymentMethod::firstOrCreate(['code' => $method['code']], $method);
        }
    }
}

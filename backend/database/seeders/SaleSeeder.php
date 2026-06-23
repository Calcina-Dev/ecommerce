<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Sale;
use App\Models\User;
use App\Models\PaymentMethod;
use App\Models\CashRegister;
use App\Models\CashSession;
use App\Models\StockBalance;
use Carbon\Carbon;

class SaleSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Create Employees
        $employee1 = User::firstOrCreate(
            ['email' => 'empleado1@vitaminos.com'],
            ['name' => 'Ana Vendedora', 'dni' => '11111111', 'password' => bcrypt('password'), 'role' => 'employee']
        );
        $employee2 = User::firstOrCreate(
            ['email' => 'empleado2@vitaminos.com'],
            ['name' => 'Carlos Mostrador', 'dni' => '22222222', 'password' => bcrypt('password'), 'role' => 'employee']
        );
        $admin = User::first();

        $users = collect([$employee1, $employee2, $admin]);

        // 2. Create Cash Registers
        $register = CashRegister::firstOrCreate(['name' => 'Caja Principal']);

        $paymentMethods = PaymentMethod::all();
        if ($paymentMethods->isEmpty()) return;


        // 3. Generate data for the last 30 days
        for ($i = 29; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);

            // Each day, pick a random user to open the cash register
            $dailyUser = $users->random();

            // Open Session
            $openingBalance = rand(50, 200); // 50 to 200 soles in change
            $session = CashSession::create([
                'cash_register_id' => $register->id,
                'user_id' => $dailyUser->id,
                'opening_balance' => $openingBalance,
                'opened_at' => $date->copy()->setHour(rand(8, 9))->setMinute(rand(0, 59)),
                'status' => 'open',
            ]);

            $cashCollected = 0;

            // Generate 3 to 12 sales per day
            $salesCount = rand(3, 12);
            for ($j = 0; $j < $salesCount; $j++) {
                $stock = StockBalance::where('on_hand', '>', 0)->inRandomOrder()->first();
                if (!$stock) continue;

                $product = $stock->product;
                $qty = min(rand(1, 3), $stock->on_hand);
                if ($qty <= 0) continue;

                $price = $product->price ?? rand(20, 150);
                $subtotal = $qty * $price;
                $tax = round($subtotal * 0.18, 2);
                $total = $subtotal + $tax;

                $saleTime = $session->opened_at->copy()->addMinutes(rand(10, 480)); // Random time during the shift

                $sale = Sale::create([
                    'user_id' => $dailyUser->id,
                    'warehouse_id' => $stock->warehouse_id,
                    'document_type' => 'BOLETA',
                    'document_series' => 'B001',
                    'document_number' => str_pad(Sale::count() + 1, 6, '0', STR_PAD_LEFT),
                    'subtotal' => $subtotal,
                    'total_tax' => $tax,
                    'total_amount' => $total,
                    'status' => 'DRAFT',
                    'created_at' => $saleTime,
                    'updated_at' => $saleTime,
                ]);

                $sale->items()->create([
                    'product_id' => $product->id,
                    'quantity' => $qty,
                    'price' => $price,
                    'subtotal' => $subtotal,
                    'unit_cost' => 0,
                ]);

                $paymentMethod = $paymentMethods->random();
                $sale->payments()->create([
                    'payment_method_id' => $paymentMethod->id,
                    'amount' => $total,
                    'reference' => 'TEST-' . rand(1000, 9999),
                    'cash_session_id' => $session->id,
                ]);

                if (strtolower($paymentMethod->name) === 'efectivo') {
                    $cashCollected += $total;
                }

                $sale->update(['status' => 'CONFIRMED']);
                $sale->updateQuietly(['updated_at' => $saleTime, 'created_at' => $saleTime]);
            }

            // Close Session
            $closingTime = $date->copy()->setHour(rand(18, 20))->setMinute(rand(0, 59));
            $expectedCash = $openingBalance + $cashCollected;
            
            // Sometimes perfect, sometimes missing/extra change
            $actualCash = (rand(1, 10) > 8) ? $expectedCash + rand(-5, 5) : $expectedCash;

            $session->update([
                'closing_balance' => $actualCash,
                'closed_at' => $closingTime,
                'status' => 'closed'
            ]);
        }
    }
}

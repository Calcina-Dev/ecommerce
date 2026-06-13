<?php

namespace Database\Seeders;

use App\Models\Coupon;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class CouponSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $coupons = [
            [
                'code' => 'BIENVENIDA10',
                'type' => 'percentage',
                'value' => 10,
                'valid_from' => Carbon::now()->subDays(5),
                'valid_until' => Carbon::now()->addMonths(6),
                'usage_limit' => 100,
                'times_used' => 0,
                'is_active' => true,
            ],
            [
                'code' => 'OFERTA20',
                'type' => 'percentage',
                'value' => 20,
                'valid_from' => Carbon::now(),
                'valid_until' => Carbon::now()->addDays(30),
                'usage_limit' => 50,
                'times_used' => 0,
                'is_active' => true,
            ],
            [
                'code' => 'DESCUENTO50',
                'type' => 'fixed',
                'value' => 50.00,
                'valid_from' => Carbon::now(),
                'valid_until' => Carbon::now()->addMonths(1),
                'usage_limit' => 100,
                'times_used' => 0,
                'is_active' => true,
            ],
            [
                'code' => 'VIPCLIENTE',
                'type' => 'percentage',
                'value' => 30,
                'valid_from' => Carbon::now()->subMonths(1),
                'valid_until' => Carbon::now()->addYears(1),
                'usage_limit' => 500,
                'times_used' => 0,
                'is_active' => true,
            ],
        ];

        foreach ($coupons as $coupon) {
            Coupon::updateOrCreate(['code' => $coupon['code']], $coupon);
        }
    }
}

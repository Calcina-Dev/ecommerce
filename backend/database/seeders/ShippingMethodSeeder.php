<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ShippingMethod;

class ShippingMethodSeeder extends Seeder
{
    public function run(): void
    {
        $methods = [
            ['name' => 'Shalom', 'description' => 'Agencia Shalom a nivel nacional'],
            ['name' => 'Olva Courier', 'description' => 'Envío directo a domicilio'],
            ['name' => 'Motorizado Local', 'description' => 'Delivery express (Solo Lima)'],
        ];

        foreach ($methods as $method) {
            ShippingMethod::firstOrCreate(['name' => $method['name']], $method);
        }
    }
}

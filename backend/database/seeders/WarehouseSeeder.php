<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Models\Warehouse;

class WarehouseSeeder extends Seeder
{
    public function run(): void
    {
        Warehouse::firstOrCreate(['name' => 'Almacén Principal']);
        Warehouse::firstOrCreate(['name' => 'Tienda Centro']);
        Warehouse::firstOrCreate(['name' => 'Depósito Norte']);
    }
}

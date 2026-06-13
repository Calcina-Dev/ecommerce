<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Sale;
use Illuminate\Support\Facades\Hash;

class CustomerSaleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Crear algunos clientes de prueba
        $clientes = [];
        for ($i = 1; $i <= 3; $i++) {
            $clientes[] = User::firstOrCreate(
                ['email' => "cliente{$i}@vitamin.com"],
                [
                    'name' => "Cliente VIP {$i}",
                    'password' => Hash::make('password'),
                ]
            );
        }

        // Obtener las ventas recientes que no tienen cliente
        $ventas = Sale::whereNull('customer_id')->whereNull('customer_email')->get();

        foreach ($ventas as $index => $venta) {
            if ($index % 3 == 0) {
                // Asignar un cliente registrado
                $venta->updateQuietly([
                    'customer_id' => $clientes[array_rand($clientes)]->id,
                ]);
            } elseif ($index % 3 == 1) {
                // Asignar solo un correo (Anónimo)
                $venta->updateQuietly([
                    'customer_email' => "invitado_{$index}@correo.com",
                ]);
            }
            // El caso % 3 == 2 se queda totalmente anónimo
        }
    }
}

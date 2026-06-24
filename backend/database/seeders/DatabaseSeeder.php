<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Generar 10 clientes/usuarios aleatorios para pruebas
        User::factory(10)->create();

        $this->call([
            WarehouseSeeder::class,
            CatalogSeeder::class,
        ]);

        \App\Models\DocumentSeries::firstOrCreate(['document_type' => 'BOLETA'], ['series' => 'B001', 'current_number' => 0]);
        \App\Models\DocumentSeries::firstOrCreate(['document_type' => 'FACTURA'], ['series' => 'F001', 'current_number' => 0]);
        \App\Models\DocumentSeries::firstOrCreate(['document_type' => 'TICKET'], ['series' => 'T001', 'current_number' => 0]);
        \App\Models\DocumentSeries::firstOrCreate(['document_type' => 'ORDEN_COMPRA'], ['series' => 'OC01', 'current_number' => 0]);
        \App\Models\DocumentSeries::firstOrCreate(['document_type' => 'TRANSFERENCIA'], ['series' => 'TR01', 'current_number' => 0]);
        \App\Models\DocumentSeries::firstOrCreate(['document_type' => 'NOTA_INGRESO'], ['series' => 'NI01', 'current_number' => 0]);
        \App\Models\DocumentSeries::firstOrCreate(['document_type' => 'NOTA_SALIDA'], ['series' => 'NS01', 'current_number' => 0]);

        $this->call([
            PurchaseFlowSeeder::class,
            WarehouseTransferSeeder::class,
        ]);

        // SaleSeeder should be called after DocumentSeries are created.
        User::firstOrCreate(
            ['email' => 'test@example.com'],
            ['name' => 'Test User', 'dni' => '12345678', 'password' => bcrypt('password'), 'role' => 'admin']
        );

        User::firstOrCreate(
            ['email' => 'mrluisito5@gmail.com'],
            ['name' => 'CABRERA LUDEÑA JORGE LUIS CATALINO', 'dni' => '70235441', 'phone' => '952035525', 'password' => bcrypt('eldevil21'), 'role' => 'admin']
        );

        User::firstOrCreate(
            ['email' => 'Heidigusman4@gmail.com'],
            ['name' => 'GUSMAN QUINTANA HEIDI', 'dni' => '73759537', 'phone' => null, 'password' => bcrypt('eldevil21'), 'role' => 'admin']
        );

        $this->call([
            CouponSeeder::class,
            PaymentMethodSeeder::class,
            ShippingMethodSeeder::class,
            SaleSeeder::class,
            OnlineOrderSeeder::class,
            StorefrontPageSeeder::class,
            StoreSettingSeeder::class,
        ]);
    }
}

<?php

namespace Database\Seeders;

use App\Models\StoreSetting;
use Illuminate\Database\Seeder;

class StoreSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = StoreSetting::first();
        
        if (!$settings) {
            $settings = new StoreSetting();
        }

        $settings->store_name = 'Compra Saludable';
        $settings->whatsapp_number = '+51 928 586 883';
        $settings->contact_email = 'ventas@comprasaludable.com';
        $settings->store_address = 'Av. Tomás Valle 917, SMP – Lima, Perú';
        $settings->footer_theme = 'dark';
        
        // Define default footer columns if none exist
        if (!$settings->footer_columns) {
            $settings->footer_columns = [
                [
                    'title' => 'Empresa',
                    'links' => [
                        ['label' => 'Sobre Nosotros', 'url' => '/about-us'],
                        ['label' => 'Contáctanos', 'url' => '/contactanos'],
                        ['label' => 'Consejos de Salud', 'url' => '/consejos-de-salud'],
                        ['label' => 'Testimonios', 'url' => '/testimonios'],
                    ]
                ],
                [
                    'title' => 'Ayuda al cliente',
                    'links' => [
                        ['label' => 'Preguntas frecuentes', 'url' => '/preguntas-frecuentes'],
                        ['label' => 'Envíos y entregas', 'url' => '/envios-y-entregas'],
                        ['label' => 'Devoluciones y reembolsos', 'url' => '/devoluciones-y-reembolsos'],
                    ]
                ],
                [
                    'title' => 'Tienda & Legal',
                    'links' => [
                        ['label' => 'Términos y condiciones', 'url' => '/terms-and-conditions'],
                        ['label' => 'Política de privacidad', 'url' => '/politica-de-privacidad'],
                    ]
                ]
            ];
        }

        $settings->save();
    }
}

<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StorefrontPageSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('storefront_pages')->truncate();

        $pages = [
            [
                'slug' => 'home',
                'title' => 'Inicio',
                'is_active' => true,
                'blocks' => json_encode([
                    [
                        'type' => 'hero_modern',
                        'data' => [
                            'badge' => 'Salud y Bienestar',
                            'title_line_1' => 'Bienvenido a',
                            'title_line_2' => 'Compra Saludable',
                            'description' => 'Tu destino confiable para vitaminas, suplementos y productos naturales de la más alta calidad. Cuidamos de ti y de tu familia.',
                            'button_text' => 'Ver Catálogo',
                            'button_link' => '/productos',
                            'animate_rotation' => true,
                            'animate_glow' => true,
                        ]
                    ]
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'slug' => 'about-us',
                'title' => 'Sobre Nosotros',
                'is_active' => true,
                'blocks' => json_encode([
                    [
                        'type' => 'value_proposition',
                        'data' => [
                            'title' => 'Nuestra Misión y Visión',
                            'description' => "En Compra Saludable, nacimos con el propósito de democratizar el acceso a productos de salud, vitaminas y suplementos naturales de la más alta calidad en todo el Perú.\n\nCreemos firmemente que el bienestar integral debe estar al alcance de todos. Por ello, trabajamos directamente con laboratorios certificados y marcas reconocidas internacionalmente para asegurar que cada producto que llega a tus manos cumpla con los más estrictos estándares de calidad.\n\nNuestro equipo está conformado por apasionados de la nutrición y el bienestar, siempre dispuestos a brindarte la mejor asesoría personalizada.",
                        ]
                    ]
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'slug' => 'terms-and-conditions',
                'title' => 'Términos y Condiciones',
                'is_active' => true,
                'blocks' => json_encode([
                    [
                        'type' => 'value_proposition',
                        'data' => [
                            'title' => 'Términos y Condiciones de Uso',
                            'description' => "Bienvenido a Compra Saludable. Al acceder y utilizar nuestro sitio web, aceptas cumplir con los siguientes términos y condiciones:\n\n1. Uso del Sitio: El contenido de esta página es únicamente para fines informativos y de comercio electrónico. No sustituye el consejo médico profesional.\n2. Productos: Todos los suplementos y vitaminas deben consumirse según las indicaciones del fabricante o bajo la supervisión de un especialista en salud.\n3. Precios y Stock: Nos reservamos el derecho de modificar los precios y el inventario sin previo aviso. Las promociones están sujetas a disponibilidad.\n4. Propiedad Intelectual: Todo el material gráfico, diseño y contenido de esta web pertenece a Compra Saludable.",
                        ]
                    ]
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'slug' => 'contactanos',
                'title' => 'Contáctanos',
                'is_active' => true,
                'blocks' => json_encode([
                    [
                        'type' => 'value_proposition',
                        'data' => [
                            'title' => 'Estamos aquí para ayudarte',
                            'description' => "Tu opinión y bienestar son nuestra prioridad. Contamos con un equipo de atención al cliente listo para resolver todas tus dudas sobre nuestros productos, procesos de compra o despachos.\n\nPuedes comunicarte con nosotros a través de los siguientes canales:\n• WhatsApp / Teléfono: +51 928 586 883\n• Correo Electrónico: ventas@comprasaludable.com\n• Dirección: Av. Tomás Valle 917, San Martín de Porres – Lima, Perú\n\nNuestro horario de atención es de Lunes a Sábado de 9:00 am a 7:00 pm. ¡Esperamos tu mensaje!",
                        ]
                    ]
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'slug' => 'consejos-de-salud',
                'title' => 'Consejos de Salud',
                'is_active' => true,
                'blocks' => json_encode([
                    [
                        'type' => 'value_proposition',
                        'data' => [
                            'title' => 'Blog y Consejos de Bienestar',
                            'description' => "En Compra Saludable no solo vendemos productos, también queremos educar e inspirar a nuestra comunidad a llevar un estilo de vida más sano.\n\nEn esta sección, próximamente encontrarás artículos redactados por especialistas sobre nutrición, rutinas de ejercicios, beneficios de vitaminas específicas, y tips para fortalecer tu sistema inmunológico de manera natural.\n\n¡Mantente atento a nuestras próximas publicaciones!",
                        ]
                    ]
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'slug' => 'testimonios',
                'title' => 'Testimonios',
                'is_active' => true,
                'blocks' => json_encode([
                    [
                        'type' => 'value_proposition',
                        'data' => [
                            'title' => 'Historias Reales, Resultados Reales',
                            'description' => "\"Comencé a tomar el colágeno hidrolizado recomendado por el equipo de Compra Saludable y mis articulaciones se sienten mucho mejor. ¡El envío fue súper rápido!\" - María R., Lima\n\n\"Llevo meses comprando mi proteína y vitaminas aquí. Tienen los mejores precios y el servicio por WhatsApp es de primera.\" - Carlos M., Arequipa\n\n\"Excelente calidad y confianza total. Los productos siempre llegan sellados y con fecha de vencimiento lejana. 100% recomendados.\" - Laura S., Trujillo",
                        ]
                    ]
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'slug' => 'preguntas-frecuentes',
                'title' => 'Preguntas Frecuentes',
                'is_active' => true,
                'blocks' => json_encode([
                    [
                        'type' => 'value_proposition',
                        'data' => [
                            'title' => 'Resolvamos tus Dudas (FAQ)',
                            'description' => "¿Tienen tienda física?\nActualmente operamos de manera 100% online y hacemos envíos a todo el Perú desde nuestro centro de distribución.\n\n¿Los productos son originales?\nSí, trabajamos directamente con los representantes oficiales de cada marca. Todos nuestros productos cuentan con registro sanitario.\n\n¿Cómo sé qué suplemento es adecuado para mí?\nPuedes escribirnos al WhatsApp y uno de nuestros asesores te orientará basándose en tus objetivos y necesidades.",
                        ]
                    ]
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'slug' => 'envios-y-entregas',
                'title' => 'Envíos y Entregas',
                'is_active' => true,
                'blocks' => json_encode([
                    [
                        'type' => 'value_proposition',
                        'data' => [
                            'title' => 'Políticas de Envío y Despacho',
                            'description' => "Queremos que recibas tus productos lo más pronto posible para que empieces a cuidar de tu salud.\n\n• Envíos en Lima Metropolitana: Las entregas se realizan entre 24 a 48 horas hábiles tras la confirmación de la compra.\n• Envíos a Provincias: Trabajamos con agencias confiables (Shalom, Olva Courier). El tiempo estimado es de 2 a 5 días hábiles, dependiendo del destino.\n\n¡Recuerda que ofrecemos ENVÍO GRATIS en Lima para todas las compras superiores a S/ 100!",
                        ]
                    ]
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'slug' => 'metodos-de-pago',
                'title' => 'Métodos de Pago',
                'is_active' => true,
                'blocks' => json_encode([
                    [
                        'type' => 'value_proposition',
                        'data' => [
                            'title' => 'Transacciones 100% Seguras',
                            'description' => "Para tu comodidad y seguridad, en Compra Saludable ofrecemos múltiples opciones de pago, todas protegidas bajo estrictos protocolos de encriptación:\n\n• Tarjetas de Crédito y Débito: Procesamos pagos con Visa, Mastercard, American Express y Diners Club.\n• Billeteras Digitales: Aceptamos transferencias rápidas y seguras a través de Yape y Plin.\n• Transferencias Bancarias: Contamos con cuentas en BCP, BBVA e Interbank para tu mayor facilidad.",
                        ]
                    ]
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'slug' => 'libro-de-reclamaciones',
                'title' => 'Libro de Reclamaciones',
                'is_active' => true,
                'blocks' => json_encode([
                    [
                        'type' => 'value_proposition',
                        'data' => [
                            'title' => 'Libro de Reclamaciones Virtual',
                            'description' => "Conforme a lo establecido en el Código de Protección y Defensa del Consumidor (Ley N° 29571), ponemos a tu disposición nuestro Libro de Reclamaciones Virtual.\n\nSi has tenido algún inconveniente con nuestros servicios o productos, por favor envíanos un correo a reclamos@comprasaludable.com con el asunto 'Reclamo' e incluyendo tus datos personales, número de pedido y el detalle del incidente.\n\nNos comprometemos a revisar tu caso y brindarte una respuesta formal dentro del plazo legal establecido de 15 días hábiles.",
                        ]
                    ]
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'slug' => 'politica-de-privacidad',
                'title' => 'Política de Privacidad',
                'is_active' => true,
                'blocks' => json_encode([
                    [
                        'type' => 'value_proposition',
                        'data' => [
                            'title' => 'Protección de Datos Personales',
                            'description' => "En cumplimiento con la Ley de Protección de Datos Personales (Ley N° 29733), Compra Saludable garantiza la total confidencialidad y seguridad de tu información.\n\nLos datos que nos proporcionas (nombre, dirección, correo, teléfono) serán utilizados exclusivamente para:\n1. Procesar, enviar y facturar tus pedidos.\n2. Comunicarnos contigo ante cualquier eventualidad con tu envío.\n3. Enviarte promociones exclusivas (solo si has dado tu consentimiento).\n\nNunca venderemos ni compartiremos tu información con terceros no autorizados.",
                        ]
                    ]
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'slug' => 'politica-de-cookies',
                'title' => 'Política de Cookies',
                'is_active' => true,
                'blocks' => json_encode([
                    [
                        'type' => 'value_proposition',
                        'data' => [
                            'title' => 'Uso de Cookies en nuestra Web',
                            'description' => "Nuestra página web utiliza cookies propias y de terceros para brindarte una mejor experiencia de usuario.\n\nLas cookies nos permiten recordar los productos que agregaste a tu carrito, mantener tu sesión iniciada de manera segura y obtener estadísticas anónimas sobre qué secciones de la tienda son las más visitadas para poder mejorarlas.\n\nAl continuar navegando en nuestra página, aceptas el uso de dichas cookies. Puedes deshabilitarlas en cualquier momento desde la configuración de tu navegador, aunque esto podría afectar ciertas funcionalidades de la tienda.",
                        ]
                    ]
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'slug' => 'politica-de-devoluciones',
                'title' => 'Política de Devoluciones',
                'is_active' => true,
                'blocks' => json_encode([
                    [
                        'type' => 'value_proposition',
                        'data' => [
                            'title' => 'Garantía de Devolución',
                            'description' => "Para garantizar la salud e integridad de nuestros clientes, los productos naturales y suplementos tienen lineamientos estrictos de cambio y devolución:\n\n• Tienes hasta 7 días calendario para solicitar una devolución desde que recibes el producto.\n• El producto debe estar completamente sellado, en su envase original, sin haber sido abierto ni adulterado, y conservando sus etiquetas.\n• En caso de recibir un producto dañado de fábrica o un pedido incorrecto, nosotros cubriremos el costo total del envío de retorno y reposición.",
                        ]
                    ]
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ];

        DB::table('storefront_pages')->insert($pages);
    }
}

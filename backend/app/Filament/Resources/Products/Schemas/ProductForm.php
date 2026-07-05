<?php

namespace App\Filament\Resources\Products\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('category_id')
                    ->relationship('category', 'name')
                    ->label('Categoría Principal')
                    ->required(),
                Select::make('categories')
                    ->relationship('categories', 'name')
                    ->label('Categorías Múltiples / Etiquetas')
                    ->multiple()
                    ->preload(),
                Select::make('brand_id')
                    ->relationship('brand', 'name')
                    ->label('Marca (Opcional)')
                    ->placeholder('Sin marca')
                    ->nullable(),
                TextInput::make('name')
                    ->required()
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn (string $operation, $state, callable $set) => $operation === 'create' ? $set('slug', Str::slug($state)) : null),
                TextInput::make('slug')
                    ->required()
                    ->unique(ignoreRecord: true),
                TextInput::make('sku')
                    ->label('SKU / Código')
                    ->unique(ignoreRecord: true)
                    ->maxLength(100),
                RichEditor::make('short_description')
                    ->label('Descripción Breve (Resumen / Extracto)')
                    ->toolbarButtons([
                        'attachFiles', 'blockquote', 'bold', 'bulletList', 'codeBlock', 'h2', 'h3', 'italic', 'link', 'orderedList', 'redo', 'strike', 'underline', 'undo',
                    ])
                    ->columnSpanFull(),
                RichEditor::make('description')
                    ->label('Descripción Detallada / Información Clínica')
                    ->toolbarButtons([
                        'attachFiles', 'blockquote', 'bold', 'bulletList', 'codeBlock', 'h2', 'h3', 'italic', 'link', 'orderedList', 'redo', 'strike', 'underline', 'undo',
                    ])
                    ->columnSpanFull(),
                \Filament\Schemas\Components\Section::make('✨ Generador y Resumen Inteligente IA (AI Overview)')
                    ->description('Caja 1: Prompt / Estructura base (puedes editarla). Pulsa el botón para generar. Caja 2: Resultado final que se guardará en BD y se mostrará en vivo en la web.')
                    ->schema([
                        Textarea::make('ai_prompt_template')
                            ->label('1. Caja de Prompt / Estructura de Instrucciones (Editable)')
                            ->default("ESTRUCTURA CLÍNICA OBLIGATORIA (3 BLOQUES):\n\n🎯 PERFIL Y MODO DE EMPLEO\n• ¿Para qué sirve?: Beneficios biológicos directos y claros.\n• ¿Para quién es ideal?: Perfil del paciente o usuario recomendado.\n• Activos clave por porción: Cantidades exactas sin relleno (mg / mcg / UI).\n• ¿Cómo tomarlo?: Modo de empleo, horarios y mezclas permitidas/prohibidas.\n• Apto para dietas / Certificaciones: Vegano, Sin Gluten, Libre de Lácteos, Calidad GMP.\n\n🧪 EXPLICACIÓN DE INGREDIENTES Y SINERGIA\n• Mecanismo por Ingrediente: Qué hace cada molécula en el cuerpo.\n• Sinergia de la Fórmula: Por qué están juntos y en qué proporción.\n• Precauciones específicas: Qué sentir o vigilar.\n\n🛡️ SEGURIDAD, ADVERTENCIAS Y CONSERVACIÓN\n• Advertencia médica: Consulta previa a médico en embarazo, lactancia o tratamientos.\n• ¿Quién debe tener cuidado?: Poblaciones sensibles.\n• Conservación: Almacenar en un lugar fresco, seco y alejado de la luz solar directa.")
                            ->afterStateHydrated(function ($component, ?string $state, callable $get, ?Model $record) {
                                if (empty($state) || str_contains($state ?? '', '\\n') || str_contains($state ?? '', 'Actúa como un copywriter') || str_contains($state ?? '', 'Producto a analizar:')) {
                                    $template = "ESTRUCTURA CLÍNICA OBLIGATORIA (3 BLOQUES):\n\n" .
                                                "🎯 PERFIL Y MODO DE EMPLEO\n" .
                                                "• ¿Para qué sirve?: Beneficios biológicos directos y claros.\n" .
                                                "• ¿Para quién es ideal?: Perfil del paciente o usuario recomendado.\n" .
                                                "• Activos clave por porción: Cantidades exactas sin relleno (mg / mcg / UI).\n" .
                                                "• ¿Cómo tomarlo?: Modo de empleo, horarios y mezclas permitidas/prohibidas.\n" .
                                                "• Apto para dietas / Certificaciones: Vegano, Sin Gluten, Libre de Lácteos, Calidad GMP.\n\n" .
                                                "🧪 EXPLICACIÓN DE INGREDIENTES Y SINERGIA\n" .
                                                "• Mecanismo por Ingrediente: Qué hace cada molécula en el cuerpo.\n" .
                                                "• Sinergia de la Fórmula: Por qué están juntos y en qué proporción.\n" .
                                                "• Precauciones específicas: Qué sentir o vigilar.\n\n" .
                                                "🛡️ SEGURIDAD, ADVERTENCIAS Y CONSERVACIÓN\n" .
                                                "• Advertencia médica: Consulta previa a médico en embarazo, lactancia o tratamientos.\n" .
                                                "• ¿Quién debe tener cuidado?: Poblaciones sensibles.\n" .
                                                "• Conservación: Almacenar en un lugar fresco, seco y alejado de la luz solar directa.";

                                    $component->state($template);
                                }
                            })
                            ->rows(8)
                            ->columnSpanFull()
                            ->hintAction(
                                \Filament\Actions\Action::make('generate_ai')
                                    ->label('⚡ GENERAR RESUMEN AHORA')
                                    ->icon('heroicon-m-sparkles')
                                    ->color('primary')
                                    ->action(function ($state, callable $set, callable $get) {
                                        $productName = $get('name') ?? 'Producto sin nombre';
                                        $desc = strip_tags($get('description') ?? $get('short_description') ?? '');
                                        $desc = str_replace(['\\n', '\\r', "\n", "\r"], ' ', $desc);
                                        $desc = trim(preg_replace('/\s+/', ' ', $desc));
                                        $prompt = $state ?? "Estructura estándar de 3 bloques";

                                        $fullPrompt = "Eres un copywriter médico e-commerce de élite y científico nutricional. Tu objetivo es redactar una síntesis clínica comercial altamente persuasiva, profesional y fácil de leer para una tienda online premium.\n\n" .
                                            "DATOS DEL PRODUCTO A ANALIZAR:\n" .
                                            "- Nombre del Producto: {$productName}\n" .
                                            "- Descripción Técnica y Características: {$desc}\n\n" .
                                            "REGLAS ESTRICTAS E INQUEBRANTABLES DE REDACCIÓN:\n" .
                                            "1. NO digas 'Actuando como copywriter', NO pongas introducciones, NO repitas el nombre ni la descripción del producto al inicio, NO pongas conclusiones ni texto fuera de los 3 bloques.\n" .
                                            "2. NO uses asteriscos dobles (**) para poner negritas ni títulos. Usa exactamente el símbolo de viñeta (•) seguido del subtítulo limpio, por ejemplo: '• ¿Para qué sirve?: Explicación directa...'.\n" .
                                            "3. Genera EXCLUSIVAMENTE los 3 bloques solicitados con sus respectivos emojis y viñetas.\n\n" .
                                            "ESTRUCTURA OBLIGATORIA A LLENAR:\n" .
                                            $prompt;

                                        $apiKey = env('GEMINI_API_KEY') ?? env('OPENAI_API_KEY');
                                        if (!empty($apiKey)) {
                                            try {
                                                $response = \Illuminate\Support\Facades\Http::post("https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key={$apiKey}", [
                                                    'contents' => [
                                                        ['parts' => [['text' => $fullPrompt]]]
                                                    ]
                                                ]);
                                                if (!$response->successful() || !isset($response['candidates'][0]['content']['parts'])) {
                                                    // Fallback to gemini-2.0-flash if 2.5-flash is unavailable
                                                    $response = \Illuminate\Support\Facades\Http::post("https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key={$apiKey}", [
                                                        'contents' => [
                                                            ['parts' => [['text' => $fullPrompt]]]
                                                        ]
                                                    ]);
                                                }

                                                if ($response->successful() && isset($response['candidates'][0]['content']['parts'])) {
                                                    $textResult = '';
                                                    foreach ($response['candidates'][0]['content']['parts'] as $part) {
                                                        if (isset($part['text'])) {
                                                            $textResult .= $part['text'];
                                                        }
                                                    }
                                                    if (!empty($textResult)) {
                                                        $set('ai_overview', trim($textResult));
                                                        \Filament\Notifications\Notification::make()->title('✨ Resumen generado en vivo por Google Gemini 2.5 Flash IA')->success()->send();
                                                        return;
                                                    }
                                                }

                                                $errorMsg = $response->json('error.message') ?? $response->body();
                                                \Illuminate\Support\Facades\Log::warning("Gemini API error: " . $errorMsg);
                                                \Filament\Notifications\Notification::make()
                                                    ->title('⚠️ Error exacto de Google Gemini API:')
                                                    ->body(substr($errorMsg, 0, 400))
                                                    ->warning()
                                                    ->send();
                                            } catch (\Exception $e) {
                                                \Illuminate\Support\Facades\Log::error("Gemini API exception: " . $e->getMessage());
                                                \Filament\Notifications\Notification::make()
                                                    ->title('⚠️ Error de conexión (Excepción) con Gemini:')
                                                    ->body(substr($e->getMessage(), 0, 400))
                                                    ->warning()
                                                    ->send();
                                            }
                                        } else {
                                            \Filament\Notifications\Notification::make()
                                                ->title('⚠️ Aviso: No se encontró GEMINI_API_KEY en las variables de Railway.')
                                                ->body('Usando motor de síntesis local mientras la configuras.')
                                                ->warning()
                                                ->send();
                                        }

                                        // Intelligent Local Synthesis (tailored to product name & features)
                                        $lowerDesc = mb_strtolower($productName . ' ' . $desc);
                                        
                                        if (str_contains($lowerDesc, 'prost') || str_contains($lowerDesc, 'hombre') || str_contains($lowerDesc, 'masculin')) {
                                            $generated = "🎯 PERFIL Y MODO DE EMPLEO\n" .
                                                "• ¿Para qué sirve?: Apoyo natural especializado para la salud prostática, confort urinario y vitalidad masculina.\n" .
                                                "• ¿Para quién es ideal?: Hombres a partir de los 40 años que buscan prevenir molestias y mantener una función urogenital saludable.\n" .
                                                "• Activos clave por porción: Extractos botánicos biológicamente activos y antioxidantes de alta pureza.\n" .
                                                "• ¿Cómo tomarlo?: Tomar 1 porción al día con abundante agua, preferentemente junto a una comida principal para maximizar la absorción.\n" .
                                                "• Certificaciones y dietas: 100% Natural • Sin Gluten • Libre de OGM • Calidad GMP Auditada y Trazabilidad FEFO.\n\n" .
                                                "🧪 EXPLICACIÓN DE INGREDIENTES Y SINERGIA\n" .
                                                "• Mecanismo de acción: Los fitoesteroles y antioxidantes protegen los tejidos prostáticos y apoyan el equilibrio celular masculino.\n" .
                                                "• Sinergia de la fórmula: Combinación estandarizada que promueve el flujo urinario saludable sin causar pesadez ni fatiga.\n" .
                                                "• Precaución: Monitorear la respuesta individual durante los primeros días de uso regular.\n\n" .
                                                "🛡️ SEGURIDAD, ADVERTENCIAS Y CONSERVACIÓN\n" .
                                                "• Advertencia médica: Consultar con su médico urólogo antes de iniciar si está bajo tratamiento médico específico o condición previa.\n" .
                                                "• ¿Quién debe tener cuidado?: Uso recomendado exclusivamente para adultos hombres.\n" .
                                                "• Conservación: Almacenar en un lugar fresco, seco y alejado de la luz solar directa para preservar los activos botánicos.";
                                        } elseif (str_contains($lowerDesc, 'mujer') || str_contains($lowerDesc, 'ovari') || str_contains($lowerDesc, 'hormon') || str_contains($lowerDesc, 'ciclo')) {
                                            $generated = "🎯 PERFIL Y MODO DE EMPLEO\n" .
                                                "• ¿Para qué sirve?: Respaldo integral para el equilibrio hormonal, bienestar del ciclo menstrual y salud metabólica femenina.\n" .
                                                "• ¿Para quién es ideal?: Mujeres que buscan apoyo natural diario para el ritmo hormonal y armonía corporal.\n" .
                                                "• Activos clave por porción: Inositoles y extractos puros en proporción fisiológica estandarizada.\n" .
                                                "• ¿Cómo tomarlo?: Mezclar 1 porción al día en una bebida fría o tibia sin cafeína, preferentemente con alimentos.\n" .
                                                "• Certificaciones y dietas: Vegano • Sin Gluten • Libre de Lácteos y OGM • Calidad GMP Auditada.\n\n" .
                                                "🧪 EXPLICACIÓN DE INGREDIENTES Y SINERGIA\n" .
                                                "• Mecanismo de acción: Actúan como mensajeros celulares mejorando la señalización metabólica y sensibilidad celular.\n" .
                                                "• Sinergia de la fórmula: Proporción científicamente calibrada para potenciar la eficacia biológica mutua sin saturación.\n" .
                                                "• Precaución: Monitorear la respuesta en personas con sensibilidad a variaciones en la glucosa.\n\n" .
                                                "🛡️ SEGURIDAD, ADVERTENCIAS Y CONSERVACIÓN\n" .
                                                "• Advertencia médica: Consultar con su médico especialista en caso de embarazo, lactancia o tratamientos hormonales.\n" .
                                                "• ¿Quién debe tener cuidado?: Poblaciones sensibles o bajo tratamiento clínico de fertilidad.\n" .
                                                "• Conservación: Almacenar en un lugar fresco, seco y herméticamente cerrado.";
                                        } else {
                                            $generated = "🎯 PERFIL Y MODO DE EMPLEO\n" .
                                                "• ¿Para qué sirve?: Respaldo clínico avanzado para optimizar el equilibrio corporal, energía y bienestar general con {$productName}.\n" .
                                                "• ¿Para quién es ideal?: Adultos con ritmo de vida activo y personas que buscan soporte nutricional diario de máxima pureza.\n" .
                                                "• Activos clave por porción: Fórmula estandarizada de alta biodisponibilidad y grado clínico.\n" .
                                                "• ¿Cómo tomarlo?: Tomar 1 porción diaria con abundante agua, preferentemente junto a una comida principal.\n" .
                                                "• Certificaciones y dietas: 100% Vegano • Sin Gluten • Libre de OGM • Calidad GMP y Lote Auditado FEFO.\n\n" .
                                                "🧪 EXPLICACIÓN DE INGREDIENTES Y SINERGIA\n" .
                                                "• Mecanismo de acción: Los principios activos de {$productName} actúan a nivel celular favoreciendo la homeostasis metabólica.\n" .
                                                "• Sinergia de la fórmula: Diseñada en proporciones fisiológicas exactas para potenciar la eficacia mutua y absorción sin saturación.\n" .
                                                "• Precaución: Monitorear la tolerancia individual durante los primeros 3 días de uso.\n\n" .
                                                "🛡️ SEGURIDAD, ADVERTENCIAS Y CONSERVACIÓN\n" .
                                                "• Advertencia médica: Consultar con un profesional de la salud antes de usar si está embarazada, lactando o en tratamiento crónico.\n" .
                                                "• Conservación: Almacenar en un lugar fresco, seco y alejado de la luz solar directa para preservar la potencia.";
                                        }

                                        $set('ai_overview', $generated);
                                        \Filament\Notifications\Notification::make()
                                            ->title('⚡ Resumen clínico sintetizado y transferido a Caja 2')
                                            ->body('Revisa el resultado abajo en Caja de Respuesta y luego pulsa Guardar producto.')
                                            ->success()
                                            ->send();
                                    })
                            ),
                        Textarea::make('ai_overview')
                            ->label('2. Caja de Respuesta Generada (Este es el texto final que se guarda en BD y se muestra en la tienda virtual)')
                            ->placeholder('Pulsa el botón "⚡ GENERAR RESUMEN AHORA" arriba en la Caja 1 para autocompletar este campo, o escribe tu propio resumen aquí.')
                            ->rows(10)
                            ->columnSpanFull(),
                    ])
                    ->columnSpanFull(),
                TextInput::make('price')
                    ->label('Precio Base')
                    ->numeric()
                    ->prefix('S/')
                    ->required()
                    ->helperText(fn (?Model $record) => 
                        $record && $record->average_entry_cost > 0
                        ? 'Precio Sugerido: S/ ' . number_format($record->recommended_price, 2) . ' (Basado en costo prom. de ingreso + 60%)'
                        : 'Precio Sugerido: S/ 0.00 (No hay compras registradas)'
                    ),
                TextInput::make('compare_at_price')
                    ->numeric()
                    ->prefix('S/')
                    ->label('Precio Anterior (Opcional)')
                    ->rule(fn ($get) => function (string $attribute, $value, \Closure $fail) use ($get) {
                        if (!empty($value) && (float) $value <= (float) $get('price')) {
                            $fail('El Precio Anterior debe ser MAYOR al Precio Actual para que se considere una oferta.');
                        }
                    }),
                \Filament\Forms\Components\Placeholder::make('current_images_preview')
                    ->label('Imágenes Activas del Producto')
                    ->content(function (?Model $record) {
                        if (!$record || $record->images->isEmpty()) {
                            return new \Illuminate\Support\HtmlString('<span style="color: #6b7280; font-style: italic;">No hay imágenes registradas para este producto.</span>');
                        }
                        $html = '<div style="display: flex; gap: 16px; flex-wrap: wrap; margin-top: 4px;">';
                        foreach ($record->images as $img) {
                            $url = asset('storage/' . $img->image_url);
                            $badge = $img->is_primary ? '<span style="position: absolute; top: 6px; left: 6px; background: #10b981; color: white; font-size: 11px; padding: 2px 8px; border-radius: 6px; font-weight: bold; box-shadow: 0 1px 2px rgba(0,0,0,0.1);">★ Principal</span>' : '';
                            $html .= '<div style="position: relative; border: 1px solid #e5e7eb; border-radius: 12px; padding: 8px; background: #f9fafb; display: flex; flex-direction: column; align-items: center; width: 140px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);"><img src="' . $url . '" style="height: 120px; width: 120px; object-fit: contain; border-radius: 8px; background: white;" />' . $badge . '<div style="font-size: 11px; color: #4b5563; text-align: center; margin-top: 6px; width: 100%; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="' . basename($img->image_url) . '">' . basename($img->image_url) . '</div></div>';
                        }
                        $html .= '</div>';
                        return new \Illuminate\Support\HtmlString($html);
                    })
                    ->columnSpanFull(),
                \Filament\Forms\Components\Repeater::make('images')
                    ->relationship('images')
                    ->schema([
                        \Filament\Forms\Components\FileUpload::make('image_url')
                            ->label('Archivo de Imagen')
                            ->image()
                            ->disk('public')
                            ->visibility('public')
                            ->directory('products')
                            ->required(),
                        \Filament\Forms\Components\Toggle::make('is_primary')
                            ->label('¿Es principal?')
                            ->default(false),
                    ])
                    ->defaultItems(0)
                    ->columns(2)
                    ->columnSpanFull()
                    ->label('Agregar / Editar Imágenes'),
                \Filament\Forms\Components\Placeholder::make('stock_info')
                    ->label('Stock Actual')
                    ->content('El stock ahora se gestiona automáticamente por Almacén a través de Recepciones y Transferencias.')
                    ->columnSpanFull(),
                \Filament\Schemas\Components\Section::make('Insignias de Confianza y Garantías (Ficha de Producto)')
                    ->description('Activa, desactiva y personaliza los textos de las insignias que aparecen debajo del botón "Agregar al Carrito" en la tienda virtual.')
                    ->schema([
                        \Filament\Schemas\Components\Group::make()->schema([
                            Toggle::make('show_gmp_badge')
                                ->label('¿Activar Insignia 1?')
                                ->default(true),
                            TextInput::make('badge_1_title')
                                ->label('Título 1')
                                ->placeholder('Laboratorio')
                                ->helperText('Ej: Laboratorio, Beneficio...'),
                            TextInput::make('badge_1_subtitle')
                                ->label('Texto 1')
                                ->placeholder('Grado Clínico GMP')
                                ->helperText('Ej: Grado Clínico GMP, Bueno para mujeres...'),
                        ]),
                        \Filament\Schemas\Components\Group::make()->schema([
                            Toggle::make('show_fefo_badge')
                                ->label('¿Activar Insignia 2?')
                                ->default(true),
                            TextInput::make('badge_2_title')
                                ->label('Título 2')
                                ->placeholder('Trazabilidad')
                                ->helperText('Ej: Trazabilidad, Origen...'),
                            TextInput::make('badge_2_subtitle')
                                ->label('Texto 2')
                                ->placeholder('Lote Auditado FEFO')
                                ->helperText('Ej: Lote Auditado FEFO, 100% Orgánico...'),
                        ]),
                        \Filament\Schemas\Components\Group::make()->schema([
                            Toggle::make('show_shipping_badge')
                                ->label('¿Activar Insignia 3?')
                                ->default(true),
                            TextInput::make('badge_3_title')
                                ->label('Título 3')
                                ->placeholder('Despacho')
                                ->helperText('Ej: Despacho, Garantía...'),
                            TextInput::make('badge_3_subtitle')
                                ->label('Texto 3')
                                ->placeholder('Envío Seguro Nacional')
                                ->helperText('Ej: Envío Seguro Nacional, Express 24h...'),
                        ]),
                    ])
                    ->columns(3)
                    ->columnSpanFull(),
                Toggle::make('is_active')
                    ->label('¿Producto Activo?')
                    ->default(true),
                Toggle::make('is_featured')
                    ->label('¿Destacar en el Home?')
                    ->default(false),
            ]);
    }
}

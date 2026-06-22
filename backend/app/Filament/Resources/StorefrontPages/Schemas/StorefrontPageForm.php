<?php

namespace App\Filament\Resources\StorefrontPages\Schemas;

use Filament\Schemas\Schema;

use Filament\Forms\Components\Builder;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Section;

class StorefrontPageForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Detalles de la Página')
                    ->schema([
                        TextInput::make('title')->required()->label('Título Interno'),
                        TextInput::make('slug')->required()->unique(ignoreRecord: true)->label('Slug (ej: home)'),
                        Toggle::make('is_active')->default(true)->label('¿Activo?'),
                    ])->columns(3),
                    
                Builder::make('blocks')
                    ->label('Constructor de la Página (Bloques)')
                    ->blocks([
                        Builder\Block::make('hero_modern')
                            ->label('Hero Moderno Animado')
                            ->icon('heroicon-m-sparkles')
                            ->schema([
                                TextInput::make('badge')->label('Texto del Badge (ej: Novedades 2026)'),
                                TextInput::make('title_line_1')->label('Título Línea 1')->required(),
                                TextInput::make('title_line_2')->label('Título Línea 2 (Resaltado)')->required(),
                                Textarea::make('description')->label('Párrafo descriptivo'),
                                TextInput::make('button_text')->label('Texto del Botón Principal'),
                                TextInput::make('button_link')->label('Enlace del Botón'),
                                FileUpload::make('hero_image')->label('Imagen Principal (Fondo Transparente recomendado)')->directory('storefront/hero')->image(),
                                TextInput::make('floating_card_title')->label('Título Tarjeta Flotante (ej: 100% Natural)'),
                                TextInput::make('floating_card_subtitle')->label('Subtítulo Tarjeta Flotante'),
                                Toggle::make('animate_rotation')->label('Activar Rotación 3D en Imagen')->default(true),
                                Toggle::make('animate_glow')->label('Activar Brillo Pulsante de Fondo')->default(true),
                            ]),

                        Builder\Block::make('carousel')
                            ->label('Carrusel de Banners')
                            ->icon('heroicon-m-photo')
                            ->schema([
                                \Filament\Forms\Components\Repeater::make('slides')
                                    ->label('Diapositivas (Banners)')
                                    ->schema([
                                        FileUpload::make('image')->label('Imagen del Banner')->directory('storefront/banners')->required()->image(),
                                        TextInput::make('link')->label('Enlace (Opcional)'),
                                    ]),
                                Toggle::make('autoplay')->label('Autoplay')->default(true),
                            ]),

                        Builder\Block::make('category_grid')
                            ->label('Cuadrícula de Categorías')
                            ->icon('heroicon-m-squares-2x2')
                            ->schema([
                                TextInput::make('title')->label('Título de Sección (ej: Compra por Categoría)'),
                                Select::make('category_ids')
                                    ->label('Categorías a Mostrar')
                                    ->multiple()
                                    ->options(\App\Models\Category::pluck('name', 'id')),
                            ]),

                        Builder\Block::make('featured_products')
                            ->label('Productos Destacados')
                            ->icon('heroicon-m-star')
                            ->schema([
                                TextInput::make('title')->label('Título de Sección (ej: Tendencias)'),
                                Select::make('product_ids')
                                    ->label('Productos a Destacar')
                                    ->multiple()
                                    ->options(\App\Models\Product::pluck('name', 'id')),
                            ]),
                            
                        Builder\Block::make('value_proposition')
                            ->label('Franja de Propuesta de Valor')
                            ->icon('heroicon-m-check-badge')
                            ->schema([
                                TextInput::make('title')->label('Título Principal'),
                                Textarea::make('description')->label('Párrafo Descriptivo'),
                                TextInput::make('button_text')->label('Texto del Botón (Opcional)'),
                                TextInput::make('button_link')->label('Enlace del Botón (Opcional)'),
                            ]),
                    ])
                    ->columnSpanFull()
                    ->collapsible(),
            ]);
    }
}

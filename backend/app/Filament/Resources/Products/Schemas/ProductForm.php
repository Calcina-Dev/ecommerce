<?php

namespace App\Filament\Resources\Products\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
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
                    ->required(),
                Select::make('brand_id')
                    ->relationship('brand', 'name')
                    ->required(),
                TextInput::make('name')
                    ->required()
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn (string $operation, $state, callable $set) => $operation === 'create' ? $set('slug', Str::slug($state)) : null),
                TextInput::make('slug')
                    ->required()
                    ->unique(ignoreRecord: true),
                Textarea::make('description')
                    ->rows(3)
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
                \Filament\Forms\Components\Repeater::make('images')
                    ->relationship('images')
                    ->schema([
                        \Filament\Forms\Components\FileUpload::make('image_url')
                            ->label('Imagen')
                            ->image()
                            ->directory('products')
                            ->required(),
                        \Filament\Forms\Components\Toggle::make('is_primary')
                            ->label('¿Es principal?')
                            ->default(false),
                    ])
                    ->defaultItems(0)
                    ->columns(2)
                    ->columnSpanFull()
                    ->label('Imágenes del Producto'),
                \Filament\Forms\Components\Placeholder::make('stock_info')
                    ->label('Stock Actual')
                    ->content('El stock ahora se gestiona automáticamente por Almacén a través de Recepciones y Transferencias.')
                    ->columnSpanFull(),
                Toggle::make('is_active')
                    ->default(true),
            ]);
    }
}

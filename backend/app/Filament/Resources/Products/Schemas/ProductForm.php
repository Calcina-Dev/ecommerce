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
                Toggle::make('is_active')
                    ->default(true),
            ]);
    }
}

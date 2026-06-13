<?php

namespace App\Filament\Resources\Coupons\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class CouponForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('code')
                    ->label('Código del Cupón')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255),
                \Filament\Forms\Components\Select::make('type')
                    ->label('Tipo de Descuento')
                    ->options([
                        'fixed' => 'Monto Fijo (S/)',
                        'percentage' => 'Porcentaje (%)',
                    ])
                    ->required()
                    ->default('fixed'),
                TextInput::make('value')
                    ->label('Valor del Descuento')
                    ->required()
                    ->numeric()
                    ->minValue(0),
                DatePicker::make('valid_from')
                    ->label('Válido Desde'),
                DatePicker::make('valid_until')
                    ->label('Válido Hasta'),
                TextInput::make('usage_limit')
                    ->label('Límite de Uso Total')
                    ->numeric()
                    ->minValue(1),
                TextInput::make('times_used')
                    ->label('Veces Usado')
                    ->disabled()
                    ->numeric()
                    ->default(0),
                Toggle::make('is_active')
                    ->label('¿Cupón Activo?')
                    ->default(true)
                    ->required(),
            ]);
    }
}

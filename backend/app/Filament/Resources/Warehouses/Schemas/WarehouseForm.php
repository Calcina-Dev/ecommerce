<?php

namespace App\Filament\Resources\Warehouses\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class WarehouseForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Nombre del Almacén')
                    ->required()
                    ->maxLength(255),
                TextInput::make('location')
                    ->label('Ubicación / Dirección')
                    ->maxLength(255),
                Toggle::make('is_active')
                    ->label('Activo')
                    ->default(true)
                    ->required(),
                Toggle::make('is_default')
                    ->label('¿Es el Almacén Principal?')
                    ->default(false)
                    ->required(),
            ]);
    }
}

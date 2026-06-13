<?php

namespace App\Filament\Resources\WarehouseTransfers\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class WarehouseTransferForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                \Filament\Forms\Components\Select::make('from_warehouse_id')
                    ->label('Almacén Origen')
                    ->relationship('fromWarehouse', 'name')
                    ->required(),
                \Filament\Forms\Components\Select::make('to_warehouse_id')
                    ->label('Almacén Destino')
                    ->relationship('toWarehouse', 'name')
                    ->required()
                    ->different('from_warehouse_id'),
                TextInput::make('reference_number')
                    ->label('Número de Referencia')
                    ->default(fn () => 'TRF-' . strtoupper(uniqid()))
                    ->required()
                    ->unique(ignoreRecord: true),
                \Filament\Forms\Components\Select::make('status')
                    ->label('Estado')
                    ->options([
                        'pending' => 'Pendiente',
                        'completed' => 'Completado',
                        'cancelled' => 'Cancelado',
                    ])
                    ->required()
                    ->default('pending'),
                Textarea::make('notes')
                    ->label('Notas')
                    ->columnSpanFull(),
                \Filament\Forms\Components\Repeater::make('items')
                    ->relationship()
                    ->schema([
                        \Filament\Forms\Components\Select::make('product_id')
                            ->label('Producto')
                            ->relationship('product', 'name')
                            ->required(),
                        TextInput::make('quantity')
                            ->label('Cantidad')
                            ->numeric()
                            ->required()
                            ->minValue(1),
                    ])
                    ->columns(2)
                    ->columnSpanFull()
                    ->required(),
            ]);
    }
}

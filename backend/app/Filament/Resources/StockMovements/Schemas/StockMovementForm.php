<?php

namespace App\Filament\Resources\StockMovements\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class StockMovementForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                \Filament\Forms\Components\Select::make('warehouse_id')
                    ->relationship('warehouse', 'name')
                    ->label('Almacén'),
                \Filament\Forms\Components\Select::make('product_id')
                    ->relationship('product', 'name')
                    ->label('Producto'),
                \Filament\Forms\Components\Select::make('batch_id')
                    ->relationship('batch', 'batch_number')
                    ->label('Lote'),
                \Filament\Forms\Components\Select::make('user_id')
                    ->relationship('user', 'name')
                    ->label('Usuario (Responsable)'),
                \Filament\Forms\Components\Select::make('type')
                    ->options([
                        'IN' => 'Entrada (IN)',
                        'OUT' => 'Salida (OUT)',
                        'ADJUSTMENT' => 'Ajuste (ADJUSTMENT)',
                    ])
                    ->label('Tipo de Movimiento'),
                TextInput::make('quantity')
                    ->label('Cantidad'),
                TextInput::make('reason')
                    ->label('Motivo'),
                TextInput::make('reference_type')
                    ->label('Tipo Doc. Origen'),
                TextInput::make('reference_id')
                    ->label('ID Doc. Origen'),
                Textarea::make('notes')
                    ->label('Notas')
                    ->columnSpanFull(),
                    
                \Filament\Forms\Components\ViewField::make('traceability')
                    ->view('filament.components.traceability-map')
                    ->columnSpanFull()
                    ->hiddenLabel()
                    ->hiddenOn('create'),
            ]);
    }
}

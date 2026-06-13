<?php

namespace App\Filament\Resources\PurchaseOrders\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Repeater;
use Filament\Schemas\Schema;

class PurchaseOrderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                \Filament\Forms\Components\ViewField::make('timeline')
                    ->view('filament.components.purchase-order-timeline')
                    ->columnSpanFull()
                    ->hidden(fn (string $operation) => $operation === 'create'),
                Select::make('supplier_id')
                    ->relationship('supplier', 'name')
                    ->required(),
                TextInput::make('order_number')
                    ->label('N° de Orden')
                    ->default('Se generará automáticamente')
                    ->formatStateUsing(fn ($state, $record) => $record ? $record->order_number : 'Se generará automáticamente')
                    ->disabled()
                    ->dehydrated(false)
                    ->unique(ignoreRecord: true),
                Select::make('status')
                    ->label('Estado')
                    ->options([
                        'draft' => 'Borrador',
                        'sent' => 'Enviada al Proveedor',
                        'partial' => 'Recepción Parcial',
                        'completed' => 'Completada',
                        'cancelled' => 'Cancelada',
                    ])
                    ->default('draft')
                    ->disabled(fn (string $operation) => $operation === 'create')
                    ->required(),
                DatePicker::make('expected_delivery_date'),
                Textarea::make('notes')->columnSpanFull(),
                Repeater::make('items')
                    ->relationship()
                    ->schema([
                        Select::make('product_id')
                            ->relationship('product', 'name')
                            ->required(),
                        TextInput::make('quantity')
                            ->numeric()
                            ->required()
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn ($state, callable $get, callable $set) => $set('subtotal', $state * ($get('unit_cost') ?? 0))),
                        TextInput::make('unit_cost')
                            ->numeric()
                            ->prefix('S/')
                            ->required()
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn ($state, callable $get, callable $set) => $set('subtotal', $state * ($get('quantity') ?? 0))),
                        TextInput::make('subtotal')
                            ->numeric()
                            ->prefix('S/')
                            ->readOnly()
                            ->required(),
                    ])
                    ->columns(4)
                    ->columnSpanFull()
            ]);
    }
}

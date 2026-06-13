<?php

namespace App\Filament\Resources\Orders\Schemas;

use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Placeholder;
use Filament\Schemas\Schema;



class OrderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                \Filament\Forms\Components\ViewField::make('timeline')
                    ->view('filament.components.order-timeline')
                    ->columnSpanFull()
                    ->hiddenLabel()
                    ->hiddenOn('create'),
                
                Grid::make(2)
                    ->columnSpanFull()
                    ->schema([
                        // Lado Izquierdo: Información Principal y Datos de Envío
                        Group::make()
                            ->columnSpan(['default' => 2, 'lg' => 1])
                            ->schema([
                                \Filament\Schemas\Components\Section::make('Información Principal')
                                    ->schema([
                                        TextInput::make('order_number')
                                            ->label('Nº Pedido')
                                            ->disabled()
                                            ->dehydrated(false),
                                        TextInput::make('document_number')
                                            ->label('Comprobante')
                                            ->formatStateUsing(fn ($record) => $record && $record->document_series ? "{$record->document_series}-{$record->document_number}" : 'Pendiente')
                                            ->disabled()
                                            ->dehydrated(false),
                                        \Filament\Forms\Components\Select::make('payment_status')
                                            ->label('Estado de Pago')
                                            ->options([
                                                'pending' => 'Pendiente',
                                                'paid' => 'Pagado',
                                                'failed' => 'Fallido',
                                            ])
                                            ->required()
                                            ->disabled(fn (string $operation, ?\App\Models\Order $record) => 
                                                $operation === 'edit' && $record && in_array($record->getOriginal('status'), ['shipped', 'delivered'])
                                            ),
                                        \Filament\Forms\Components\Select::make('payment_method')
                                            ->label('Método de Pago')
                                            ->options(fn () => [
                                                'Pasarelas Web' => \App\Models\PaymentMethod::where('is_active', true)
                                                    ->whereIn('scope', ['web', 'both'])
                                                    ->pluck('name', 'code')
                                                    ->toArray(),
                                                'Métodos Manuales / POS' => \App\Models\PaymentMethod::where('is_active', true)
                                                    ->whereIn('scope', ['pos', 'both'])
                                                    ->pluck('name', 'code')
                                                    ->toArray(),
                                            ])
                                            ->disabled(fn (string $operation, ?\App\Models\Order $record) => 
                                                $operation === 'edit' && $record && in_array($record->getOriginal('status'), ['shipped', 'delivered'])
                                            ),
                                        \Filament\Forms\Components\TextInput::make('discount_amount')
                                            ->label('Descuento (Cupón)')
                                            ->formatStateUsing(fn ($record) => $record && $record->discount_amount > 0 ? "S/ {$record->discount_amount} (" . ($record->coupon ? $record->coupon->code : 'Desconocido') . ")" : 'S/ 0.00')
                                            ->disabled()
                                            ->dehydrated(false)
                                            ->visible(fn (?\App\Models\Order $record) => $record && $record->discount_amount > 0),
                                        \Filament\Forms\Components\TextInput::make('total_amount')
                                            ->label('Total Final')
                                            ->prefix('S/')
                                            ->disabled()
                                            ->dehydrated(false),
                                    ])->columns(2),

                                \Filament\Schemas\Components\Section::make('Datos de Envío')
                                    ->schema([
                                        \Filament\Forms\Components\TextInput::make('shipping_name')->label('Nombre')->required(),
                                        \Filament\Forms\Components\TextInput::make('shipping_email')->label('Email')->email()->required(),
                                        \Filament\Forms\Components\TextInput::make('shipping_phone')->label('Teléfono')->required(),
                                        \Filament\Forms\Components\TextInput::make('shipping_city')->label('Ciudad')->required(),
                                        \Filament\Forms\Components\TextInput::make('shipping_address')->label('Dirección')->columnSpanFull()->required(),
                                        \Filament\Forms\Components\Select::make('shipping_method_id')
                                            ->label('Empresa de Envío')
                                            ->relationship('shippingMethod', 'name')
                                            ->preload()
                                            ->searchable(),
                                        \Filament\Forms\Components\TextInput::make('tracking_code')
                                            ->label('Código de Seguimiento (Tracking)'),
                                        \Filament\Forms\Components\TextInput::make('shipping_cost')
                                            ->label('Costo de Envío (Asumido por Empresa)')
                                            ->numeric()
                                            ->prefix('S/')
                                            ->default(0),
                                    ])->columns(2)
                                    ->disabled(fn (string $operation, ?\App\Models\Order $record) => 
                                        $operation === 'edit' && $record && in_array($record->getOriginal('status'), ['shipped', 'delivered'])
                                    ),
                            ]),

                        // Lado Derecho: Productos
                        Group::make()
                            ->columnSpan(['default' => 2, 'lg' => 1])
                            ->schema([
                                \Filament\Schemas\Components\Section::make('Productos')
                                    ->schema([
                                        \Filament\Forms\Components\Repeater::make('items')
                                            ->hiddenLabel()
                                            ->relationship()
                                            ->schema([
                                                \Filament\Forms\Components\Select::make('product_id')
                                                    ->relationship('product', 'name')
                                                    ->label('Producto')
                                                    ->required()
                                                    ->disableOptionsWhenSelectedInSiblingRepeaterItems(),
                                                \Filament\Forms\Components\TextInput::make('quantity')
                                                    ->label('Cantidad')
                                                    ->numeric()
                                                    ->required()
                                                    ->minValue(1),
                                                \Filament\Forms\Components\TextInput::make('price')
                                                    ->label('Precio')
                                                    ->numeric()
                                                    ->prefix('S/')
                                                    ->required(),
                                            ])
                                            ->columns(3)
                                            ->disabled(fn (string $operation, ?\App\Models\Order $record) => 
                                                $operation === 'edit' && $record && in_array($record->getOriginal('status'), ['shipped', 'delivered'])
                                            )
                                            ->deletable(fn (string $operation, ?\App\Models\Order $record) => 
                                                !($operation === 'edit' && $record && in_array($record->getOriginal('status'), ['shipped', 'delivered']))
                                            )
                                            ->addable(fn (string $operation, ?\App\Models\Order $record) => 
                                                !($operation === 'edit' && $record && in_array($record->getOriginal('status'), ['shipped', 'delivered']))
                                            ),
                                    ]),
                            ]),
                    ]),
                \Filament\Forms\Components\ViewField::make('traceability')
                    ->view('filament.components.traceability-map')
                    ->columnSpanFull()
                    ->hiddenLabel()
                    ->hiddenOn('create'),
            ]);
    }
}

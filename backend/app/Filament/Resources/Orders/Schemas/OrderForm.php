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
        $ubigeos = json_decode(file_get_contents(database_path('data/ubigeos_peru.json')), true);

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
                                                $operation === 'edit' && $record && ($record->getOriginal('status') !== 'pending' || $record->getOriginal('payment_status') === 'paid')
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
                                                $operation === 'edit' && $record && ($record->getOriginal('status') !== 'pending' || $record->getOriginal('payment_status') === 'paid')
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

                                \Filament\Schemas\Components\Section::make('Detalles de Pago Seguro (Izipay)')
                                    ->schema([
                                        \Filament\Forms\Components\Placeholder::make('fraud_alert')
                                            ->hiddenLabel()
                                            ->content(fn (?\App\Models\Order $record) => new \Illuminate\Support\HtmlString('
                                                <div class="flex items-center gap-2 p-3 rounded-lg bg-danger-50 text-danger-600 dark:bg-danger-500/10 dark:text-danger-400">
                                                    <svg style="width: 24px; height: 24px; flex-shrink: 0;" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                                    </svg>
                                                    <div>
                                                        <strong>Alerta de Fraude:</strong> Esta orden fue pagada con una tarjeta emitida en el extranjero (País: ' . ($record->card_country ?? 'Desconocido') . '). Revise la orden cuidadosamente para evitar contracargos.
                                                    </div>
                                                </div>
                                            '))
                                            ->columnSpanFull()
                                            ->visible(fn (?\App\Models\Order $record) => $record && $record->is_foreign_card),
                                        \Filament\Forms\Components\TextInput::make('card_brand')
                                            ->label('Marca de Tarjeta')
                                            ->disabled()
                                            ->dehydrated(false)
                                            ->formatStateUsing(fn ($state) => strtoupper($state)),
                                        \Filament\Forms\Components\TextInput::make('card_bin')
                                            ->label('BIN (Primeros 6)')
                                            ->disabled()
                                            ->dehydrated(false)
                                            ->formatStateUsing(fn ($state) => $state ? "{$state}******" : null),
                                        \Filament\Forms\Components\TextInput::make('card_last_digits')
                                            ->label('Últimos 4 Dígitos')
                                            ->disabled()
                                            ->dehydrated(false)
                                            ->formatStateUsing(fn ($state) => $state ? "**** **** **** {$state}" : null),
                                        \Filament\Forms\Components\TextInput::make('card_country')
                                            ->label('País Emisor')
                                            ->disabled()
                                            ->dehydrated(false)
                                            ->formatStateUsing(fn ($state) => strtoupper($state)),
                                    ])
                                    ->columns(4)
                                    ->visible(fn (?\App\Models\Order $record) => $record && $record->payment_method === 'izipay' && $record->card_last_digits),

                                \Filament\Schemas\Components\Section::make('Datos de Envío')
                                    ->schema([
                                        \Filament\Forms\Components\TextInput::make('shipping_name')->label('Nombre')->required(),
                                        \Filament\Forms\Components\TextInput::make('shipping_email')->label('Email')->email()->required(),
                                        \Filament\Forms\Components\TextInput::make('shipping_phone')->label('Teléfono')->required(),
                                        \Filament\Forms\Components\TextInput::make('shipping_address')->label('Dirección')->columnSpanFull()->required(),
                                        \Filament\Forms\Components\Select::make('shipping_department')
                                            ->label('Departamento')
                                            ->options(function () use ($ubigeos) {
                                                $deps = array_unique(array_column($ubigeos, 'department'));
                                                sort($deps);
                                                return array_combine($deps, $deps);
                                            })
                                            ->reactive()
                                            ->afterStateUpdated(fn (callable $set) => $set('shipping_province', null) ?? $set('shipping_district', null) ?? $set('shipping_postal_code', null))
                                            ->searchable()
                                            ->required(),
                                        \Filament\Forms\Components\Select::make('shipping_province')
                                            ->label('Provincia')
                                            ->options(function (callable $get) use ($ubigeos) {
                                                $dep = $get('shipping_department');
                                                if (!$dep) return [];
                                                $provs = array_unique(array_column(array_filter($ubigeos, fn($u) => $u['department'] === $dep), 'province'));
                                                sort($provs);
                                                return array_combine($provs, $provs);
                                            })
                                            ->reactive()
                                            ->afterStateUpdated(fn (callable $set) => $set('shipping_district', null) ?? $set('shipping_postal_code', null))
                                            ->searchable()
                                            ->required(),
                                        \Filament\Forms\Components\Select::make('shipping_district')
                                            ->label('Distrito')
                                            ->options(function (callable $get) use ($ubigeos) {
                                                $dep = $get('shipping_department');
                                                $prov = $get('shipping_province');
                                                if (!$dep || !$prov) return [];
                                                $dists = array_unique(array_column(array_filter($ubigeos, fn($u) => $u['department'] === $dep && $u['province'] === $prov), 'district'));
                                                sort($dists);
                                                return array_combine($dists, $dists);
                                            })
                                            ->reactive()
                                            ->afterStateUpdated(function (callable $set, callable $get, $state) use ($ubigeos) {
                                                $dep = $get('shipping_department');
                                                $prov = $get('shipping_province');
                                                if ($dep && $prov && $state) {
                                                    $match = collect($ubigeos)->first(fn($u) => $u['department'] === $dep && $u['province'] === $prov && $u['district'] === $state);
                                                    if ($match) {
                                                        $set('shipping_postal_code', $match['postal_code']);
                                                    }
                                                }
                                            })
                                            ->searchable()
                                            ->required(),
                                        \Filament\Forms\Components\TextInput::make('shipping_postal_code')
                                            ->label('Código Postal (Ubigeo)')
                                            ->maxLength(6)
                                            ->reactive()
                                            ->afterStateUpdated(function (callable $set, $state) use ($ubigeos) {
                                                if (strlen($state) === 6) {
                                                    $match = collect($ubigeos)->first(fn($u) => $u['postal_code'] === $state);
                                                    if ($match) {
                                                        $set('shipping_department', $match['department']);
                                                        $set('shipping_province', $match['province']);
                                                        $set('shipping_district', $match['district']);
                                                    }
                                                }
                                            })
                                            ->required(),
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

                                        \Filament\Schemas\Components\Actions::make([
                                            \Filament\Actions\Action::make('save')
                                                ->label('Guardar cambios')
                                                ->submit('save')
                                                ->color('primary'),
                                            \Filament\Actions\Action::make('cancel')
                                                ->label('Cancelar')
                                                ->url(fn($livewire) => method_exists($livewire, 'getResource') ? $livewire->getResource()::getUrl('index') : '#')
                                                ->color('gray'),
                                        ])->columnSpanFull()->alignCenter(),
                                    ])->columns(2)
                                    ->disabled(fn (string $operation, ?\App\Models\Order $record) => 
                                        $operation === 'edit' && $record && in_array($record->getOriginal('status'), ['shipped', 'delivered', 'cancelled'])
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
                                                $operation === 'edit' && $record && ($record->getOriginal('status') !== 'pending' || $record->getOriginal('payment_status') === 'paid')
                                            )
                                            ->deletable(fn (string $operation, ?\App\Models\Order $record) => 
                                                !($operation === 'edit' && $record && ($record->getOriginal('status') !== 'pending' || $record->getOriginal('payment_status') === 'paid'))
                                            )
                                            ->addable(fn (string $operation, ?\App\Models\Order $record) => 
                                                !($operation === 'edit' && $record && ($record->getOriginal('status') !== 'pending' || $record->getOriginal('payment_status') === 'paid'))
                                            ),
                                    ]),
                                \Filament\Schemas\Components\Section::make('Historial y Notas')
                                    ->schema([
                                        \Filament\Forms\Components\ViewField::make('order_notes_wrapper')
                                            ->view('filament.components.order-notes-wrapper')
                                            ->hiddenLabel()
                                            ->columnSpanFull()
                                            ->hiddenOn('create'),
                                    ])
                                    ->collapsible()
                                    ->hiddenOn('create'),

                                \Filament\Schemas\Components\Section::make('Trazabilidad del Pedido')
                                    ->description('Flujo de vida del documento (Puedes arrastrar los nodos)')
                                    ->schema([
                                        \Filament\Forms\Components\ViewField::make('traceability')
                                            ->view('filament.components.traceability-map')
                                            ->columnSpanFull()
                                            ->hiddenLabel()
                                            ->hiddenOn('create'),
                                    ])
                                    ->collapsible()
                                    ->hiddenOn('create'),
                            ]),
                    ]),
            ]);
    }
}

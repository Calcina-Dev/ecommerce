<?php

namespace App\Filament\Resources\Sales\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class SaleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                \Filament\Schemas\Components\Section::make('Datos del Cliente')
                    ->description('Selecciona un cliente registrado o ingresa un correo para la venta.')
                    ->columns(2)
                    ->schema([
                        \Filament\Forms\Components\Select::make('customer_id')
                            ->label('Cliente Registrado')
                            ->relationship('customer', 'name')
                            ->searchable()
                            ->preload()
                            ->createOptionForm([
                                \Filament\Forms\Components\TextInput::make('dni')
                                    ->label('DNI')
                                    ->required()
                                    ->maxLength(15)
                                    ->unique(table: 'users', column: 'dni')
                                    ->live(debounce: 500)
                                    ->afterStateUpdated(function ($state, callable $set) {
                                        if (empty($state)) return;
                                        $service = app(\App\Services\PeruConsultService::class);
                                        $result = null;
                                        if (strlen($state) === 8) {
                                            $result = $service->consultDni($state);
                                        } elseif (strlen($state) === 11) {
                                            $result = $service->consultRuc($state);
                                        }
                                        if ($result && isset($result['name'])) {
                                            $set('name', $result['name']);
                                            \Filament\Notifications\Notification::make()->title('Datos autocompletados')->success()->send();
                                        }
                                    })
                                    ->suffixAction(
                                        \Filament\Actions\Action::make('search')
                                            ->icon('heroicon-m-magnifying-glass')
                                            ->action(function ($state, callable $set) {
                                                if (empty($state)) {
                                                    \Filament\Notifications\Notification::make()->title('Ingrese un documento')->warning()->send();
                                                    return;
                                                }

                                                $service = app(\App\Services\PeruConsultService::class);
                                                $result = null;

                                                if (strlen($state) === 8) {
                                                    $result = $service->consultDni($state);
                                                } elseif (strlen($state) === 11) {
                                                    $result = $service->consultRuc($state);
                                                } else {
                                                    \Filament\Notifications\Notification::make()->title('Documento inválido')->body('Debe tener 8 o 11 dígitos')->danger()->send();
                                                    return;
                                                }

                                                if ($result && isset($result['name'])) {
                                                    $set('name', $result['name']);
                                                    \Filament\Notifications\Notification::make()->title('Datos encontrados')->success()->send();
                                                } else {
                                                    \Filament\Notifications\Notification::make()->title('No se encontraron datos')->danger()->send();
                                                }
                                            })
                                    ),
                                \Filament\Forms\Components\TextInput::make('name')
                                    ->label('Nombre Completo')
                                    ->required(),
                                \Filament\Forms\Components\TextInput::make('phone')
                                    ->label('Celular')
                                    ->tel(),
                                \Filament\Forms\Components\TextInput::make('email')
                                    ->label('Correo Electrónico')
                                    ->email(),
                            ])
                            ->createOptionUsing(function (array $data) {
                                $user = \App\Models\User::create([
                                    'dni' => $data['dni'],
                                    'name' => $data['name'],
                                    'phone' => $data['phone'] ?? null,
                                    'email' => $data['email'] ?? ($data['dni'] . '@cliente.local'),
                                    'password' => bcrypt($data['dni']),
                                ]);
                                return $user->id;
                            })
                            ->placeholder('Ninguno (Venta Anónima)'),
                        \Filament\Forms\Components\TextInput::make('customer_email')
                            ->label('Correo Electrónico (Venta Anónima)')
                            ->email()
                            ->placeholder('cliente@correo.com'),
                    ]),

                \Filament\Schemas\Components\Section::make('Datos Principales')
                    ->columns(3)
                    ->schema([
                        \Filament\Forms\Components\Select::make('warehouse_id')
                            ->label('Almacén')
                            ->relationship('warehouse', 'name')
                            ->required()
                            ->default(fn() => \App\Models\Warehouse::first()?->id),
                        \Filament\Forms\Components\Select::make('document_type')
                            ->label('Comprobante')
                            ->options(function () {
                                return \App\Models\DocumentSeries::where('is_active', true)
                                    ->whereIn('document_type', ['BOLETA', 'FACTURA', 'TICKET'])
                                    ->pluck('document_type', 'document_type')
                                    ->mapWithKeys(fn ($type) => [$type => ucfirst(strtolower($type))]);
                            })
                            ->default(function () {
                                return \App\Models\DocumentSeries::where('is_active', true)
                                    ->where('document_type', 'BOLETA')
                                    ->exists() ? 'BOLETA' : null;
                            })
                            ->required(),
                        \Filament\Forms\Components\TextInput::make('document_number')
                            ->label('N° Documento (Autogenerado)')
                            ->disabled()
                            ->dehydrated(false),
                    ]),

                \Filament\Schemas\Components\Section::make('Productos (Punto de Venta)')
                    ->schema([
                        \Filament\Forms\Components\Repeater::make('items')
                            ->relationship('items')
                            ->columns(6)
                            ->schema([
                                \Filament\Forms\Components\Select::make('product_id')
                                    ->label('Producto')
                                    ->relationship('product', 'name')
                                    ->searchable()
                                    ->required()
                                    ->columnSpan(3)
                                    ->disableOptionsWhenSelectedInSiblingRepeaterItems()
                                    ->reactive()
                                    ->afterStateUpdated(fn ($state, callable $set) => $set('price', \App\Models\Product::find($state)?->price ?? 0)),
                                \Filament\Forms\Components\TextInput::make('quantity')
                                    ->label('Cantidad')
                                    ->numeric()
                                    ->default(1)
                                    ->required()
                                    ->columnSpan(1)
                                    ->reactive()
                                    ->afterStateUpdated(fn ($state, callable $get, callable $set) => $set('subtotal', $state * ($get('price') ?? 0))),
                                \Filament\Forms\Components\TextInput::make('price')
                                    ->label('Precio Unitario')
                                    ->numeric()
                                    ->required()
                                    ->columnSpan(1)
                                    ->reactive()
                                    ->afterStateUpdated(fn ($state, callable $get, callable $set) => $set('subtotal', $state * ($get('quantity') ?? 1))),
                                \Filament\Forms\Components\TextInput::make('subtotal')
                                    ->label('Subtotal')
                                    ->numeric()
                                    ->required()
                                    ->columnSpan(1)
                                    ->readOnly(),
                            ])
                            ->live()
                            ->afterStateUpdated(function (callable $get, callable $set) {
                                $items = $get('items');
                                $total = 0;
                                foreach ((array) $items as $item) {
                                    $total += $item['subtotal'] ?? 0;
                                }
                                $set('total_amount', $total);
                                $set('subtotal', round($total / 1.18, 2));
                                $set('total_tax', round($total - ($total / 1.18), 2));
                            }),
                    ])
                    ->columnSpanFull(),



                \Filament\Schemas\Components\Section::make('Totales')
                    ->columns(3)
                    ->schema([
                        \Filament\Forms\Components\TextInput::make('subtotal')
                            ->label('Subtotal (Base)')
                            ->numeric()
                            ->required()
                            ->readOnly(),
                        \Filament\Forms\Components\TextInput::make('total_tax')
                            ->label('IGV (18%)')
                            ->numeric()
                            ->required()
                            ->readOnly(),
                        \Filament\Forms\Components\TextInput::make('total_amount')
                            ->label('TOTAL A PAGAR')
                            ->numeric()
                            ->required()
                            ->readOnly()
                            ->extraAttributes(['class' => 'text-3xl font-bold text-primary-600']),
                        \Filament\Forms\Components\Hidden::make('status')
                            ->default('CONFIRMED'),
                        \Filament\Forms\Components\Hidden::make('user_id')
                            ->default(fn() => auth()->id() ?? 1),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}

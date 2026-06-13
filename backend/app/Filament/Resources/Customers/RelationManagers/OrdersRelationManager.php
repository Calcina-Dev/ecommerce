<?php

namespace App\Filament\Resources\Customers\RelationManagers;

use Filament\Actions\AssociateAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\DissociateAction;
use Filament\Actions\DissociateBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class OrdersRelationManager extends RelationManager
{
    protected static string $relationship = 'orders';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('order_number')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public function infolist(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                \Filament\Schemas\Components\Section::make('Detalles del Pedido')
                    ->columns(3)
                    ->schema([
                        \Filament\Infolists\Components\TextEntry::make('order_number')->label('Pedido #'),
                        \Filament\Infolists\Components\TextEntry::make('total_amount')->label('Total')->money('PEN'),
                        \Filament\Infolists\Components\TextEntry::make('status')
                            ->label('Estado')
                            ->badge()
                            ->color(fn ($state) => match ($state) {
                                'completed' => 'success',
                                'cancelled', 'failed' => 'danger',
                                'processing' => 'info',
                                default => 'warning',
                            }),
                    ]),
                \Filament\Schemas\Components\Section::make('Productos Comprados')
                    ->schema([
                        \Filament\Infolists\Components\RepeatableEntry::make('items')
                            ->label('')
                            ->schema([
                                \Filament\Infolists\Components\TextEntry::make('product_name')->label('Producto'),
                                \Filament\Infolists\Components\TextEntry::make('quantity')->label('Cantidad'),
                                \Filament\Infolists\Components\TextEntry::make('price')->label('Precio Unit.')->money('PEN'),
                                \Filament\Infolists\Components\TextEntry::make('subtotal')->label('Subtotal')->money('PEN'),
                                \Filament\Schemas\Components\Actions::make([
                                    \Filament\Actions\Action::make('send_whatsapp')
                                        ->label('Ofertar')
                                        ->icon('heroicon-m-chat-bubble-oval-left')
                                        ->color('success')
                                        ->modalSubmitAction(fn ($action) => $action->label('Enviar')->icon('heroicon-m-paper-airplane'))
                                        ->form([
                                            \Filament\Forms\Components\TextInput::make('name')
                                                ->label('Para')
                                                ->default(fn (\Livewire\Component $livewire) => $livewire->getOwnerRecord()->name ?? 'Cliente'),
                                            \Filament\Forms\Components\TextInput::make('phone')
                                                ->label('Número')
                                                ->default(fn (\Livewire\Component $livewire) => $livewire->getOwnerRecord()->phone ?? ''),
                                            \Filament\Forms\Components\Select::make('coupon_id')
                                                ->label('Cupón de Descuento')
                                                ->options(\App\Models\Coupon::where('is_active', true)->pluck('code', 'id'))
                                                ->searchable()
                                                ->live()
                                                ->afterStateUpdated(function ($state, callable $set, callable $get, $record, \Livewire\Component $livewire) {
                                                    $coupon = \App\Models\Coupon::find($state);
                                                    if ($coupon) {
                                                        $fullName = $livewire->getOwnerRecord()->name ?? 'Cliente';
                                                        $firstName = explode(' ', trim($fullName))[0];
                                                        $productName = $record->product_name ?? 'este producto';
                                                        $set('message', "¡Hola {$firstName}! Vimos que compraste {$productName}. Te regalamos el código {$coupon->code} para tu próxima compra.");
                                                    }
                                                }),
                                            \Filament\Forms\Components\Textarea::make('message')
                                                ->label('Mensaje')
                                                ->default(function ($record, \Livewire\Component $livewire) {
                                                    $fullName = $livewire->getOwnerRecord()->name ?? 'Cliente';
                                                    $firstName = explode(' ', trim($fullName))[0];
                                                    $productName = $record->product_name ?? 'este producto';
                                                    return "¡Hola {$firstName}! Vimos que compraste {$productName}. Tenemos una oferta especial para tu próxima compra.";
                                                })
                                                ->rows(4)
                                        ])
                                        ->action(function (array $data, \Livewire\Component $livewire) {
                                            $phone = preg_replace('/[^0-9]/', '', $data['phone'] ?? '');
                                            if (strlen($phone) === 9 && !str_starts_with($phone, '51')) {
                                                $phone = '51' . $phone;
                                            }
                                            $message = urlencode($data['message'] ?? '');
                                            $url = "https://wa.me/{$phone}?text={$message}";
                                            $livewire->js("window.open('{$url}', '_blank');");
                                        })
                                ])
                            ])
                            ->columns(5)
                    ]),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('order_number')
            ->columns([
                \Filament\Tables\Columns\TextColumn::make('order_number')->label('Pedido #')->searchable(),
                \Filament\Tables\Columns\TextColumn::make('total_amount')->label('Total')->money('PEN')->sortable(),
                \Filament\Tables\Columns\TextColumn::make('status')
                    ->label('Estado Envío')
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        'completed' => 'success',
                        'cancelled', 'failed' => 'danger',
                        'processing' => 'info',
                        default => 'warning',
                    }),
                \Filament\Tables\Columns\TextColumn::make('payment_status')
                    ->label('Pago')
                    ->badge(),
                \Filament\Tables\Columns\TextColumn::make('created_at')->label('Fecha')->dateTime()->sortable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([])
            ->recordActions([
                \Filament\Actions\ViewAction::make()->modalWidth('5xl'),
            ])
            ->toolbarActions([]);
    }
}

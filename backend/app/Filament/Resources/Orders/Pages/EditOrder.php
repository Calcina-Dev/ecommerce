<?php

namespace App\Filament\Resources\Orders\Pages;

use App\Filament\Resources\Orders\OrderResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

use Filament\Actions\Action;

class EditOrder extends EditRecord
{
    protected static string $resource = OrderResource::class;

    protected function getFormActions(): array
    {
        return [];
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('markAsProcessing')
                ->label('Procesar Pedido')
                ->color('primary')
                ->icon('heroicon-o-cog')
                ->visible(fn () => in_array($this->record->status, ['pending', 'pending_payment']))
                ->action(function () {
                    $this->record->update(['status' => 'processing']);
                    $this->refreshFormData(['status']);
                }),

            Action::make('markAsShipped')
                ->label('Marcar como Enviado')
                ->color('warning')
                ->icon('heroicon-o-truck')
                ->visible(fn () => $this->record->status === 'processing')
                ->disabled(fn () => $this->record->payment_status !== 'paid')
                ->tooltip(fn () => $this->record->payment_status !== 'paid' ? 'El pedido no se puede enviar hasta que el pago esté completado' : null)
                ->requiresConfirmation()
                ->modalHeading('¿Marcar como Enviado?')
                ->modalDescription('Por favor, ingresa los datos de envío obligatorios para proceder.')
                ->form([
                    \Filament\Forms\Components\Select::make('shipping_method_id')
                        ->label('Empresa de Envío')
                        ->options(\App\Models\ShippingMethod::pluck('name', 'id'))
                        ->required()
                        ->default(fn () => $this->record->shipping_method_id),
                    \Filament\Forms\Components\TextInput::make('tracking_code')
                        ->label('Código de Seguimiento (Tracking)')
                        ->required()
                        ->default(fn () => $this->record->tracking_code),
                    \Filament\Forms\Components\TextInput::make('shipping_cost')
                        ->label('Costo de Envío (Asumido por Empresa)')
                        ->numeric()
                        ->prefix('S/')
                        ->default(fn () => $this->record->shipping_cost ?? 0)
                        ->required(),
                ])
                ->action(function (array $data) {
                    $this->record->shipping_method_id = $data['shipping_method_id'];
                    $this->record->tracking_code = $data['tracking_code'];
                    $this->record->shipping_cost = $data['shipping_cost'];
                    $this->record->status = 'shipped';
                    $this->record->save(); // Save triggers the observer
                    $this->refreshFormData(['status', 'shipping_method_id', 'tracking_code', 'shipping_cost']);
                }),

            Action::make('markAsDelivered')
                ->label('Marcar como Entregado')
                ->color('success')
                ->icon('heroicon-o-check-badge')
                ->visible(fn () => $this->record->status === 'shipped')
                ->action(function () {
                    $this->record->status = 'delivered';
                    $this->record->save();
                    $this->refreshFormData(['status']);
                }),

            Action::make('cancelOrder')
                ->label('Cancelar Pedido')
                ->color('danger')
                ->icon('heroicon-o-x-circle')
                ->visible(fn () => !in_array($this->record->status, ['shipped', 'delivered', 'cancelled']))
                ->requiresConfirmation()
                ->modalHeading('¿Cancelar Pedido?')
                ->modalDescription(fn () => $this->record->payment_status === 'paid' ? 'Este pedido ya está PAGADO. Al cancelar, el sistema automáticamente reembolsará el dinero al cliente mediante la pasarela de pagos, y devolverá el stock. ¿Proceder?' : 'El pedido no está pagado. El stock será restaurado. ¿Proceder?')
                ->action(function () {
                    if ($this->record->payment_status === 'paid' && $this->record->gateway_transaction_id) {
                        try {
                            if ($this->record->payment_method === 'izipay') {
                                $service = new \App\Services\IzipayService();
                                $service->refundTransaction($this->record->gateway_transaction_id);
                            } elseif ($this->record->payment_method === 'mercadopago') {
                                $service = new \App\Services\MercadoPagoService();
                                $service->refundPayment($this->record->gateway_transaction_id);
                            }
                            \Filament\Notifications\Notification::make()->title('Reembolso exitoso en la pasarela')->success()->send();
                        } catch (\Exception $e) {
                            \Filament\Notifications\Notification::make()->title('Error en reembolso: ' . $e->getMessage())->danger()->send();
                            return; // Abort cancelation if refund fails
                        }
                    }

                    $this->record->status = 'cancelled';
                    $this->record->payment_status = $this->record->payment_status === 'paid' ? 'refunded' : 'failed';
                    $this->record->save();
                    $this->refreshFormData(['status', 'payment_status']);
                    \Filament\Notifications\Notification::make()->title('Pedido cancelado y stock restaurado')->success()->send();
                }),

        ];
    }

}

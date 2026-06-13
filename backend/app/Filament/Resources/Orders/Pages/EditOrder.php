<?php

namespace App\Filament\Resources\Orders\Pages;

use App\Filament\Resources\Orders\OrderResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

use Filament\Actions\Action;

class EditOrder extends EditRecord
{
    protected static string $resource = OrderResource::class;

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
                ->modalDescription('Esto descontará el stock de los productos automáticamente. ¿Estás seguro?')
                ->action(function () {
                    $this->record->status = 'shipped';
                    $this->record->save(); // Save triggers the observer
                    $this->refreshFormData(['status']);
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
                ->visible(fn () => !in_array($this->record->status, ['delivered', 'cancelled']))
                ->requiresConfirmation()
                ->modalHeading('¿Cancelar Pedido?')
                ->modalDescription('Si el pedido ya fue enviado, el stock será restaurado. ¿Proceder?')
                ->action(function () {
                    $this->record->status = 'cancelled';
                    $this->record->save();
                    $this->refreshFormData(['status']);
                }),

        ];
    }

    protected function getFormActions(): array
    {
        if (in_array($this->record->status, ['shipped', 'delivered'])) {
            return [];
        }

        return parent::getFormActions();
    }
}

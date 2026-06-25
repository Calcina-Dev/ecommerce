<?php

namespace App\Filament\Resources\PurchaseInvoices\Pages;

use App\Filament\Resources\PurchaseInvoices\PurchaseInvoiceResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPurchaseInvoice extends EditRecord
{
    protected static string $resource = PurchaseInvoiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\Action::make('confirm')
                ->label('Confirmar Recepción')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->requiresConfirmation()
                ->visible(fn () => $this->record->status === 'DRAFT')
                ->action(function () {
                    foreach ($this->record->lines as $line) {
                        if (empty($line->batch_number) || empty($line->expiration_date)) {
                            \Filament\Notifications\Notification::make()
                                ->title('Faltan Lotes o Fechas')
                                ->body('Debes editar la factura e ingresar el N° de Lote y Fecha de Caducidad para todos los productos antes de poder ingresar al inventario.')
                                ->danger()
                                ->send();
                            return;
                        }
                    }
                    $this->record->update(['status' => 'VALID']);
                    return redirect($this->getResource()::getUrl('index'));
                }),
            \Filament\Actions\Action::make('cancel')
                ->label('Anular')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->requiresConfirmation()
                ->visible(fn () => $this->record->status === 'VALID')
                ->action(function () {
                    $this->record->update(['status' => 'CANCELLED']);
                    return redirect($this->getResource()::getUrl('index'));
                }),
            DeleteAction::make()
                ->visible(fn () => $this->record->status === 'DRAFT'),
        ];
    }

    protected function getFormActions(): array
    {
        if ($this->record && $this->record->status !== 'DRAFT') {
            return [];
        }

        return parent::getFormActions();
    }

    public function getFooter(): ?\Illuminate\Contracts\View\View
    {
        return view('filament.components.traceability-map', ['record' => $this->getRecord()]);
    }
}

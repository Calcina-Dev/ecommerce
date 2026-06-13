<?php

namespace App\Filament\Resources\Sales\Pages;

use App\Filament\Resources\Sales\SaleResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewSale extends ViewRecord
{
    protected static string $resource = SaleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\Action::make('cancel')
                ->label('Anular Venta')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->requiresConfirmation()
                ->visible(fn (\App\Models\Sale $record) => $record->status === 'CONFIRMED')
                ->action(fn (\App\Models\Sale $record) => $record->update(['status' => 'CANCELLED'])),
            \Filament\Actions\Action::make('print')
                ->label('Imprimir Ticket')
                ->icon('heroicon-o-printer')
                ->color('info')
                ->url(fn (\App\Models\Sale $record) => route('sale.ticket', $record->id))
                ->openUrlInNewTab(),
        ];
    }

    public function getFooter(): ?\Illuminate\Contracts\View\View
    {
        return view('filament.components.traceability-map', ['record' => $this->getRecord()]);
    }
}

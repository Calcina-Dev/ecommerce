<?php

namespace App\Filament\Resources\PurchaseOrders\Pages;

use App\Filament\Resources\PurchaseOrders\PurchaseOrderResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPurchaseOrder extends EditRecord
{
    protected static string $resource = PurchaseOrderResource::class;

    public function mount(int | string $record): void
    {
        parent::mount($record);

        if (in_array($this->record->status, ['cancelled', 'completed', 'partial'])) {
            abort(403, 'Esta orden no puede ser editada porque ya está en proceso o finalizada.');
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}

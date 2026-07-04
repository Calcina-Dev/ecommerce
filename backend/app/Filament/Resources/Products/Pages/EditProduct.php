<?php

namespace App\Filament\Resources\Products\Pages;

use App\Filament\Resources\Products\ProductResource;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditProduct extends EditRecord
{
    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('ver_producto')
                ->label('Ver producto en tienda')
                ->icon('heroicon-o-globe-alt')
                ->url(fn ($record) => env('FRONTEND_URL', env('APP_ENV') === 'local' ? 'http://localhost:3000' : 'https://comprasaludable.up.railway.app') . '/productos/' . $record->slug)
                ->openUrlInNewTab()
                ->color('info'),
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}

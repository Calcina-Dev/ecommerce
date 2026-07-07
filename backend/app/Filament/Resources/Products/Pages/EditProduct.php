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

    protected ?array $uploadedGalleryImages = null;

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data = parent::mutateFormDataBeforeFill($data);

        $images = $this->record->images()->orderBy('sort_order')->get();
        $data['gallery_images'] = $images->pluck('image_url')->toArray();

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (isset($data['gallery_images'])) {
            $this->uploadedGalleryImages = is_array($data['gallery_images']) ? $data['gallery_images'] : [$data['gallery_images']];
            unset($data['gallery_images']);
        }
        return $data;
    }

    protected function afterSave(): void
    {
        if ($this->uploadedGalleryImages !== null) {
            ProductResource::syncGalleryImages($this->record, $this->uploadedGalleryImages);
        }
    }

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

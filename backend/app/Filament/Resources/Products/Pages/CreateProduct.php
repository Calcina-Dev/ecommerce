<?php

namespace App\Filament\Resources\Products\Pages;

use App\Filament\Resources\Products\ProductResource;
use Filament\Resources\Pages\CreateRecord;

class CreateProduct extends CreateRecord
{
    protected static string $resource = ProductResource::class;

    protected ?array $uploadedGalleryImages = null;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (isset($data['gallery_images'])) {
            $this->uploadedGalleryImages = is_array($data['gallery_images']) ? $data['gallery_images'] : [$data['gallery_images']];
            unset($data['gallery_images']);
        }
        return $data;
    }

    protected function afterCreate(): void
    {
        if ($this->uploadedGalleryImages !== null) {
            ProductResource::syncGalleryImages($this->record, $this->uploadedGalleryImages);
        }
    }
}

<?php

namespace App\Filament\Resources\DocumentSeries\Pages;

use App\Filament\Resources\DocumentSeries\DocumentSeriesResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditDocumentSeries extends EditRecord
{
    protected static string $resource = DocumentSeriesResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}

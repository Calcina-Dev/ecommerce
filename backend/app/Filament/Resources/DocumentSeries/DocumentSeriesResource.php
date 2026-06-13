<?php

namespace App\Filament\Resources\DocumentSeries;

use App\Filament\Resources\DocumentSeries\Pages\CreateDocumentSeries;
use App\Filament\Resources\DocumentSeries\Pages\EditDocumentSeries;
use App\Filament\Resources\DocumentSeries\Pages\ListDocumentSeries;
use App\Filament\Resources\DocumentSeries\Schemas\DocumentSeriesForm;
use App\Filament\Resources\DocumentSeries\Tables\DocumentSeriesTable;
use App\Models\DocumentSeries;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class DocumentSeriesResource extends Resource
{
    protected static ?string $model = DocumentSeries::class;

    protected static ?string $modelLabel = 'Serie de Documento';
    protected static ?string $pluralModelLabel = 'Series de Documentos';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentDuplicate;
    protected static \UnitEnum|string|null $navigationGroup = 'Configuración';

    public static function form(Schema $schema): Schema
    {
        return DocumentSeriesForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return DocumentSeriesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListDocumentSeries::route('/'),
            'create' => CreateDocumentSeries::route('/create'),
            'edit' => EditDocumentSeries::route('/{record}/edit'),
        ];
    }
}

<?php

namespace App\Filament\Resources\StorefrontPages;

use App\Filament\Resources\StorefrontPages\Pages\CreateStorefrontPage;
use App\Filament\Resources\StorefrontPages\Pages\EditStorefrontPage;
use App\Filament\Resources\StorefrontPages\Pages\ListStorefrontPages;
use App\Filament\Resources\StorefrontPages\Schemas\StorefrontPageForm;
use App\Filament\Resources\StorefrontPages\Tables\StorefrontPagesTable;
use App\Models\StorefrontPage;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class StorefrontPageResource extends Resource
{
    protected static ?string $model = StorefrontPage::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return StorefrontPageForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return StorefrontPagesTable::configure($table);
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
            'index' => ListStorefrontPages::route('/'),
            'create' => CreateStorefrontPage::route('/create'),
            'edit' => EditStorefrontPage::route('/{record}/edit'),
        ];
    }
}

<?php

namespace App\Filament\Resources\Products;

use App\Filament\Resources\Products\Pages\CreateProduct;
use App\Filament\Resources\Products\Pages\EditProduct;
use App\Filament\Resources\Products\Pages\ListProducts;
use App\Filament\Resources\Products\Schemas\ProductForm;
use App\Filament\Resources\Products\Tables\ProductsTable;
use App\Models\Product;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCube;
    protected static \UnitEnum|string|null $navigationGroup = 'Catálogo';
    
    protected static ?string $recordTitleAttribute = 'name';
    
    public static function getGloballySearchableAttributes(): array
    {
        return ['name', 'sku'];
    }

    public static function getGlobalSearchResultDetails(\Illuminate\Database\Eloquent\Model $record): array
    {
        return [
            'SKU' => $record->sku,
            'Precio' => 'S/ ' . number_format($record->price, 2),
            'Stock' => $record->total_stock . ' uds',
        ];
    }

    public static function form(Schema $schema): Schema
    {
        return ProductForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ProductsTable::configure($table);
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
            'index' => ListProducts::route('/'),
            'create' => CreateProduct::route('/create'),
            'edit' => EditProduct::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    public static function syncGalleryImages(Product $product, array $imageUrls): void
    {
        $imageUrls = array_values(array_filter($imageUrls)); // Ensure clean 0-indexed array without empty values

        if (empty($imageUrls)) {
            $product->images()->delete();
            return;
        }

        // Delete removed images
        $product->images()->whereNotIn('image_url', $imageUrls)->delete();

        // Update or create images in the specified order
        foreach ($imageUrls as $index => $url) {
            $img = $product->images()->where('image_url', $url)->first();
            if ($img) {
                $img->update([
                    'is_primary' => ($index === 0),
                    'sort_order' => $index,
                ]);
            } else {
                $product->images()->create([
                    'image_url' => $url,
                    'is_primary' => ($index === 0),
                    'sort_order' => $index,
                ]);
            }
        }
    }
}

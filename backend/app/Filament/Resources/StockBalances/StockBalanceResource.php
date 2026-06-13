<?php

namespace App\Filament\Resources\StockBalances;

use App\Filament\Resources\StockBalances\Pages\CreateStockBalance;
use App\Filament\Resources\StockBalances\Pages\EditStockBalance;
use App\Filament\Resources\StockBalances\Pages\ListStockBalances;
use App\Filament\Resources\StockBalances\Schemas\StockBalanceForm;
use App\Filament\Resources\StockBalances\Tables\StockBalancesTable;
use App\Models\StockBalance;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class StockBalanceResource extends Resource
{
    protected static ?string $model = StockBalance::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $modelLabel = 'Inventario Actual';
    protected static ?string $pluralModelLabel = 'Inventario Actual';
    protected static \UnitEnum|string|null $navigationGroup = 'Inventario & Logística';
    protected static ?int $navigationSort = 1;

    public static function canCreate(): bool
    {
        return false;
    }

    public static function form(\Filament\Schemas\Schema $schema): \Filament\Schemas\Schema
    {
        return $schema->components([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                \Filament\Tables\Columns\TextColumn::make('warehouse.name')->label('Almacén')->searchable()->sortable(),
                \Filament\Tables\Columns\TextColumn::make('product.name')->label('Producto')->searchable()->sortable(),
                \Filament\Tables\Columns\TextColumn::make('batch.batch_number')->label('Lote')->searchable()->sortable(),
                \Filament\Tables\Columns\TextColumn::make('batch.expiration_date')->label('Vencimiento')->date()->sortable(),
                \Filament\Tables\Columns\TextColumn::make('on_hand')->label('Stock Disponible')->numeric()->sortable()
                    ->badge()
                    ->color(fn (string $state): string => match (true) {
                        $state <= 0 => 'danger',
                        $state <= 10 => 'warning',
                        default => 'success',
                    }),
                \Filament\Tables\Columns\TextColumn::make('total_value')
                    ->label('Valor Total')
                    ->money('PEN')
                    ->state(function ($record) {
                        return $record->on_hand * ($record->batch->unit_cost ?? 0);
                    }),
            ])
            ->filters([
                \Filament\Tables\Filters\SelectFilter::make('warehouse_id')
                    ->label('Almacén')
                    ->relationship('warehouse', 'name')
                    ->multiple()
                    ->preload(),
                \Filament\Tables\Filters\SelectFilter::make('product_id')
                    ->label('Producto')
                    ->relationship('product', 'name')
                    ->searchable()
                    ->preload(),
                \Filament\Tables\Filters\TernaryFilter::make('has_stock')
                    ->label('¿Tiene Stock?')
                    ->placeholder('Todos')
                    ->trueLabel('Solo con stock (> 0)')
                    ->falseLabel('Sin stock (0)')
                    ->queries(
                        true: fn ($query) => $query->where('on_hand', '>', 0),
                        false: fn ($query) => $query->where('on_hand', '<=', 0),
                    ),
            ])
            ->actions([])
            ->bulkActions([]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListStockBalances::route('/'),
        ];
    }
}

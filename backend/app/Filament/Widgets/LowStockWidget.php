<?php

namespace App\Filament\Widgets;

use App\Models\StockBalance;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;

class LowStockWidget extends BaseWidget
{
    protected static ?int $sort = 5;
    protected int | string | array $columnSpan = 1;

    public function table(Table $table): Table
    {
        return $table
            ->query(
                StockBalance::query()
                    ->where('on_hand', '<=', 5)
                    ->orderBy('on_hand', 'asc')
            )
            ->heading('Alertas de Stock Crítico')
            ->columns([
                Tables\Columns\TextColumn::make('product.name')
                    ->label('Producto')
                    ->weight('bold')
                    ->searchable(),
                Tables\Columns\TextColumn::make('warehouse.name')
                    ->label('Almacén')
                    ->badge()
                    ->color('info'),
                Tables\Columns\TextColumn::make('on_hand')
                    ->label('Stock')
                    ->badge()
                    ->color(fn ($state) => $state <= 0 ? 'danger' : 'warning')
                    ->formatStateUsing(fn ($state) => $state <= 0 ? 'AGOTADO' : "{$state} uds."),
            ])
            ->paginated([5])
            ->defaultPaginationPageOption(5);
    }
}

<?php

namespace App\Filament\Widgets;

use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;

class DeadStockTable extends BaseWidget
{
    protected static ?int $sort = 3;
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        $ninetyDaysAgo = Carbon::now()->subDays(90);

        return $table
            ->query(
                Product::query()
                    ->whereHas('stockBalances', function (Builder $query) {
                        $query->where('on_hand', '>', 0);
                    })
                    ->whereDoesntHave('orderItems', function (Builder $query) use ($ninetyDaysAgo) {
                        $query->whereHas('order', function ($orderQuery) use ($ninetyDaysAgo) {
                            $orderQuery->where('status', '!=', 'cancelled')
                                       ->where('created_at', '>=', $ninetyDaysAgo);
                        });
                    })
            )
            ->heading('Inventario Muerto (Sin Ventas en 90 Días)')
            ->description('Productos que ocupan espacio y tienen capital invertido pero no rotan.')
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Producto')
                    ->weight('bold')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('sku')
                    ->label('SKU')
                    ->searchable(),
                Tables\Columns\TextColumn::make('total_stock')
                    ->label('Stock Actual')
                    ->getStateUsing(fn (Product $record) => $record->total_stock)
                    ->badge()
                    ->color('danger'),
                Tables\Columns\TextColumn::make('average_entry_cost')
                    ->label('Costo Unitario')
                    ->getStateUsing(fn (Product $record) => $record->average_entry_cost)
                    ->money('PEN'),
                Tables\Columns\TextColumn::make('capital')
                    ->label('Capital Estancado')
                    ->getStateUsing(fn (Product $record) => $record->total_stock * $record->average_entry_cost)
                    ->money('PEN')
                    ->color('warning')
                    ->weight('bold'),
            ])
            ->paginated([5])
            ->defaultPaginationPageOption(5);
    }
}

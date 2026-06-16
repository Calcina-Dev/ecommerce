<?php

namespace App\Filament\Widgets;

use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class TopCustomersTable extends BaseWidget
{
    protected static ?int $sort = 4;
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                User::query()
                    ->whereHas('orders', function (Builder $query) {
                        $query->where('status', '!=', 'cancelled');
                    })
                    ->withSum(['orders' => function ($query) {
                        $query->where('status', '!=', 'cancelled');
                    }], 'total_amount')
                    ->withCount(['orders' => function ($query) {
                        $query->where('status', '!=', 'cancelled');
                    }])
                    ->orderByDesc('orders_sum_total_amount')
            )
            ->heading('Salón de la Fama (Top Clientes)')
            ->description('Tus mejores clientes según el dinero total gastado históricamente.')
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Cliente')
                    ->weight('bold')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->searchable(),
                Tables\Columns\TextColumn::make('orders_count')
                    ->label('Compras')
                    ->badge()
                    ->color('info')
                    ->sortable(),
                Tables\Columns\TextColumn::make('orders_sum_total_amount')
                    ->label('Total Gastado')
                    ->money('PEN')
                    ->color('success')
                    ->weight('bold')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Miembro desde')
                    ->date('M Y')
                    ->sortable(),
            ])
            ->paginated([5])
            ->defaultPaginationPageOption(5);
    }
}

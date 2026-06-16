<?php

namespace App\Filament\Widgets;

use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use App\Models\CashSession;
use Filament\Widgets\Concerns\InteractsWithPageFilters;

class DiscrepanciesTable extends BaseWidget
{
    use InteractsWithPageFilters;

    protected static ?int $sort = 3;
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        $period = $this->filters['period'] ?? 'today';
        $dateFrom = $this->filters['date_from'] ?? null;
        $dateTo = $this->filters['date_to'] ?? null;

        $query = CashSession::query()->whereNotNull('closed_at');

        if ($period === 'today') {
            $query->whereDate('created_at', now()->toDateString());
        } elseif ($period === 'week') {
            $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
        } elseif ($period === 'month') {
            $query->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()]);
        } elseif ($period === 'year') {
            $query->whereBetween('created_at', [now()->startOfYear(), now()->endOfYear()]);
        } elseif ($period === 'custom') {
            if ($dateFrom) {
                $query->whereDate('created_at', '>=', $dateFrom);
            }
            if ($dateTo) {
                $query->whereDate('created_at', '<=', $dateTo);
            }
        }

        return $table
            ->query(
                $query->orderBy('closed_at', 'desc')
            )
            ->heading('Últimos Cierres de Caja')
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Cajero')
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('cashRegister.name')
                    ->label('Caja')
                    ->badge()
                    ->color('info'),
                Tables\Columns\TextColumn::make('discrepancy')
                    ->label('Estado / Descuadre')
                    ->getStateUsing(function (CashSession $record) {
                        $cashSales = $record->salePayments()->whereHas('paymentMethod', fn($q) => $q->where('name', 'Efectivo'))->sum('amount');
                        $expected = $record->opening_balance 
                                  + $cashSales 
                                  + $record->transactions()->where('type', 'in')->sum('amount') 
                                  - $record->transactions()->where('type', 'out')->sum('amount');
                        $diff = round($record->closing_balance - $expected, 2);
                        return $diff;
                    })
                    ->money('PEN')
                    ->color(fn ($state) => $state == 0 ? 'success' : ($state < 0 ? 'danger' : 'warning'))
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('closed_at')
                    ->label('Fecha de Cierre')
                    ->dateTime('d M Y, h:i a')
                    ->sortable(),
            ])
            ->paginated([5])
            ->defaultPaginationPageOption(5);
    }
}

<?php

namespace App\Filament\Widgets;

use App\Models\Batch;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

class ExpiringBatchesWidget extends BaseWidget
{
    protected static ?int $sort = 6;
    protected int | string | array $columnSpan = 1;

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Batch::query()
                    ->where('status', 'active')
                    ->where('expiration_date', '<=', Carbon::now()->addDays(30))
                    ->orderBy('expiration_date', 'asc')
            )
            ->heading('Lotes Próximos a Vencer (30 días)')
            ->columns([
                Tables\Columns\TextColumn::make('product.name')
                    ->label('Producto')
                    ->weight('bold')
                    ->searchable(),
                Tables\Columns\TextColumn::make('batch_number')
                    ->label('N° Lote')
                    ->searchable(),
                Tables\Columns\TextColumn::make('expiration_date')
                    ->label('Fecha Venc.')
                    ->date('d/m/Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('days_remaining')
                    ->label('Estado')
                    ->badge()
                    ->color(function (Batch $record): string {
                        $days = Carbon::now()->startOfDay()->diffInDays(Carbon::parse($record->expiration_date)->startOfDay(), false);
                        if ($days < 0) return 'danger';
                        if ($days <= 15) return 'warning';
                        return 'info';
                    })
                    ->getStateUsing(function (Batch $record): string {
                        $days = Carbon::now()->startOfDay()->diffInDays(Carbon::parse($record->expiration_date)->startOfDay(), false);
                        if ($days < 0) return "Vencido hace " . abs($days) . " días";
                        if ($days == 0) return "Vence HOY";
                        return "Vence en {$days} días";
                    }),
            ])
            ->paginated([5])
            ->defaultPaginationPageOption(5);
    }
}

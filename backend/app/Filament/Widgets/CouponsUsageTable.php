<?php

namespace App\Filament\Widgets;

use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use App\Models\Coupon;
use Illuminate\Support\Facades\DB;

class CouponsUsageTable extends BaseWidget
{
    use InteractsWithPageFilters;

    protected static ?int $sort = 8;
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        $period = $this->filters['period'] ?? 'month';
        $dateFrom = $this->filters['date_from'] ?? null;
        $dateTo = $this->filters['date_to'] ?? null;

        $ordersFilter = "";
        if ($period === 'week') {
            $ordersFilter = "AND created_at >= '" . now()->startOfWeek() . "' AND created_at <= '" . now()->endOfWeek() . "'";
        } elseif ($period === 'month') {
            $ordersFilter = "AND created_at >= '" . now()->startOfMonth() . "' AND created_at <= '" . now()->endOfMonth() . "'";
        } elseif ($period === 'year') {
            $ordersFilter = "AND created_at >= '" . now()->startOfYear() . "' AND created_at <= '" . now()->endOfYear() . "'";
        } elseif ($period === 'custom') {
            if ($dateFrom) {
                $ordersFilter .= " AND created_at >= '{$dateFrom}'";
            }
            if ($dateTo) {
                $ordersFilter .= " AND created_at <= '{$dateTo}'";
            }
        }

        return $table
            ->heading('Ranking de Cupones Efectivos')
            ->query(function () use ($ordersFilter) {
                return Coupon::query()
                    ->select('coupons.*')
                    ->selectRaw('COALESCE(o.total_uses, 0) as total_uses')
                    ->selectRaw('COALESCE(o.total_discount, 0) as total_discount')
                    ->selectRaw('COALESCE(o.total_revenue, 0) as total_revenue')
                    ->leftJoin(DB::raw("(
                        SELECT coupon_id, COUNT(id) as total_uses, SUM(discount_amount) as total_discount, SUM(total_amount) as total_revenue 
                        FROM orders 
                        WHERE payment_status = 'paid' {$ordersFilter} AND coupon_id IS NOT NULL
                        GROUP BY coupon_id
                    ) o"), 'o.coupon_id', '=', 'coupons.id')
                    ->whereRaw('COALESCE(o.total_uses, 0) > 0');
            })
            ->columns([
                Tables\Columns\TextColumn::make('code')
                    ->label('Código de Cupón')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('type')
                    ->label('Tipo')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'percentage' => 'info',
                        'fixed' => 'success',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'percentage' => 'Porcentaje',
                        'fixed' => 'Monto Fijo',
                        default => $state,
                    }),
                Tables\Columns\TextColumn::make('total_uses')
                    ->label('Veces Utilizado')
                    ->sortable()
                    ->numeric(),
                Tables\Columns\TextColumn::make('total_discount')
                    ->label('Total Descuento Otorgado')
                    ->money('PEN')
                    ->sortable()
                    ->color('danger'),
                Tables\Columns\TextColumn::make('total_revenue')
                    ->label('Ingresos Generados (Neto)')
                    ->money('PEN')
                    ->sortable()
                    ->color('success'),
            ])
            ->defaultSort('total_uses', 'desc')
            ->paginated([5, 10, 'all']);
    }
}

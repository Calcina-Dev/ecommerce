<?php

namespace App\Filament\Widgets;

use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use App\Models\ShippingMethod;
use Illuminate\Support\Facades\DB;

class LogisticsReportTable extends BaseWidget
{
    use InteractsWithPageFilters;

    protected static ?int $sort = 7;
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
            ->heading('Eficiencia Logística (Envíos Web)')
            ->query(function () use ($ordersFilter) {
                return ShippingMethod::query()
                    ->select('shipping_methods.*')
                    ->selectRaw('COALESCE(o.total_orders, 0) as total_orders')
                    ->selectRaw('COALESCE(o.total_shipping_cost, 0) as total_shipping_cost')
                    ->leftJoin(DB::raw("(
                        SELECT shipping_method_id, COUNT(id) as total_orders, SUM(shipping_cost) as total_shipping_cost 
                        FROM orders 
                        WHERE payment_status = 'paid' {$ordersFilter}
                        GROUP BY shipping_method_id
                    ) o"), 'o.shipping_method_id', '=', 'shipping_methods.id')
                    ->whereRaw('COALESCE(o.total_orders, 0) > 0');
            })
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Método de Envío / Courier')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_orders')
                    ->label('Cant. Despachos')
                    ->sortable()
                    ->numeric(),
                Tables\Columns\TextColumn::make('total_shipping_cost')
                    ->label('Costo Flete Asumido')
                    ->money('PEN')
                    ->sortable()
                    ->color('danger'),
                Tables\Columns\TextColumn::make('avg_shipping_cost')
                    ->label('Costo Promedio por Envío')
                    ->state(function ($record) {
                        $orders = $record->total_orders ?? 0;
                        $cost = $record->total_shipping_cost ?? 0;
                        return $orders > 0 ? ($cost / $orders) : 0;
                    })
                    ->money('PEN')
                    ->color('warning'),
            ])
            ->defaultSort('total_orders', 'desc')
            ->paginated([5, 10, 'all']);
    }
}

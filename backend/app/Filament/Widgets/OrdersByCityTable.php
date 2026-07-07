<?php

namespace App\Filament\Widgets;

use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use App\Models\Order;
use Illuminate\Support\Facades\DB;

class OrdersByCityTable extends BaseWidget
{
    use InteractsWithPageFilters;

    protected static ?int $sort = 2;
    protected int | string | array $columnSpan = 1;

    public function table(Table $table): Table
    {
        $period = $this->filters['period'] ?? 'month';
        $dateFrom = $this->filters['date_from'] ?? null;
        $dateTo = $this->filters['date_to'] ?? null;

        $subquery = DB::table('orders')
            ->select(
                DB::raw('MIN(id) as id'),
                DB::raw("COALESCE(NULLIF(shipping_district, ''), NULLIF(shipping_city, ''), NULLIF(shipping_department, '')) as shipping_city"),
                DB::raw('COUNT(id) as total_orders'),
                DB::raw('SUM(total_amount) as total_revenue')
            )
            ->where(function ($q) {
                $q->where(function ($sub) {
                    $sub->whereNotNull('shipping_district')->where('shipping_district', '!=', '');
                })->orWhere(function ($sub) {
                    $sub->whereNotNull('shipping_city')->where('shipping_city', '!=', '');
                })->orWhere(function ($sub) {
                    $sub->whereNotNull('shipping_department')->where('shipping_department', '!=', '');
                });
            })
            ->whereIn(DB::raw('LOWER(status)'), ['shipped', 'delivered', 'completed', 'received', 'recibido']);

        if ($period === 'week') {
            $subquery->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
        } elseif ($period === 'month') {
            $subquery->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()]);
        } elseif ($period === 'year') {
            $subquery->whereBetween('created_at', [now()->startOfYear(), now()->endOfYear()]);
        } elseif ($period === 'custom') {
            if ($dateFrom) {
                $subquery->whereDate('created_at', '>=', $dateFrom);
            }
            if ($dateTo) {
                $subquery->whereDate('created_at', '<=', $dateTo);
            }
        }

        $subquery->groupBy(DB::raw("COALESCE(NULLIF(shipping_district, ''), NULLIF(shipping_city, ''), NULLIF(shipping_department, ''))"));

        return $table
            ->heading('Envíos por Ciudad')
            ->query(function () use ($subquery) {
                return Order::query()->fromSub($subquery, 'orders');
            })
            ->columns([
                Tables\Columns\TextColumn::make('shipping_city')
                    ->label('Ciudad / Distrito')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('total_orders')
                    ->label('Cant. Pedidos')
                    ->sortable()
                    ->numeric(),
                Tables\Columns\TextColumn::make('total_revenue')
                    ->label('Ingresos Totales')
                    ->sortable()
                    ->money('PEN'),
            ])
            ->defaultSort('total_orders', 'desc')
            ->paginated([5, 10, 'all']);
    }
}
